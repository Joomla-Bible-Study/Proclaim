# Proclaim E2E suite

Browser tests (Playwright) covering the admin and site halves of the
component, including the WCAG 2.2 AA accessibility gate. They run against
**real local Joomla installs**, not fixtures — which is why they are not in
CI (nothing there to point them at) and instead gate releases via
`composer test:release`.

## Prerequisites

1. **Two dev sites**, a Joomla 5 and a Joomla 6 install with Proclaim
   deployed (symlinked via `composer symlink` is the usual arrangement).
2. **`build.properties`** in the repo root (gitignored; copy
   `build.dist.properties` and fill in):

   ```properties
   builder.j5dev.url      = https://j5-dev.local:8890
   builder.j5dev.username = admin
   builder.j5dev.password = …
   builder.j6dev.url      = https://j6-dev.local:8890
   builder.j6dev.username = admin
   builder.j6dev.password = …
   # or a shared fallback for both:
   builder.joomla_username = admin
   builder.joomla_password = …
   ```

3. **Playwright's Chromium** — one-time:

   ```bash
   npx playwright install chromium
   ```

   The suite runs real Chromium in new headless mode (`channel: 'chromium'`),
   not `chrome-headless-shell`: the accessibility rules `color-contrast` and
   `target-size` measure rendered output, and a pass from a renderer users
   don't run is a weak basis for a conformance claim.

## Running

```bash
composer test:e2e     # everything (admin/site × J5/J6, plus api-test if configured)
composer test:a11y    # WCAG 2.2 AA scans only — J6 projects only
composer test:api     # clean package install + REST API acceptance (#1330)
npx playwright test --project=admin-j6        # one project
npx playwright test --ui                      # interactive debugging
npm run test:e2e:report                       # open the last HTML report
```

The a11y specs are J6-only by design (Proclaim renders identical markup on
both platforms; J5 exists to catch Joomla-version behaviour differences,
which the functional specs cover). `composer test:a11y` passes
`--project=admin-j6 --project=site-j6` so a J5 hiccup can never block an
accessibility run.

## The API acceptance project

`tests/e2e/api/` runs against the **role=test** install — whichever entry in
`build.properties` carries `builder.<id>.role = test`, discovered by role
rather than by name. That is the site `composer test:install` provisions
from the built package, which is the whole point: #1309/#1310/#1328 all
shipped because every other layer tested a site assembled by something
other than the installer. `composer test:api` chains the clean install and
the spec; with no role=test install configured, the project simply doesn't
exist. The DB-row and on-disk halves of the assertion live in
`build/verify-api-install.php`, inside `composer test:install` itself.

## Authentication

`global-setup.js` logs into the admin of each site **the selected projects
actually use** and saves the session under `tests/e2e/.auth/` — only the
`admin-*` projects consume a session; the `site-*` projects browse
anonymously. A still-valid saved session is reused, and a login that does
run is retried with backoff before it may fail the run (#1342).

## Failure modes worth knowing

- **"Page is an error page"** — the guard against scanning a Joomla error
  page and calling it compliant. Usually a wrong view name in a spec or a
  broken install.
- **"PHP error output leaked into the page"** — a dev site with
  `display_errors` printed a Deprecated/Warning/Fatal into the markup. That
  is a real PHP bug in whatever rendered the page; fix it rather than the
  test. (Twice these have surfaced as bogus "contrast violations" on
  Xdebug's stack-trace tables.)
- **Data-dependent skips** — detail views reached by clicking the first row
  of a listing skip, not fail, on an empty database.

## Where things live

| Path | What |
|---|---|
| `admin/`, `site/` | The specs; `a11y.spec.js` in each is the WCAG gate |
| `helpers/axe.js` | Shared axe-core wrapper: WCAG 2.2 AA tags, readable failure reports, error-page + PHP-leak guards |
| `global-setup.js` | Site-aware admin authentication (see above) |
| `.auth/` | Saved session state (gitignored) |
| `../../playwright.config.js` | Projects, base URLs from build.properties, real-Chromium channel |
