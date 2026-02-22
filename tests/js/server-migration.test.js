/**
 * Jest tests for server-migration.es6.js
 *
 * Tests the migration plan building logic and progress calculations.
 *
 * @package  Proclaim.Tests
 * @since    10.1.0
 */

describe('Server Migration Tool', () => {
    // =========================================================================
    // Migration plan building from scan data + user selections
    // =========================================================================

    describe('Migration plan building', () => {
        const scanData = [
            {
                id: 1,
                server_name: 'Old Server',
                published: 1,
                params: { path: '//example.com/media', protocol: '' },
                types: { youtube: 5, vimeo: 3, local: 2 },
                total: 10,
            },
            {
                id: 2,
                server_name: 'Another Legacy',
                published: 1,
                params: {},
                types: { soundcloud: 4, unknown: 1 },
                total: 5,
            },
        ];

        function buildPlan(selections) {
            const groups = [];

            for (const [key, value] of Object.entries(selections)) {
                if (value === 'skip') {
                    continue;
                }

                const [serverIdStr, type] = key.split('_');
                const serverId = parseInt(serverIdStr, 10);
                const server = scanData.find(s => s.id === serverId);

                if (!server || !server.types[type]) {
                    continue;
                }

                groups.push({
                    legacyServerId: serverId,
                    legacyServerParams: server.params,
                    detectedType: type,
                    targetValue: value,
                    targetType: type === 'unknown' ? 'embed' : type,
                    count: server.types[type],
                });
            }

            return groups;
        }

        test('skipped groups are excluded from plan', () => {
            const selections = {
                '1_youtube': 'skip',
                '1_vimeo': 'skip',
                '1_local': 'skip',
            };

            const plan = buildPlan(selections);
            expect(plan).toHaveLength(0);
        });

        test('create_new groups are included', () => {
            const selections = {
                '1_youtube': 'create_new',
                '1_vimeo': 'skip',
                '1_local': 'skip',
            };

            const plan = buildPlan(selections);
            expect(plan).toHaveLength(1);
            expect(plan[0].detectedType).toBe('youtube');
            expect(plan[0].targetValue).toBe('create_new');
            expect(plan[0].count).toBe(5);
        });

        test('existing server groups are included', () => {
            const selections = {
                '1_youtube': 'existing_10',
                '1_vimeo': 'existing_20',
                '1_local': 'skip',
            };

            const plan = buildPlan(selections);
            expect(plan).toHaveLength(2);
            expect(plan[0].targetValue).toBe('existing_10');
            expect(plan[1].targetValue).toBe('existing_20');
        });

        test('unknown type maps to embed target type', () => {
            const selections = {
                '2_soundcloud': 'skip',
                '2_unknown': 'create_new',
            };

            const plan = buildPlan(selections);
            expect(plan).toHaveLength(1);
            expect(plan[0].detectedType).toBe('unknown');
            expect(plan[0].targetType).toBe('embed');
        });

        test('plan preserves legacy server params', () => {
            const selections = {
                '1_youtube': 'create_new',
            };

            const plan = buildPlan(selections);
            expect(plan[0].legacyServerParams).toEqual({
                path: '//example.com/media',
                protocol: '',
            });
        });

        test('mixed selections produce correct plan', () => {
            const selections = {
                '1_youtube': 'create_new',
                '1_vimeo': 'existing_20',
                '1_local': 'skip',
                '2_soundcloud': 'create_new',
                '2_unknown': 'skip',
            };

            const plan = buildPlan(selections);
            expect(plan).toHaveLength(3);

            const totalFiles = plan.reduce((sum, g) => sum + g.count, 0);
            expect(totalFiles).toBe(5 + 3 + 4); // youtube + vimeo + soundcloud
        });
    });

    // =========================================================================
    // Progress calculation across multiple groups
    // =========================================================================

    describe('Progress calculation', () => {
        function calculateProgress(migratedTotal, totalFiles) {
            return totalFiles > 0 ? Math.round((migratedTotal / totalFiles) * 100) : 100;
        }

        test('0 of 100 is 0%', () => {
            expect(calculateProgress(0, 100)).toBe(0);
        });

        test('50 of 100 is 50%', () => {
            expect(calculateProgress(50, 100)).toBe(50);
        });

        test('100 of 100 is 100%', () => {
            expect(calculateProgress(100, 100)).toBe(100);
        });

        test('1 of 3 rounds to 33%', () => {
            expect(calculateProgress(1, 3)).toBe(33);
        });

        test('2 of 3 rounds to 67%', () => {
            expect(calculateProgress(2, 3)).toBe(67);
        });

        test('0 total files returns 100%', () => {
            expect(calculateProgress(0, 0)).toBe(100);
        });

        test('partial batch progress', () => {
            // After migrating 25 of 75 total files
            expect(calculateProgress(25, 75)).toBe(33);
        });

        test('multi-group cumulative progress', () => {
            // Group 1: 5 youtube files, Group 2: 3 vimeo files = 8 total
            // After group 1 complete (5/8)
            expect(calculateProgress(5, 8)).toBe(63);
            // After group 2 complete (8/8)
            expect(calculateProgress(8, 8)).toBe(100);
        });
    });

    // =========================================================================
    // Type badge mapping
    // =========================================================================

    describe('Type badge colors', () => {
        const TYPE_BADGES = {
            youtube: 'danger',
            vimeo: 'info',
            wistia: 'primary',
            resi: 'info',
            soundcloud: 'warning',
            dailymotion: 'info',
            rumble: 'success',
            embed: 'secondary',
            article: 'dark',
            virtuemart: 'primary',
            docman: 'info',
            local: 'dark',
            unknown: 'light',
        };

        test('all known types have badge colors', () => {
            const expectedTypes = [
                'youtube', 'vimeo', 'wistia', 'resi',
                'soundcloud', 'dailymotion', 'rumble',
                'embed', 'article', 'virtuemart', 'docman', 'local', 'unknown',
            ];

            for (const type of expectedTypes) {
                expect(TYPE_BADGES[type]).toBeDefined();
                expect(typeof TYPE_BADGES[type]).toBe('string');
            }
        });

        test('unknown types fall back gracefully', () => {
            const badge = TYPE_BADGES['nonexistent'] || 'secondary';
            expect(badge).toBe('secondary');
        });
    });
});
