/**
 * E2E — REST API acceptance on a package-installed site (#1330)
 *
 * Runs against the role=test install (`api-j6test` project), the site
 * `composer test:install` provisions from the built package — not a dev site
 * assembled by symlinks or file copying, which is exactly what hid #1309 and
 * #1310 for three releases. The DB-row and on-disk assertions live in
 * build/verify-api-install.php inside the install harness; this spec covers
 * everything an administrator or API consumer actually touches:
 *
 *   - the plugin is visible and manageable in the Plugins screen
 *   - an unauthenticated request gets 401, NOT 404 — 404 is the signature
 *     all three shipped bugs produce (routes never registered)
 *   - a Joomla API token obtained the way an admin obtains one (their
 *     profile) reaches the API and gets a JSON:API document
 *   - the one remaining setting, public_reads, works when set the way an
 *     admin sets it — through the plugin's own edit form. #1328's lesson:
 *     writing the value straight to the database would pass while the UI
 *     path is broken, so the UI path is the one exercised.
 *
 * Serial: the token test feeds the authenticated request, and the
 * public_reads test must restore the shipped default for the 401 assertion
 * to stay meaningful on re-runs.
 */

const { test, expect } = require('@playwright/test');

const API_SERMONS = '/api/index.php/v1/proclaim/sermons';
const API_INFO = '/api/index.php/v1/proclaim/info';
const API_SERIES = '/api/index.php/v1/proclaim/series';

// Set by the token test, consumed by the later ones. Safe because the describe
// is serial.
let apiToken = '';

