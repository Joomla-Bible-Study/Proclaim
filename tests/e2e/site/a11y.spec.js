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
/**
 * How many records to open per detail view. Each is a full page load and an axe
 * scan, so this trades runtime for the chance of catching a record whose data
 * shape differs from the first one's.
 */
const DETAIL_SAMPLE = 5;

// ⚠️ .proclaim-grid-card is in every one of these. The selectors here used to
// name .proclaim-item and .effects, which the sermons and series-podcast
// listings do not render at all — so those two tests skipped on every run
// rather than testing anything, and reported themselves as skipped rather than
// failed.
const DETAILS = [
    ['sermon detail', 'cwmsermons', '.proclaim-grid-card a[href], .proclaim-item a[href]'],
    ['teacher detail', 'cwmteachers', '.proclaim-grid-card a[href], .proclaim-item a[href]'],
    ['series detail', 'cwmseriesdisplays', '.proclaim-grid-card a[href], .proclaim-item a[href]'],
    ['series podcast detail', 'cwmseriespodcastlist', '.proclaim-grid-card a[href], .effects a[href]'],
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

            const links = page.locator(itemSelector);
            const total = await links.count();

            // Nothing to open on an empty database — skip rather than fail,
            // the same way the rest of the site suite handles missing data.
            if (!total) {
                test.skip(true, `No records on the ${listing} listing to open`);
            }

            // ⚠️ Sample several records, not just the first. Detail pages differ
            // by what the record actually has: a teacher with no phone rendered
            // `<a href="tel:"></a>`, a link with no accessible name, on most of
            // the teachers on the site — and this suite passed for months
            // because whichever record happened to sort first had a phone.
            // Whatever sorts first is data, not a decision.
            const hrefs = [];

            for (let i = 0; i < total && hrefs.length < DETAIL_SAMPLE; i++) {
                const href = await links.nth(i).getAttribute('href');

                // ⚠️ The item selectors match every link inside a card, and a
                // card carries mailto: and tel: links too. Navigating to one
                // aborts the request and fails the test for the wrong reason.
                if (!href || !href.startsWith('/') || hrefs.includes(href)) {
                    continue;
                }

                hrefs.push(href);
            }

            if (!hrefs.length) {
                test.skip(true, `No navigable ${listing} detail links found`);
            }

            for (const href of hrefs) {
                await page.goto(href, { waitUntil: 'networkidle' });
                await expect(page.locator(PROCLAIM_CONTENT).first()).toBeVisible({ timeout: 15000 });

                await expectNoViolations(page, expect, { include: PROCLAIM_CONTENT });
            }
        });
    }

    // The media popup renders as a bare window the player opens, so there is no
    // link to click — the id has to come from somewhere.
    //
    // ⚠️ This used to request mediaid=1 and skip when it did not resolve. Media
    // ids on a site of any age do not start at 1 (on the reference database the
    // lowest is 838), so the request 404'd and the test skipped itself on every
    // run, reporting as skipped rather than failed. Same shape as the selector
    // bug noted above DETAILS: green by never running.
    //
    // The id now comes from the listing's own player links: Cwmmedia renders
    // each as `a.fancybox_player` carrying the media file's id in data-id. That
    // is real data on whatever database the suite runs against, and needs no
    // navigation, so there is no stale-locator problem either.
    test('media popup meets WCAG AA', async ({ page }) => {
        await page.goto('/?option=com_proclaim&view=cwmsermons', { waitUntil: 'networkidle' });

        const mediaId = await page
            .locator('a.fancybox_player[data-id]')
            .first()
            .getAttribute('data-id')
            .catch(() => null);

        // An empty database, or a template whose player renders no media link.
        if (!mediaId || !/^\d+$/.test(mediaId)) {
            test.skip(true, 'No media player link on the sermon listing to take an id from');
        }

        const response = await page.goto(
            `/?option=com_proclaim&view=cwmpopup&mediaid=${mediaId}&tmpl=component`,
            { waitUntil: 'networkidle' }
        );

        expect(response.status()).toBeLessThan(400);
        await expect(page.locator(PROCLAIM_CONTENT).first()).toBeAttached({ timeout: 15000 });

        // The accessible name is the one part of a third-party embed we control,
        // so assert it here rather than leaving it to the scan. Every addon sets
        // it via CWMAddon::frameTitle(); axe cannot check it once the frame is
        // excluded below.
        const frame = page.locator('iframe').first();

        if (await frame.count()) {
            await expect(frame).toHaveAttribute('title', /\S/);
        }

        // ⚠️ The player frame is excluded because the document inside it is the
        // provider's. YouTube's player reports aria-allowed-attr and
        // aria-prohibited-attr against its own markup, and nothing we change
        // fixes it — but everything Proclaim renders around it is still scanned.
        // Narrower would be better; axe's frame-chain exclude does not reach
        // into a cross-origin player, so this is the honest boundary.
        await expectNoViolations(page, expect, {
            include: PROCLAIM_CONTENT,
            exclude: ['iframe'],
        });
    });
});
