const { test, expect } = require('@playwright/test');

/**
 * #1930: a narrow element card packed seven controls into a fixed-percentage
 * width and they ran past its edge — the remove button ended up half outside
 * the card and partly unclickable.
 *
 * ⚠️ Hiding them was never an option. The four move buttons are the
 * single-pointer, keyboard-operable alternative WCAG 2.2 SC 2.5.7 requires for
 * every dragging operation, so dropping them below a breakpoint would remove
 * that alternative at exactly the widths where dragging is hardest. They are
 * collapsed into a menu instead.
 *
 * These assert both halves: nothing overflows the card, and everything is
 * still reachable — including by keyboard.
 *
 * @package  Proclaim.Tests
 * @since    __DEPLOY_VERSION__
 */

/**
 * Open the layout editor on the first template.
 *
 * @param {import('@playwright/test').Page} page The page.
 * @returns {Promise<void>}
 */
async function openLayoutEditor(page) {
    await page.goto('/administrator/index.php?option=com_proclaim&view=cwmtemplates', {
        waitUntil: 'networkidle',
    });

    const editLink = page.locator('table tbody tr a[href*="task=cwmtemplate.edit"]').first();

    if (!(await editLink.count())) {
        test.skip(true, 'No template to open on this database');
    }

    await editLink.click();
    await page.waitForLoadState('networkidle');

    const layoutTab = page.locator('button[role="tab"]', { hasText: /layout editor/i }).first();

    if (!(await layoutTab.count())) {
        test.skip(true, 'No Layout Editor tab on this template form');
    }

    await layoutTab.click();
    await expect(page.locator('.layout-canvas')).toBeAttached({ timeout: 15000 });
    await page.waitForTimeout(800);
}

test.describe('Layout editor narrow cards @a11y-interaction', () => {
    test('no control overflows its card at any width', async ({ page }) => {
        await openLayoutEditor(page);

        for (const width of [1600, 1100, 800]) {
            await page.setViewportSize({ width, height: 900 });
            await page.waitForTimeout(400);

            const spills = await page.evaluate(() => {
                const cards = [...document.querySelectorAll('.row-elements .element-card')];

                return cards.flatMap((card) => {
                    const box = card.getBoundingClientRect();

                    return [...card.querySelectorAll('.btn-move, .btn-settings, .btn-remove, .btn-actions-toggle')]
                        // A control inside a closed menu has no box to overflow with.
                        .filter((b) => b.offsetParent !== null)
                        .map((b) => Math.round(b.getBoundingClientRect().right - box.right))
                        .filter((over) => over > 0);
                });
            });

            expect(
                spills,
                `At ${width}px wide, controls run past their card's right edge by ${spills.join(', ')}px. `
                + 'Before #1930 a colspan-2 card overflowed by 133px and its remove button was half unclickable.',
            ).toEqual([]);
        }
    });

    test('a collapsed card still reaches every control, by keyboard', async ({ page }) => {
        await openLayoutEditor(page);
        await page.setViewportSize({ width: 1100, height: 900 });
        await page.waitForTimeout(400);

        const card = page.locator('.row-elements .element-card')
            .filter({ has: page.locator('.btn-actions-toggle:visible') })
            .first();

        if (!(await card.count())) {
            test.skip(true, 'No card narrow enough to collapse at this width');
        }

        const toggle = card.locator('.btn-actions-toggle');
        const menu   = card.locator('.element-actions-menu');

        await expect(menu, 'The menu starts closed').toBeHidden();
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');

        // Keyboard only: focus the toggle and open it with Enter.
        await toggle.focus();
        await expect(toggle).toBeFocused();
        await page.keyboard.press('Enter');

        await expect(menu, 'Enter on the toggle opens the menu').toBeVisible();
        await expect(toggle).toHaveAttribute('aria-expanded', 'true');

        // All six controls present and operable, not merely in the DOM.
        for (const selector of [
            '.btn-move[data-move="left"]',
            '.btn-move[data-move="right"]',
            '.btn-move[data-move="up"]',
            '.btn-move[data-move="down"]',
            '.btn-settings',
            '.btn-remove',
        ]) {
            await expect(menu.locator(selector), `${selector} should be reachable in the menu`).toBeVisible();
        }

        // ⚠️ SC 2.5.8 (Target Size, Minimum) — 24x24 CSS px. The inline
        // buttons this replaces were set to 22px, which did not meet it.
        const smallest = await menu.locator('.btn-move, .btn-settings, .btn-remove').evaluateAll(
            (buttons) => Math.min(...buttons.map((b) => {
                const r = b.getBoundingClientRect();

                return Math.min(r.width, r.height);
            }))
        );

        expect(smallest, 'Every menu control should be at least 24px on its smaller side').toBeGreaterThanOrEqual(24);

        // Escape closes it and does not strand focus.
        await page.keyboard.press('Escape');
        await expect(menu, 'Escape closes the menu').toBeHidden();
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
    });
});
