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
 *   - the media resource's full write lifecycle round-trips (#2135): create,
 *     read back, amend a field via PATCH, confirm the amendment is visible,
 *     trash, delete. Every prior test here only ever created — nothing
 *     exercised PATCH or DELETE, which is exactly how #2135 shipped invisible.
 *
 * Serial: the token test feeds the authenticated request, and the
 * public_reads test must restore the shipped default for the 401 assertion
 * to stay meaningful on re-runs.
 */

const { test, expect } = require('@playwright/test');

const API_SERMONS = '/api/index.php/v1/proclaim/sermons';
const API_INFO = '/api/index.php/v1/proclaim/info';
const API_SERIES = '/api/index.php/v1/proclaim/series';
const API_MEDIA = '/api/index.php/v1/proclaim/media';

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

    test('media: create, read, amend, confirm, trash and delete round-trip (#2135)', async ({ request }) => {
        expect(apiToken, 'The token test did not run, so this proves nothing').not.toBe('');

        const headers = { 'X-Joomla-Token': apiToken };

        // study 1 and server 1 are both seeded by install.mysql.utf8.sql —
        // present on any site this harness provisions, not a fixture this
        // test has to build.
        //
        // createdate is chosen to prove the write/read round-trip is now
        // symmetric: what is sent here must come back unchanged (#2135
        // Defect 4). Before the fix, POST converted it as local-to-UTC and
        // GET returned the stored value unconverted, so a value written and
        // read back drifted by the site's UTC offset.
        const createdate = '2025-12-06 17:05:00';

        const createResponse = await request.post(API_MEDIA, {
            headers,
            data: {
                study_id: 1,
                server_id: 1,
                language: '*',
                createdate,
                params: { filename: 'zz-2135-roundtrip.mp3' },
            },
        });

        // Joomla's ApiController::add() never sets a 201 -- displayItem()
        // leaves the application's default 200 in place. Matching that
        // rather than the "correct" REST convention, since that is what
        // every other resource on this API actually returns.
        expect(createResponse.status(), await createResponse.text()).toBe(200);

        const created = await createResponse.json();
        const mediaId = created.data?.id;

        expect(mediaId, 'POST did not return a resource id').toBeTruthy();

        try {
            // Read it back — confirms the record exists and createdate
            // survived the write path unchanged.
            let getResponse = await request.get(`${API_MEDIA}/${mediaId}`, { headers });
            expect(getResponse.status()).toBe(200);

            let body = await getResponse.json();
            expect(
                body.data?.attributes?.createdate,
                'createdate drifted on the round trip — the write and read conventions disagree (#2135 Defect 4)',
            ).toBe(createdate);
            expect(
                body.data?.attributes?.podcast_id ?? '',
                'A freshly created record should carry no podcast link yet',
            ).toBe('');

            // PATCH omitting every field but podcast_id — this is the exact
            // shape that 500'd before the fix (#2135 Defect 1): the core
            // ApiController backfills every other column from the raw table
            // row, including params as a JSON string, which the model's old
            // Registry::loadArray() call could not accept.
            const patchResponse = await request.patch(`${API_MEDIA}/${mediaId}`, {
                headers,
                data: { podcast_id: '1' },
            });
            expect(patchResponse.status(), await patchResponse.text()).toBe(200);

            // Confirm the amendment is actually visible — podcast_id was
            // write-only before the fix (#2135 Defect 3): a caller could set
            // it and never read it back, so a record could look completely
            // healthy while being silently absent from the podcast feed.
            //
            // getItem() explodes a non-empty podcast_id into an array for
            // display (pre-existing model behaviour, not part of this fix) —
            // an empty one stays the plain string asserted above.
            getResponse = await request.get(`${API_MEDIA}/${mediaId}`, { headers });
            body = await getResponse.json();
            expect(
                body.data?.attributes?.podcast_id,
                'podcast_id was set via PATCH but is not visible in a subsequent GET',
            ).toEqual(['1']);

            // Trash before delete — reachable only because PATCH now works
            // (#2135 Defect 2: trashing is itself a PATCH of published=-2, so
            // it inherited Defect 1's failure and made every API-created
            // record permanently undeletable through the API).
            const trashResponse = await request.patch(`${API_MEDIA}/${mediaId}`, {
                headers,
                data: { published: '-2' },
            });
            expect(trashResponse.status(), await trashResponse.text()).toBe(200);

            // Core's own response cycle renders and echoes a body
            // unconditionally (CMSApplication::doExecute() always calls
            // render() then respond(), and AbstractWebApplication::respond()
            // always does `echo $this->getBody()`) — nothing suppresses that
            // for a 204, which by HTTP/1.1 must carry no body at all. Every
            // resource's successful delete() sends this, framework-wide; it
            // was never reachable for media before this fix (Defect 2), so
            // nothing had ever exercised it here.
            //
            // A tolerant server (Apache, used for local verification) glosses
            // over the extra bytes; PHP's built-in dev server plus a strict
            // HTTP/1.1 client (Playwright's Node parser, used in CI) does
            // not, and throws before a response object even exists to read a
            // status from. That is a core framing defect, not this fix's to
            // carry — confirmed by tracing the framework, not guessed. Filed
            // as its own issue (#2167).
            let deleteOk = false;

            try {
                const deleteResponse = await request.delete(`${API_MEDIA}/${mediaId}`, { headers });
                expect(deleteResponse.status(), await deleteResponse.text()).toBe(204);
                deleteOk = true;
            } catch (deleteError) {
                if (!/Parse Error/.test(String(deleteError?.message))) {
                    throw deleteError;
                }
            }

            // The parse failure happens after the server has already written
            // its status line, so a clean HTTP client proves the delete
            // itself succeeded independently of whether THIS client could
            // read the malformed response: the record no longer answers as a
            // live item. GET on a missing id is its own separate, pre-existing
            // gap (500 instead of 404), so this checks for "not 200" rather
            // than a specific code.
            if (!deleteOk) {
                const verify = await request.get(`${API_MEDIA}/${mediaId}`, { headers });
                expect(
                    verify.status(),
                    'DELETE\'s response could not be parsed, and a follow-up GET still returned 200 — '
                    + 'the record was not actually removed',
                ).not.toBe(200);
            }
        } catch (error) {
            // The record must not survive a failed assertion — leaving it
            // behind would poison every later run of this suite with a
            // duplicate that was never meant to persist. Trash first: a
            // failure before the trash step above leaves the record
            // published, and delete on a published record is refused.
            await request.patch(`${API_MEDIA}/${mediaId}`, { headers, data: { published: '-2' } }).catch(() => {});
            await request.delete(`${API_MEDIA}/${mediaId}`, { headers }).catch(() => {});
            throw error;
        }

        // Not re-reading the deleted record here on purpose: a GET for an id
        // that no longer exists is a separate, pre-existing defect (500
        // instead of 404 — confirmed present before this fix too), out of
        // #2135's scope. 204 from the DELETE above is the round trip's own
        // confirmation that removal worked.
    });
});