test.describe.serial('REST API acceptance (package install) @api', () => {
    test('Proclaim and its webservices plugin are installed and enabled', async ({ page }) => {
        await page.goto(
            '/administrator/index.php?option=com_plugins&filter[search]=proclaim&filter[folder]=webservices',
            { waitUntil: 'networkidle' },
        );

        const rows = page.locator('#pluginList tbody tr');

        expect(
            await rows.count(),
            'plg_webservices_proclaim is not in the Plugins screen. If this site has no '
            + 'Proclaim at all, run `composer test:install` first — this suite asserts the '
            + 'state that harness produces.',
        ).toBeGreaterThan(0);

        // The publish toggle in the row reports state via its icon/task.
        const firstRow = rows.first();
        await expect(firstRow).toContainText(/proclaim/i);
        await expect(
            firstRow.locator('.tbody-icon .icon-publish, a[data-bs-original-title*="Unpublish"], .icon-publish'),
            'plg_webservices_proclaim exists but is disabled — a clean install must come up '
            + 'with the API reachable (#1331).',
        ).toHaveCount(1);
    });

    test('unauthenticated request is denied with 401, not 404', async ({ request }) => {
        const response = await request.get(API_SERMONS);

        expect(
            response.status(),
            '404 from the sermons route means the API routes were never registered — the '
            + 'failure signature of #1309/#1310, which shipped in 10.3.0–10.3.3. 401 is the '
            + 'only acceptable "no" here.',
        ).not.toBe(404);

        expect(response.status()).toBe(401);
    });

    test('unauthenticated request to info is denied with 401, not 404', async ({ request }) => {
        // info is a singleton route registered outside the RESOURCES loop
        // (#1429) — its own regression check that route registration for it
        // actually happened, same reasoning as the sermons check above.
        const response = await request.get(API_INFO);

        expect(response.status()).not.toBe(404);
        expect(response.status()).toBe(401);
    });

    test('a token from the admin profile reaches the API and gets JSON:API', async ({ page, request }) => {
        // Obtain the token the way an administrator does: from their own
        // account, via the header's "Edit Account" link (the com_users.user
        // form — the only backend context the token plugin injects into;
        // com_admin's profile view is not on its list).
        //
        // On a pristine account the plugin REMOVES its token fields from the
        // form entirely — the seed they display is only generated when the
        // user is saved with the plugin active, so a fresh install's
        // installer-created admin has no field at all until saved once.
        // That is the state every CI run starts from.
        // domcontentloaded, not networkidle: the assertion below already waits
        // for the thing this navigation exists to reach, and it waits for that
        // specific element rather than for the whole page to stop talking.
        // Under the dev server, networkidle here was the flake — see the Serve
        // step in e2e.yml.
        await page.goto('/administrator/index.php', { waitUntil: 'domcontentloaded' });

        const editAccount = page.locator('a[href*="task=user.edit"]', { hasText: 'Edit Account' }).first();
        await expect(editAccount, 'No "Edit Account" link in the admin header').toBeAttached();
        await page.goto(await editAccount.getAttribute('href'), { waitUntil: 'domcontentloaded' });

        if (!(await page.locator('#jform_joomlatoken_token').count())) {
            // Pristine account: save once to generate the seed, which lands
            // back on the edit form with the token fields present.
            await page.click('.button-apply');
            await page.waitForLoadState('networkidle');
        }

        const tokenField = page.locator('#jform_joomlatoken_token');

        // Diagnostics worth their weight when this fails on a machine no one
        // can attach a debugger to: where we actually landed, and what
        // Joomla had to say about it.
        const alerts = (await page.locator('joomla-alert, .alert').allInnerTexts())
            .join(' | ').replace(/\s+/g, ' ').slice(0, 300);

        await expect(
            tokenField,
            'No Joomla API Token field on the account form, even after a seed-generating save. '
            + `Landed on: ${page.url()} — messages: ${alerts || '(none)'}`,
        ).toBeAttached();

        const token = await tokenField.inputValue();

        expect(token, 'The profile never produced a token value').not.toBe('');

        apiToken = token;

        const response = await request.get(API_SERMONS, {
            headers: { 'X-Joomla-Token': token },
        });

        expect(response.status()).toBe(200);

        const body = await response.json();

        // A JSON:API document carries its resources under `data`.
        expect(Array.isArray(body.data), 'Expected a JSON:API document with a data array').toBe(true);

        // info is a singleton item, not a list — same token, no re-auth needed.
        const infoResponse = await request.get(API_INFO, {
            headers: { 'X-Joomla-Token': token },
        });

        expect(infoResponse.status()).toBe(200);

        const infoBody = await infoResponse.json();

        expect(infoBody.data?.type).toBe('info');
        expect(
            infoBody.data?.attributes?.version,
            'Expected a non-empty version string from CwmproclaimHelper::getVersion()',
        ).not.toBe('');
    });

    test('list responses actually carry the fields their views declare', async ({ request }) => {
        // #1749: a name in $fieldsToRenderList that the row does not carry is
        // dropped by array_intersect_key() — no null, no notice. Six such
        // fields shipped across four views from 10.3.0. The unit contract test
        // checks the declarations against the schema; this checks the one thing
        // it cannot, that the bytes come back over HTTP.
        expect(apiToken, 'The token test did not run, so this proves nothing').not.toBe('');

        const headers = { 'X-Joomla-Token': apiToken };

        for (const [url, fields] of [
            [API_SERIES, ['series_text', 'description', 'series_thumbnail', 'teacher']],
            [API_SERMONS, ['studytitle', 'studyintro', 'series_id']],
        ]) {
            const body = await (await request.get(url, { headers })).json();
            const first = body.data?.[0];

            expect(first, `${url} returned no rows, so nothing here was checked`).toBeTruthy();

            for (const field of fields) {
                expect(
                    Object.keys(first.attributes ?? {}),
                    `${url} declares "${field}" but the response has no such attribute — the list query does `
                    + 'not select it, or it is not a column at all.',
                ).toContain(field);
            }
        }
    });

    test('public_reads set through the plugin UI opens and closes anonymous reads', async ({ page, request }) => {
        // Open the plugin's edit form from the Plugins screen — the same
        // path an administrator walks.
        await page.goto(
            '/administrator/index.php?option=com_plugins&filter[search]=proclaim&filter[folder]=webservices',
            { waitUntil: 'networkidle' },
        );
        await page.locator('#pluginList tbody tr a[href*="task=plugin.edit"]').first().click();
        await page.waitForLoadState('networkidle');

        const setPublicReads = async (value) => {
            await page.locator(`label[for="jform_params_public_reads${value}"]`).click();
            await page.click('.button-apply');
            await page.waitForLoadState('networkidle');
        };

        // Open anonymous reads…
        await setPublicReads(1);
        let response = await request.get(API_SERMONS);
        expect(
            response.status(),
            'public_reads=Yes saved through the UI, but an anonymous read is still rejected',
        ).toBe(200);

        // …and restore the shipped default, which must close them again.
        await setPublicReads(0);
        response = await request.get(API_SERMONS);
        expect(
            response.status(),
            'public_reads=No saved through the UI, but anonymous reads stayed open',
        ).toBe(401);
    });
});
