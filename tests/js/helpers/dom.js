'use strict';

/**
 * A browser for the JS unit tests: one jsdom window per test file, with the
 * window's globals installed on Node's globalThis before any code under test
 * loads.
 *
 * This is the environment Jest's jsdom test environment used to provide, built
 * on jsdom alone so the runner can be Node's own. It keeps that environment's
 * choices, because the suite was written against them:
 *
 * - the same JSDOM options (a visual, script-running window at http://localhost/);
 * - the window *is* the global object, so `window.Joomla` and a bare `Joomla`
 *   are one variable, as in a browser — the sources use both forms;
 * - an exception thrown inside an event listener fails the running test, rather
 *   than being swallowed by dispatchEvent() and leaving it green.
 *
 * Each test file runs in its own process under `node --test`, so each gets a
 * fresh window, as each got a fresh environment under Jest.
 *
 * @package  Proclaim.Tests
 * @since    10.7.8
 */

const vm = require('node:vm');
const { after } = require('node:test');
const { JSDOM, VirtualConsole } = require('jsdom');

// Captured before setup.js replaces console.log and console.warn with mocks, so
// that jsdom's own messages still reach the terminal.
const realConsole = globalThis.console;

const virtualConsole = new VirtualConsole();
virtualConsole.sendTo(realConsole, { omitJSDOMErrors: true });
virtualConsole.on('jsdomError', (error) => {
    realConsole.error(error);
});

const dom = new JSDOM('<!DOCTYPE html>', {
    pretendToBeVisual: true,
    runScripts: 'dangerously',
    url: 'http://localhost/',
    virtualConsole,
});
const { window } = dom;

/**
 * Window properties left as Node's own.
 *
 * jsdom implements its timers, clock and microtask queue by calling these very
 * globals, so installing jsdom's versions in their place would make each one
 * call itself. Keeping Node's (setTimeout and setInterval are only wrapped, to
 * record them for the teardown) also means node:test's mock.timers controls
 * every timer the code under test starts. console stays Node's so output goes
 * where the test runner expects it.
 */
const KEEP_NODE = new Set([
    'setTimeout', 'clearTimeout', 'setInterval', 'clearInterval',
    'queueMicrotask', 'performance', 'console',
]);

/**
 * Window self-references, installed below as the global object itself — and
 * `constructor`, which the window inherits from Window.prototype and the global
 * object should not.
 */
const SELF_REFERENCES = new Set(['window', 'self', 'top', 'parent', 'frames', 'constructor']);

/** What every JavaScript realm has (Object, Array, Promise...): Node's are used. */
const LANGUAGE = new Set(Object.getOwnPropertyNames(vm.runInNewContext('globalThis')));

const replaced = new Map();

/**
 * Define a property on globalThis, remembering what was there so the teardown
 * can put it back.
 *
 * @param {string} name       The property name.
 * @param {object} descriptor The new property's descriptor.
 * @returns {void}
 */
function install(name, descriptor) {
    if (!replaced.has(name)) {
        replaced.set(name, Object.getOwnPropertyDescriptor(globalThis, name));
    }

    Object.defineProperty(globalThis, name, { configurable: true, enumerable: true, ...descriptor });
}

/**
 * Find a property's descriptor on an object or anywhere up its prototype chain.
 *
 * @param {object} object The object to search.
 * @param {string} name   The property name.
 * @returns {PropertyDescriptor|undefined} The first descriptor found.
 */
function findDescriptor(object, name) {
    for (let o = object; o !== null; o = Object.getPrototypeOf(o)) {
        const descriptor = Object.getOwnPropertyDescriptor(o, name);

        if (descriptor) {
            return descriptor;
        }
    }

    return undefined;
}

// Everything the window offers, from itself up to EventTarget.prototype.
const names = new Set();

for (let o = window; o !== window.Object.prototype; o = Object.getPrototypeOf(o)) {
    Object.getOwnPropertyNames(o).forEach((name) => names.add(name));
}

names.forEach((name) => {
    if (LANGUAGE.has(name) || KEEP_NODE.has(name) || SELF_REFERENCES.has(name)) {
        return;
    }

    const descriptor = findDescriptor(window, name);

    if (typeof descriptor.value === 'function' && /^[A-Z]/.test(name)) {
        // An interface — HTMLElement, Event, FormData, File... Left unbound, so
        // that `new`, `instanceof` and `class extends` work on it.
        install(name, { writable: true, value: descriptor.value });
    } else if (typeof descriptor.value === 'function') {
        // A method — addEventListener, getComputedStyle, scrollTo... jsdom
        // checks what it is called on, so it is bound to the window.
        install(name, { writable: true, value: descriptor.value.bind(window) });
    } else {
        // State — document, location, localStorage, innerWidth... Read from
        // and written to the window, so jsdom and the tests see the same value.
        install(name, {
            get: () => window[name],
            set: (value) => {
                window[name] = value;
            },
        });
    }
});

// In a browser, and in Jest's environment, the window is the global object.
install('window', { get: () => globalThis });
install('top', { get: () => globalThis });
['self', 'parent', 'frames'].forEach((name) => install(name, { writable: true, value: globalThis }));

// Jest's environment cleared every timer along with its window. These are
// Node's timers, so the ones started here are recorded and cleared at the
// teardown. Otherwise a timer the code under test leaves pending — a 3-second
// "saved" badge, say — holds the process open, and fires after the window is
// gone, where touching the DOM would fail the run.
const started = [];
const { clearTimeout: nodeClearTimeout } = globalThis;

['setTimeout', 'setInterval'].forEach((name) => {
    const start = globalThis[name];

    install(name, {
        writable: true,
        value: (...args) => {
            const handle = start(...args);

            started.push(handle);

            return handle;
        },
    });
});

// Jest's environment turned an exception thrown by an event listener into an
// uncaught exception, which fails the test running at the time — unless the
// code under test listens for 'error' on the window, in which case it is
// assumed to handle it. jsdom by itself only reports such an exception as an
// 'error' event on the window, so without this a click handler that throws
// would let `button.click()` return normally and the test pass.
let errorListeners = 0;

window.addEventListener('error', (event) => {
    if (errorListeners === 0 && event.error != null) {
        process.emit('uncaughtException', event.error);
    }
});

install('addEventListener', {
    writable: true,
    value: (type, ...rest) => {
        if (type === 'error') {
            errorListeners += 1;
        }

        return window.addEventListener(type, ...rest);
    },
});

install('removeEventListener', {
    writable: true,
    value: (type, ...rest) => {
        if (type === 'error') {
            errorListeners -= 1;
        }

        return window.removeEventListener(type, ...rest);
    },
});

after(() => {
    started.forEach((handle) => nodeClearTimeout(handle));
    window.close();

    replaced.forEach((descriptor, name) => {
        if (descriptor) {
            Object.defineProperty(globalThis, name, descriptor);
        } else {
            delete globalThis[name];
        }
    });
});

module.exports = { dom };
