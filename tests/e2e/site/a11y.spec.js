/**
 * E2E — WCAG 2.2 AA, public site views
 *
 * Scans every page a visitor can reach. The scan is confined to
 * `.com-proclaim`, the wrapper every Proclaim site view renders, because the
 * surrounding page is the site's Joomla template and other extensions —
 * markup Proclaim does not own and cannot fix. (`.com-proclaim` rather than
 * the inner `.proclaim-main-content`: the outer wrapper also contains the
 * skip link, which is ours and worth judging.)
 *
 * These tests degrade like the rest of the site suite: with no sermon data the
 * page still renders a container, and an empty container is still scanned.
 * Detail views that need a record to exist reach it by clicking through from
 * a listing, and skip rather than fail when the database is empty.
 */

const { test, expect } = require('@playwright/test');
const { expectNoViolations } = require('../helpers/axe');

const PROCLAIM_CONTENT = '.com-proclaim';

/**
 * Every site view reachable without a record id.
 *
 * cwmlatest currently renders an empty stub template; scanning it is trivially
 * green today, but it still proves the view renders without error, and the
 * scan is already in place for whenever the template grows content.
 */
const LISTINGS = [
    ['sermon listing', 'cwmsermons'],
    ['teacher listing', 'cwmteachers'],
    // The site view is cwmseriesdisplays — cwmseries is the *admin* view name
    // and 404s on the front end. Worth stating, because the two halves of the
    // component do not use matching names and the mistake looks like a bug.
    ['series listing', 'cwmseriesdisplays'],
    ['series podcast listing', 'cwmseriespodcastlist'],
    ['landing page', 'cwmlandingpage'],
    ['latest sermons', 'cwmlatest'],
    ['terms of use', 'cwmterms'],
];

/**
 * Detail views, each reached from its listing so the test never has to guess
 * a record id. [label, listing view, selector for the first item link]
 */
const DETAILS = [
    ['sermon detail', 'cwmsermons', '.proclaim-item a[href]'],
    ['teacher detail', 'cwmteachers', '.proclaim-item a[href], .teacher-card a[href]'],
    ['series detail', 'cwmseriesdisplays', '.proclaim-item a[href], .series-card a[href]'],
    ['series podcast detail', 'cwmseriespodcastlist', '.effects a[href]'],
];

test.describe('Site accessibility (WCAG 2.2 AA) @a11y', () => {
    for (const [label, view] of LISTINGS) {
        test(`${label} meets WCAG AA`, async ({ page }) => {
            const response = await page.goto(`/?option=com_proclaim&view=${view}`, {
                waitUntil: 'networkidle',
            });

            expect(response.status()).toBeLessThan(400);
            // .first(): a page can carry several .com-proclaim regions — the
            // view plus a published Proclaim module — and axe scans them all.
            // attached, not visible: a legitimately empty view (cwmterms with
            // nothing configured, cwmlatest's stub) renders a zero-height
            // container, which Playwright counts as hidden. A 500 or blank
            // response still fails — there is no container to attach.
            await expect(page.locator(PROCLAIM_CONTENT).first()).toBeAttached({ timeout: 15000 });

            await expectNoViolations(page, expect, { include: PROCLAIM_CONTENT });
        });
    }

    for (const [label, listing, itemSelector] of DETAILS) {
        test(`${label} meets WCAG AA`, async ({ page }) => {
            await page.goto(`/?option=com_proclaim&view=${listing}`, {
                waitUntil: 'networkidle',
            });

            const firstLink = page.locator(itemSelector).first();

            // Nothing to open on an empty database — skip rather than fail,
            // the same way the rest of the site suite handles missing data.
            if (!(await firstLink.count())) {
                test.skip(true, `No records on the ${listing} listing to open`);
            }

            await firstLink.click();
            await page.waitForLoadState('networkidle');
            await expect(page.locator(PROCLAIM_CONTENT).first()).toBeVisible({ timeout: 15000 });

            await expectNoViolations(page, expect, { include: PROCLAIM_CONTENT });
        });
    }

    // The media popup needs a mediaid and renders as a bare window the player
    // opens. There is no listing to click through — reach it directly and
    // skip when the id does not resolve to real media on this database.
    test('media popup meets WCAG AA', async ({ page }) => {
        const response = await page.goto('/?option=com_proclaim&view=cwmpopup&mediaid=1&tmpl=component', {
            waitUntil: 'networkidle',
        });

        if (response.status() >= 400) {
            test.skip(true, 'No media id 1 on this database');
        }

        const container = page.locator(PROCLAIM_CONTENT);

        if (!(await container.count())) {
            test.skip(true, 'Popup did not render a Proclaim container for media id 1');
        }

        await expectNoViolations(page, expect, { include: PROCLAIM_CONTENT });
    });
});
