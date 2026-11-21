'use strict';

/**
 * The parts of Jest's runtime the suite relies on that node:test does not have,
 * built on node:test's own mocks.
 *
 * - Clearing every mock's calls before each test (Jest's `clearMocks: true`).
 *   node:test can clear one mock, but keeps no list of them all. A mock made at
 *   the top of a file, or left on a global such as Joomla.renderMessages, would
 *   otherwise carry one test's calls into the next — and a `toHaveBeenCalled()`
 *   could pass on a call an earlier test made. So mocks are made here, where
 *   they can be found again.
 * - Loading the code under test afresh (jest.resetModules / isolateModules).
 * - Running timers until none are left, and advancing the clock while letting
 *   promise callbacks run (jest.runAllTimers / advanceTimersByTimeAsync).
 *
 * @package  Proclaim.Tests
 * @since    10.7.8
 */

const path = require('node:path');
const { mock } = require('node:test');

/** Every mock function made by fn() or spyOn(). */
const mocks = new Set();

/** Methods replaced by spyOn() and not yet restored, oldest first. */
const spies = [];

/**
 * Make a mock function — node:test's mock.fn() — that clearAllMocks() can
 * reach. Jest's jest.fn().
 *
 * @param {Function} [implementation] What the mock does when called; a no-op if omitted.
 * @returns {Function} The mock; its record is on `.mock` (calls, callCount(), ...).
 */
function fn(implementation) {
    const mocked = implementation === undefined ? mock.fn() : mock.fn(implementation);

    mocks.add(mocked);

    return mocked;
}

/**
 * Implementations for fn() and mock.mockImplementation(), standing in for
 * Jest's shorthands: jest.fn().mockReturnValue(value) is fn(returning(value)),
 * mockResolvedValue(value) is resolving(value), mockRejectedValue(reason) is
 * rejecting(reason). As in Jest, the value is evaluated once and every call
 * gets that same value.
 *
 * @param {*} value The value, or rejection reason.
 * @returns {Function} The implementation.
 */
const returning = (value) => () => value;
const resolving = (value) => () => Promise.resolve(value);
const rejecting = (reason) => () => Promise.reject(reason);

/**
 * Replace a method with a mock — node:test's mock.method() — that
 * restoreAllMocks() puts back after the test. Jest's jest.spyOn().
 *
 * @param {object}   object           The object whose method is replaced.
 * @param {string}   methodName       The method's name.
 * @param {Function} [implementation] What the mock does; the original method if omitted.
 * @returns {Function} The mock.
 */
function spyOn(object, methodName, implementation) {
    const spy = implementation === undefined
        ? mock.method(object, methodName)
        : mock.method(object, methodName, implementation);

    mocks.add(spy);
    spies.push(spy);

    return spy;
}

/**
 * Forget every mock's calls, keeping what each one does. Jest's
 * jest.clearAllMocks(), which `clearMocks: true` ran before each test.
 *
 * @returns {void}
 */
function clearAllMocks() {
    mocks.forEach((mocked) => mocked.mock.resetCalls());
}

/**
 * Put back every method spyOn() replaced, newest first. Jest 30's
 * jest.restoreAllMocks(), which leaves plain mock functions as they are.
 *
 * @returns {void}
 */
function restoreAllMocks() {
    while (spies.length > 0) {
        spies.pop().mock.restore();
    }
}

const ROOT = path.resolve(__dirname, '../../..');
const TESTS = path.resolve(__dirname, '..');

/**
 * The cached modules that belong to the extension: not node_modules, and not
 * the test files and these helpers, which hold the suite's state.
 *
 * @returns {string[]} Their require.cache keys.
 */
function ownModuleIds() {
    return Object.keys(require.cache).filter((id) => id.startsWith(ROOT + path.sep)
        && !id.startsWith(TESTS + path.sep)
        && !id.includes(`${path.sep}node_modules${path.sep}`));
}

/**
 * Make the next require() of the code under test run it afresh. Jest's
 * jest.resetModules() — which also cleared the calls of every mock kept
 * directly on the global object, so this does too.
 *
 * @returns {void}
 */
function resetModules() {
    ownModuleIds().forEach((id) => {
        delete require.cache[id];
    });

    Object.getOwnPropertyNames(globalThis).forEach((name) => {
        const descriptor = Object.getOwnPropertyDescriptor(globalThis, name);

        if (descriptor && mocks.has(descriptor.value)) {
            descriptor.value.mock.resetCalls();
        }
    });
}

/**
 * Run a callback whose require() calls load the code under test afresh,
 * leaving the module cache as it found it. Jest's jest.isolateModules().
 *
 * @param {Function} callback Code that requires the modules to isolate.
 * @returns {void}
 */
function isolateModules(callback) {
    const outside = new Map(ownModuleIds().map((id) => [id, require.cache[id]]));

    for (const id of outside.keys()) {
        delete require.cache[id];
    }

    try {
        callback();
    } finally {
        ownModuleIds().forEach((id) => {
            delete require.cache[id];
        });
        outside.forEach((cached, id) => {
            require.cache[id] = cached;
        });
    }
}

// Captured before any test fakes timers: the helpers below need the real ones.
const RealDate = Date;
const realSetImmediate = setImmediate;

/**
 * Run every fake timer, including the ones that running timers start, until
 * none are left. Jest's jest.runAllTimers(): mock.timers.runAll() alone stops
 * at the latest timer that existed when it was called.
 *
 * Needs `Date` among the faked APIs. Each runAll() leaves only timers due after
 * the clock's new time, so once a call does not move the clock, none are left.
 *
 * @returns {void}
 */
function runAllTimers() {
    if (Date === RealDate) {
        throw new Error('runAllTimers() needs Date among the APIs passed to mock.timers.enable().');
    }

    for (let round = 0; round < 1000; round += 1) {
        const before = Date.now();

        mock.timers.runAll();

        if (round > 0 && Date.now() === before) {
            return;
        }
    }

    throw new Error('runAllTimers(): timers still being scheduled after 1000 rounds — an endless timer loop?');
}

/**
 * Yield to the real event loop, so every pending promise callback runs.
 *
 * @returns {Promise<void>}
 */
function nextMacrotask() {
    return new Promise((resolve) => {
        realSetImmediate(resolve);
    });
}

/**
 * Move the fake clock forward, letting promise callbacks run as timers fire.
 * Jest's jest.advanceTimersByTimeAsync(): mock.timers.tick() is synchronous, so
 * a timer whose callback awaits a fetch would not see it settle before the next
 * timer fired. Steps a millisecond at a time, yielding after each step.
 *
 * @param {number} ms How far to move the clock.
 * @returns {Promise<void>}
 */
async function advanceTimersByTimeAsync(ms) {
    await nextMacrotask();

    for (let elapsed = 0; elapsed < ms; elapsed += 1) {
        mock.timers.tick(1);
        await nextMacrotask();
    }
}

module.exports = {
    fn,
    returning,
    resolving,
    rejecting,
    spyOn,
    clearAllMocks,
    restoreAllMocks,
    resetModules,
    isolateModules,
    runAllTimers,
    advanceTimersByTimeAsync,
};
