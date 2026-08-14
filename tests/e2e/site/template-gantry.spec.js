/**
 * E2E — Proclaim under a Gantry template
 *
 * Every dev site ran Cassiopeia, so nothing exercised Proclaim's markup under a
 * template that styles it differently. That blind spot is how the teacher page's
 * navigation buttons reached production painted #8b5717 on a #8b5717 background:
 * a Gantry section preset colours every anchor inside its wrapper, Proclaim
 * renders its buttons as anchors, and Bootstrap's own `.btn-primary` colour is
 * less specific and loses (#1799).
 *
 * ⚠️ Stock Helium does not reproduce that particular failure — the colour came
 * from that site's own preset, not from the template's defaults. So this file is
 * not a regression test for #1799. It is coverage for the thing that was missing
 * underneath it: that Proclaim renders, and stays legible, in a Gantry document
 * at all.
 *
 * Runs only in the `site-helium` project, which pins a Gantry template style.
 */

const { test, expect } = require('@playwright/test');

const PROCLAIM_CONTENT = '.com-proclaim';

/**
 * The template style id to render under. Helium installs as a style whose id
 * varies per site, so it is configurable; 13 is j6-dev's.
 */
const STYLE = process.env.PROCLAIM_GANTRY_STYLE || '13';

/**
 * Views worth rendering under a second template. Detail views are reached by id
 * rather than click-through: this file is about the template, and the listings
 * already prove the links work in the site suite.
 */
const VIEWS = [
    ['landing page', 'cwmlandingpage'],
    ['sermon listing', 'cwmsermons'],
    ['teacher listing', 'cwmteachers'],
    ['series listing', 'cwmseriesdisplays'],
    ['series podcast listing', 'cwmseriespodcastlist'],
];

/**
 * PHP notices leak into the body and would otherwise read as ordinary content.
 */
const PHP_ERRORS = /(Fatal error|Parse error|Warning:|Notice:|Deprecated:)/;

/**
 * @param {string} view Proclaim site view name
 * @returns {string} URL pinned to the Gantry template style
 */
function url(view) {
    return `/index.php?option=com_proclaim&view=${view}&templateStyle=${STYLE}`;
}

test.describe('Proclaim under a Gantry template @gantry', () => {
    for (const [label, view] of VIEWS) {
        test(`${label} renders under Gantry`, async ({ page }) => {
            const response = await page.goto(url(view), { waitUntil: 'networkidle' });

            expect(response.status()).toBeLessThan(400);

            // ⚠️ Assert, do not skip. A style id that no longer resolves renders
            // the site's default template instead, and every assertion below
            // would pass while testing Cassiopeia again — the blind spot this
            // file exists to close.
            await expect(
                page.locator('.g-content, [class*="g-container"], body[class*="g-"]').first(),
                `Template style ${STYLE} did not render Gantry markup. Set PROCLAIM_GANTRY_STYLE to the Helium style id.`
            ).toBeAttached({ timeout: 15000 });

            await expect(page.locator(PROCLAIM_CONTENT).first()).toBeAttached({ timeout: 15000 });
            await expect(page.locator('body')).not.toContainText(PHP_ERRORS);
        });
    }

    /**
     * The shape of #1799, generalised: a template rule that wins over Bootstrap's
     * button colour leaves the label the same colour as the button behind it.
     * Text that is present but invisible reads as an empty button, and no
     * rendering check catches it.
     */
    test('button labels are not the same colour as their button', async ({ page }) => {
        // The navigation buttons live on a teacher's detail page, not the
        // listing. Collect the href before navigating — the listing locator goes
        // stale the moment we leave it.
        await page.goto(url('cwmteachers'), { waitUntil: 'networkidle' });

        const links = page.locator('.proclaim-grid-card a[href], .proclaim-item a[href]');
        const total = await links.count();
        let target = null;

        for (let i = 0; i < total && target === null; i++) {
            const href = await links.nth(i).getAttribute('href');

            if (href && href.startsWith('/')) {
                target = href;
            }
        }

        expect(target, 'No teacher detail link to open, so this checks nothing').not.toBeNull();

        await page.goto(
            `${target}${target.includes('?') ? '&' : '?'}templateStyle=${STYLE}`,
            { waitUntil: 'networkidle' }
        );

        const buttons = page.locator(`${PROCLAIM_CONTENT} .btn`);
        const buttonCount = await buttons.count();

        expect(buttonCount, 'No Proclaim buttons rendered, so this checks nothing').toBeGreaterThan(0);

        for (let i = 0; i < buttonCount; i++) {
            const button = buttons.nth(i);

            const [colour, background, label] = await button.evaluate((el) => {
                const style = getComputedStyle(el);

                return [style.color, style.backgroundColor, el.textContent.trim()];
            });

            // A transparent button takes its contrast from whatever is behind it,
            // which axe judges properly in the a11y suite; here only the
            // same-colour case is decidable.
            if (background === 'rgba(0, 0, 0, 0)' || label === '') {
                continue;
            }

            expect(colour, `"${label}" is the same colour as its button`).not.toBe(background);
        }
    });
});
