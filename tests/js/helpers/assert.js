'use strict';

/**
 * The Jest matchers node:assert has no one-line equivalent for: toContain, and
 * toHaveBeenCalledWith with its expect.any() / objectContaining() /
 * stringContaining() arguments.
 *
 * Everything else maps straight onto node:assert/strict — toBe to strictEqual,
 * toEqual to deepStrictEqual, toThrow to throws/rejects — in the tests
 * themselves.
 *
 * @package  Proclaim.Tests
 * @since    __DEPLOY_VERSION__
 */

const assert = require('node:assert/strict');
const { inspect, isDeepStrictEqual } = require('node:util');

/**
 * An expected value that matches by a rule rather than by equality.
 */
class Matcher {
    /**
     * @param {string}   description How the matcher reads in a failure message.
     * @param {Function} test        Whether a value matches.
     */
    constructor(description, test) {
        this.description = description;
        this.test = test;
    }

    [inspect.custom]() {
        return this.description;
    }
}

/**
 * Whether an expected value has a Matcher anywhere inside it.
 *
 * @param {*} expected The expected value.
 * @returns {boolean} True if it does.
 */
function hasMatcher(expected) {
    if (expected instanceof Matcher) {
        return true;
    }

    if (Array.isArray(expected)) {
        return expected.some(hasMatcher);
    }

    if (expected !== null && typeof expected === 'object' && Object.getPrototypeOf(expected) === Object.prototype) {
        return Object.values(expected).some(hasMatcher);
    }

    return false;
}

/**
 * Compare a value with an expected one: deepStrictEqual's rules, except that a
 * Matcher anywhere in the expected value is applied rather than compared.
 *
 * @param {*} actual   The value received.
 * @param {*} expected The value expected, possibly holding Matchers.
 * @returns {boolean} True if they match.
 */
function matches(actual, expected) {
    if (!hasMatcher(expected)) {
        return isDeepStrictEqual(actual, expected);
    }

    if (expected instanceof Matcher) {
        return expected.test(actual);
    }

    if (Array.isArray(expected)) {
        return Array.isArray(actual)
            && actual.length === expected.length
            && expected.every((item, index) => matches(actual[index], item));
    }

    const keys = Object.keys(expected);

    return actual !== null
        && typeof actual === 'object'
        && Object.getPrototypeOf(actual) === Object.getPrototypeOf(expected)
        && Object.keys(actual).length === keys.length
        && keys.every((key) => Object.hasOwn(actual, key) && matches(actual[key], expected[key]));
}

const PRIMITIVE_WRAPPERS = [String, Number, Boolean, BigInt, Symbol, Function];

/**
 * Jest's asymmetric matchers, with Jest's rules.
 */
const match = {
    /**
     * expect.any(Type): a value of that type. As in Jest, any(Object) accepts
     * anything whose typeof is 'object', and any(Array) uses Array.isArray().
     *
     * @param {Function} type The constructor.
     * @returns {Matcher} The matcher.
     */
    any(type) {
        return new Matcher(`any(${type.name})`, (value) => {
            if (type === Object) {
                return typeof value === 'object';
            }

            if (type === Array) {
                return Array.isArray(value);
            }

            if (PRIMITIVE_WRAPPERS.includes(type)) {
                return typeof value === type.name.toLowerCase() || value instanceof type;
            }

            return value instanceof type;
        });
    },

    /**
     * expect.objectContaining(subset): an object (not an array) that has each
     * of the subset's properties, own or inherited, matching its value.
     *
     * @param {object} subset The properties to require.
     * @returns {Matcher} The matcher.
     */
    objectContaining(subset) {
        return new Matcher(`objectContaining(${inspect(subset)})`, (value) => value !== null
            && typeof value === 'object'
            && !Array.isArray(value)
            && Object.keys(subset).every((key) => key in value && matches(value[key], subset[key])));
    },

    /**
     * expect.stringContaining(text): a string with that text in it.
     *
     * @param {string} text The text to look for.
     * @returns {Matcher} The matcher.
     */
    stringContaining(text) {
        return new Matcher(`stringContaining(${inspect(text)})`, (value) => (typeof value === 'string' || value instanceof String)
            && value.includes(text));
    },
};

/**
 * Whether a string has a substring, or an array-like an item (by ===), as
 * Jest's toContain decides it.
 *
 * @param {string|Iterable} haystack Where to look.
 * @param {*}               needle   What to look for.
 * @returns {boolean} True if it is there.
 */
function contains(haystack, needle) {
    return typeof haystack === 'string'
        ? haystack.includes(needle)
        : Array.from(haystack).indexOf(needle) !== -1;
}

/**
 * Jest's expect(haystack).toContain(needle).
 *
 * @param {string|Iterable} haystack Where to look.
 * @param {*}               needle   What must be there.
 * @returns {void}
 */
function includes(haystack, needle) {
    assert.ok(contains(haystack, needle), `Expected ${inspect(haystack)} to contain ${inspect(needle)}`);
}

/**
 * Jest's expect(haystack).not.toContain(needle).
 *
 * @param {string|Iterable} haystack Where to look.
 * @param {*}               needle   What must not be there.
 * @returns {void}
 */
function excludes(haystack, needle) {
    assert.ok(!contains(haystack, needle), `Expected ${inspect(haystack)} not to contain ${inspect(needle)}`);
}

/**
 * Jest's expect(mockFn).toHaveBeenCalledWith(...expected): some call — not
 * necessarily the last — had exactly these arguments.
 *
 * @param {Function} mockFn   A node:test mock function.
 * @param {...*}     expected The arguments, which may include `match` matchers.
 * @returns {void}
 */
function calledWith(mockFn, ...expected) {
    const calls = mockFn.mock.calls.map((call) => call.arguments);

    assert.ok(
        calls.some((args) => matches(args, expected)),
        `Expected a call with ${inspect(expected)}, but the calls were ${inspect(calls, { depth: 3 })}`,
    );
}

module.exports = {
    match,
    includes,
    excludes,
    calledWith,
};
