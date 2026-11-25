'use strict';

/**
 * The subform tooltip repair. Whatever Joomla renders in a subform column
 * header, the tooltip icon has to end up focusable, named by its tooltip, and
 * carrying a role that allows a name.
 *
 * Joomla 5.4 and 6.1 render the icon with aria-hidden="true" and tabindex="0".
 * Joomla 6.2 renders it with aria-labelledby and tabindex="0" but no role.
 * These load the shipped script against both.
 *
 * @package  Proclaim.Tests
 * @since    __DEPLOY_VERSION__
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, it } = require('node:test');
const { isolateModules } = require('./helpers/mocks');

/**
 * The id core gives the tooltip of the scripture subform's Bible Version column.
 */
const TIP_ID = 'tip-jform_scriptures_bible_version';

/**
 * Render a subform table whose second column header holds a tooltip icon.
 *
 * @param   {string}  iconAttributes  The icon's attributes, as core renders them.
 *
 * @returns {HTMLElement}  The icon.
 */
function renderSubform(iconAttributes) {
    document.body.innerHTML = `
        <joomla-field-subform class="subform-repeatable">
            <table class="table" id="subfieldList_jform_scriptures">
                <thead>
                    <tr>
                        <th scope="col">Reference</th>
                        <th scope="col">Bible Version
                            <span class="icon-info-circle" ${iconAttributes}></span><div role="tooltip" id="${TIP_ID}">Help text</div>
                        </th>
                    </tr>
                </thead>
                <tbody class="subform-repeatable-container"></tbody>
            </table>
        </joomla-field-subform>
    `;

    return document.querySelector('.icon-info-circle');
}

/**
 * Load the shipped script and run it the way a page load does.
 *
 * @returns {void}
 */
function loadShim() {
    isolateModules(() => {
        require('../../build/media_source/js/subform-tooltip-a11y.es6.js');
    });

    document.dispatchEvent(new Event('DOMContentLoaded'));
}

/**
 * Assert an icon is focusable, named by its tooltip, and has a role that
 * allows a name.
 *
 * @param   {HTMLElement}  icon   The icon to check.
 * @param   {string}       tipId  The id of the tooltip that should name it.
 *
 * @returns {void}
 */
function assertNamedAndFocusable(icon, tipId = TIP_ID) {
    assert.equal(icon.getAttribute('role'), 'img');
    assert.equal(icon.getAttribute('aria-labelledby'), tipId);
    assert.equal(icon.getAttribute('tabindex'), '0');
    assert.equal(icon.hasAttribute('aria-hidden'), false);
}

describe('subform tooltip repair', () => {
    it('names a Joomla 5.4 or 6.1 icon from its tooltip and keeps it focusable', () => {
        const icon = renderSubform('aria-hidden="true" tabindex="0"');

        loadShim();

        assertNamedAndFocusable(icon);
    });

    it('gives a Joomla 6.2 icon the role its name needs', () => {
        const icon = renderSubform(`aria-labelledby="${TIP_ID}" tabindex="0"`);

        loadShim();

        assertNamedAndFocusable(icon);
    });

    it('leaves the header cell to the icon', () => {
        const icon = renderSubform('aria-hidden="true" tabindex="0"');

        loadShim();

        // The icon carries the help text; describing the header with it too
        // would make a screen reader read it twice.
        assert.equal(icon.closest('th').hasAttribute('aria-describedby'), false);
    });

    it('leaves an icon that already has a role as it is', () => {
        const icon = renderSubform(`role="button" aria-describedby="${TIP_ID}" tabindex="0"`);

        loadShim();

        assert.equal(icon.getAttribute('role'), 'button');
        assert.equal(icon.hasAttribute('aria-labelledby'), false);
        assert.equal(icon.getAttribute('aria-describedby'), TIP_ID);
    });

    it('leaves an icon with no tooltip after it as it is', () => {
        const icon = renderSubform('aria-hidden="true" tabindex="0"');

        icon.nextElementSibling.remove();
        loadShim();

        assert.equal(icon.hasAttribute('role'), false);
        assert.equal(icon.getAttribute('aria-hidden'), 'true');
    });

    it('leaves an info icon outside a subform header as it is', () => {
        document.body.innerHTML = `
            <div class="form-text">
                <span class="icon-info-circle" aria-hidden="true" tabindex="0"></span><div role="tooltip" id="tip-other">Other</div>
            </div>
        `;

        loadShim();

        const icon = document.querySelector('.icon-info-circle');

        assert.equal(icon.hasAttribute('role'), false);
        assert.equal(icon.getAttribute('aria-hidden'), 'true');
    });

    it('repairs the header of a nested subform added after the page loads', async () => {
        const nestedTipId = 'tip-jform_scriptures__scriptures0__links_url';

        renderSubform('aria-hidden="true" tabindex="0"');
        loadShim();

        const row = document.createElement('tr');

        row.className = 'subform-repeatable-group';
        row.innerHTML = `
            <td>
                <table id="subfieldList_jform_scriptures__scriptures0__links">
                    <thead>
                        <tr>
                            <th scope="col">Link
                                <span class="icon-info-circle" aria-labelledby="${nestedTipId}" tabindex="0"></span><div role="tooltip" id="${nestedTipId}">Help text</div>
                            </th>
                        </tr>
                    </thead>
                </table>
            </td>
        `;
        document.querySelector('tbody.subform-repeatable-container').appendChild(row);

        // Let the MutationObserver deliver the append.
        await new Promise((resolve) => setTimeout(resolve, 0));

        assertNamedAndFocusable(row.querySelector('.icon-info-circle'), nestedTipId);
    });
});
