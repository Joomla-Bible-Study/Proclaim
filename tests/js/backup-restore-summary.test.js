/**
 * @jest-environment jsdom
 */

/**
 * #1958: a finished restore reported one line — "Import completed
 * successfully" — while `finalizeImportXHR` had already computed and returned
 * five counts plus the pending server migration. For a restore that is
 * genuinely finished that line is right; for one that is not, it reads as done
 * while work remains.
 *
 * These exercise the shipped `buildRestoreSummary()` rather than a copy of its
 * logic. The class exposes itself on `window.ProclaimBackupRestore`, so the
 * real function is reachable — a test that re-implemented the rules would pass
 * against itself and prove nothing about what ships.
 *
 * @package  Proclaim.Tests
 * @since    __DEPLOY_VERSION__
 */

/**
 * Load the module and hand back the instance it exposes.
 *
 * @returns {object} The ProclaimBackupRestore instance.
 */
function loadModule() {
    document.body.innerHTML = '';

    // Rebuilt rather than patched: the suite resets globals between tests.
    // Text._ returns the key itself, so an assertion naming a key checks that
    // the key was chosen — not that a translation happens to read a certain way.
    global.Joomla = {
        Text: { _: (key) => key },
        getOptions: () => '',
        renderMessages: () => {},
    };

    jest.isolateModules(() => {
        require('../../build/media_source/js/backup-restore.es6.js');
    });

    document.dispatchEvent(new Event('DOMContentLoaded'));

    return window.ProclaimBackupRestore;
}

describe('Restore completion summary (#1958)', () => {
    let app;

    beforeEach(() => {
        app = loadModule();
    });

    describe('what was restored', () => {
        it('lists only the counts that are non-zero', () => {
            const summary = app.buildRestoreSummary({
                tables_restored: 23,
                tasks_restored: 0,
                templatecodes_created: 4,
                auto_increment_fixes: 0,
                config_restored: true,
            });

            expect(summary).toContain('JBS_IBM_SUMMARY_RESTORED');
            expect(summary).toContain('JBS_IBM_SUMMARY_TABLES');
            expect(summary).toContain('JBS_IBM_SUMMARY_TEMPLATECODES');
            expect(summary).toContain('JBS_IBM_SUMMARY_CONFIG');

            // A zero is not an achievement worth a bullet.
            expect(summary).not.toContain('JBS_IBM_SUMMARY_TASKS');
            expect(summary).not.toContain('JBS_IBM_SUMMARY_AUTO_INCREMENT');
        });

        it('omits the section entirely when nothing was restored', () => {
            const summary = app.buildRestoreSummary({
                tables_restored: 0,
                tasks_restored: 0,
                templatecodes_created: 0,
                auto_increment_fixes: 0,
                config_restored: false,
            });

            expect(summary).not.toContain('JBS_IBM_SUMMARY_RESTORED');
        });
    });

    describe('what still needs attention', () => {
        it('says so plainly when nothing does', () => {
            const summary = app.buildRestoreSummary({ tables_restored: 23 });

            expect(summary).toContain('JBS_IBM_SUMMARY_NOTHING_OUTSTANDING');
            expect(summary).not.toContain('JBS_IBM_SUMMARY_ATTENTION');
        });

        it('reports servers left on the legacy type', () => {
            const summary = app.buildRestoreSummary({
                pending_migration: { servers: 3, media: 2008 },
            });

            expect(summary).toContain('JBS_IBM_SUMMARY_ATTENTION');
            expect(summary).toContain('JBS_IBM_SERVERS_PENDING');
            expect(summary).not.toContain('JBS_IBM_SUMMARY_NOTHING_OUTSTANDING');
        });

        it('reports a missing media folder when there are records to miss it', () => {
            const summary = app.buildRestoreSummary({
                media_status: { rows: 2094, dir_exists: false, dir_empty: false },
            });

            expect(summary).toContain('JBS_IBM_MEDIA_DIR_MISSING');
        });

        it('distinguishes an empty media folder from an absent one', () => {
            const summary = app.buildRestoreSummary({
                media_status: { rows: 2094, dir_exists: true, dir_empty: true },
            });

            expect(summary).toContain('JBS_IBM_MEDIA_DIR_EMPTY');
            expect(summary).not.toContain('JBS_IBM_MEDIA_DIR_MISSING');
        });

        /**
         * ⚠️ The guard that stops this crying wolf. A site with no media
         * records and no media folder is a site with no media, and a fresh
         * install would otherwise be told its restore is incomplete.
         */
        it('says nothing about media when there are no media records', () => {
            const summary = app.buildRestoreSummary({
                media_status: { rows: 0, dir_exists: false, dir_empty: false },
            });

            expect(summary).not.toContain('JBS_IBM_MEDIA_DIR_MISSING');
            expect(summary).not.toContain('JBS_IBM_MEDIA_DIR_EMPTY');
            expect(summary).toContain('JBS_IBM_SUMMARY_NOTHING_OUTSTANDING');
        });
    });

    it('survives a payload with nothing in it', () => {
        expect(() => app.buildRestoreSummary({})).not.toThrow();
        expect(app.buildRestoreSummary({})).toContain('JBS_IBM_IMPORT_COMPLETE');
    });
});
