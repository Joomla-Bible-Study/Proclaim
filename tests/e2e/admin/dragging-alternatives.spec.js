/**
 * E2E — non-drag movement alternatives (WCAG 2.2 SC 2.5.7, #1350)
 *
 * The axe scans can never verify this criterion — it is about an
 * interaction having a non-drag equivalent, not about markup. These tests
 * do what a user who cannot drag would do: press the buttons, and assert
 * the same outcome the drag would have produced.
 */

const { test, expect } = require('@playwright/test');

test.describe('Non-drag movement alternatives @a11y-interaction', () => {
    test('a list row moves down and back up by button, and the move persists', async ({ page }) => {
        // Ordering must be the active sort for ordering to be editable at
        // all — same precondition the drag handle has.
        await page.goto(
            '/administrator/index.php?option=com_proclaim&view=cwmteachers&list[fullordering]=teacher.ordering ASC',
            { waitUntil: 'networkidle' },
        );

        const rows = page.locator('#teachers tbody tr');

        if ((await rows.count()) < 2) {
            test.skip(true, 'Need at least two teachers to reorder');
        }

        await expect(
            rows.first().locator('.proclaim-order-buttons'),
            'The Move Up/Down buttons should be injected when ordering is active',
        ).toBeAttached();

        const firstTitle = (await rows.nth(0).innerText()).trim().split('\n')[0];

        // Move the first row down…
        await rows.first().locator('.proclaim-order-down').click();
        await page.waitForTimeout(500); // saveOrderAjax round-trip

        // …and the page must agree after a full reload: the order was
        // persisted server-side, not merely rearranged in the DOM.
        await page.reload({ waitUntil: 'networkidle' });
        const afterMove = (await page.locator('#teachers tbody tr').nth(1).innerText()).trim().split('\n')[0];
        expect(afterMove, 'The moved row should persist in position 2 across a reload').toBe(firstTitle);

        // Restore the original order the same way.
        await page.locator('#teachers tbody tr').nth(1).locator('.proclaim-order-up').click();
        await page.waitForTimeout(500);

        await page.reload({ waitUntil: 'networkidle' });
        const restored = (await page.locator('#teachers tbody tr').nth(0).innerText()).trim().split('\n')[0];
        expect(restored, 'The row should be back in position 1').toBe(firstTitle);
    });

    test('a layout-editor element moves within its row by button', async ({ page }) => {
        // Open the first template's edit form — the layout editor lives there.
        await page.goto('/administrator/index.php?option=com_proclaim&view=cwmtemplates', {
            waitUntil: 'networkidle',
        });

        const editLink = page.locator('#templateList tbody tr a[href*="task=cwmtemplate.edit"], table tbody tr a[href*="task=cwmtemplate.edit"]').first();

        if (!(await editLink.count())) {
            test.skip(true, 'No template to open on this database');
        }

        await editLink.click();
        await page.waitForLoadState('networkidle');

        // The editor AJAX-loads into its own tab; nothing renders until the
        // tab is opened.
        const layoutTab = page.locator('button[role="tab"]', { hasText: /layout editor/i }).first();

        if (!(await layoutTab.count())) {
            test.skip(true, 'No Layout Editor tab on this template form');
        }

        await layoutTab.click();

        const canvas = page.locator('.layout-canvas');
        await expect(canvas, 'The layout editor should render once its tab is opened').toBeAttached({ timeout: 15000 });
        await page.waitForTimeout(500); // element cards populate after the canvas lands

        // A row with at least two elements is needed for a left/right move.
        const multiRow = page.locator('.row-elements').filter({
            has: page.locator('.element-card:nth-child(2)'),
        }).first();

        if (!(await multiRow.count())) {
            test.skip(true, 'No row with two elements to reorder');
        }

        const cards = multiRow.locator('.element-card');
        const firstId = await cards.nth(0).getAttribute('data-element');

        await expect(
            cards.first().locator('.btn-move[data-move="right"]'),
            'Element cards should carry non-drag movement controls',
        ).toBeAttached();

        // Move the first element right: it becomes the second card, without
        // any drag — the assertion SC 2.5.7 actually asks for. (Not saved:
        // the template on the dev site is left untouched.)
        await cards.first().locator('.btn-move[data-move="right"]').click();

        await expect(multiRow.locator('.element-card').nth(1)).toHaveAttribute('data-element', firstId);

        // And back, restoring the editor state.
        await multiRow.locator('.element-card').nth(1).locator('.btn-move[data-move="left"]').click();

        await expect(multiRow.locator('.element-card').nth(0)).toHaveAttribute('data-element', firstId);
    });
});
