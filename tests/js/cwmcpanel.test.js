'use strict';

/**
 * Tests for cwmcpanel.es6.js: starting the tour a post-install link names.
 *
 * Core's Guided Tours script starts a tour from a click on any
 * .button-start-guidedtour carrying data-id, through one listener on body.
 * A listener of the same shape stands in for it here.
 *
 * @package  Proclaim.Tests
 * @since    10.7.9
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, test, beforeEach, afterEach, mock } = require('node:test');
const { fn, isolateModules } = require('./helpers/mocks');

describe('cwmcpanel guided tour start', () => {
    let clicks;
    let listener;

    /**
     * Load the script with the given control panel options, then run its start timer.
     *
     * @param {object|undefined} cpanel What Joomla.getOptions('com_proclaim.cpanel') returns.
     * @returns {void}
     */
    const loadWith = (cpanel) => {
        global.Joomla = {
            getOptions: fn((key, fallback) => (key === 'com_proclaim.cpanel' && cpanel !== undefined ? cpanel : fallback)),
        };

        isolateModules(() => {
            require('../../build/media_source/js/cwmcpanel.es6.js');
        });

        mock.timers.tick(500);
    };

    beforeEach(() => {
        // The script schedules its start as it loads, so fake timers come first.
        mock.timers.enable({ apis: ['setTimeout'] });
        document.body.replaceChildren();
        clicks = [];
        listener = (event) => {
            if (event.target.classList.contains('button-start-guidedtour')) {
                clicks.push(event.target.getAttribute('data-id'));
            }
        };
        document.body.addEventListener('click', listener);
    });

    afterEach(() => {
        document.body.removeEventListener('click', listener);
        mock.timers.reset();
        delete global.Joomla;
    });

    test('clicks a transient start button carrying the tour id', () => {
        loadWith({ startTour: 42 });

        assert.deepEqual(clicks, ['42']);
        assert.equal(document.querySelectorAll('.button-start-guidedtour').length, 0, 'The trigger must not stay in the page');
    });

    test('starts nothing without a startTour option', () => {
        loadWith(undefined);

        assert.deepEqual(clicks, []);
    });

    test('starts nothing for a value that is not a tour id', () => {
        loadWith({ startTour: 'getting_started' });

        assert.deepEqual(clicks, []);
    });
});
