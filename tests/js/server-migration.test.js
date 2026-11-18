'use strict';

/**
 * Tests for server-migration.es6.js
 *
 * Tests the migration plan building logic and progress calculations.
 *
 * @package  Proclaim.Tests
 * @since    10.1.0
 */

require('./setup');

const assert = require('node:assert/strict');
const { describe, test } = require('node:test');

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
            assert.strictEqual(plan.length, 0);
        });

        test('create_new groups are included', () => {
            const selections = {
                '1_youtube': 'create_new',
                '1_vimeo': 'skip',
                '1_local': 'skip',
            };

            const plan = buildPlan(selections);
            assert.strictEqual(plan.length, 1);
            assert.strictEqual(plan[0].detectedType, 'youtube');
            assert.strictEqual(plan[0].targetValue, 'create_new');
            assert.strictEqual(plan[0].count, 5);
        });

        test('existing server groups are included', () => {
            const selections = {
                '1_youtube': 'existing_10',
                '1_vimeo': 'existing_20',
                '1_local': 'skip',
            };

            const plan = buildPlan(selections);
            assert.strictEqual(plan.length, 2);
            assert.strictEqual(plan[0].targetValue, 'existing_10');
            assert.strictEqual(plan[1].targetValue, 'existing_20');
        });

        test('unknown type maps to embed target type', () => {
            const selections = {
                '2_soundcloud': 'skip',
                '2_unknown': 'create_new',
            };

            const plan = buildPlan(selections);
            assert.strictEqual(plan.length, 1);
            assert.strictEqual(plan[0].detectedType, 'unknown');
            assert.strictEqual(plan[0].targetType, 'embed');
        });

        test('plan preserves legacy server params', () => {
            const selections = {
                '1_youtube': 'create_new',
            };

            const plan = buildPlan(selections);
            assert.deepStrictEqual(plan[0].legacyServerParams, {
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
            assert.strictEqual(plan.length, 3);

            const totalFiles = plan.reduce((sum, g) => sum + g.count, 0);
            assert.strictEqual(totalFiles, 5 + 3 + 4); // youtube + vimeo + soundcloud
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
            assert.strictEqual(calculateProgress(0, 100), 0);
        });

        test('50 of 100 is 50%', () => {
            assert.strictEqual(calculateProgress(50, 100), 50);
        });

        test('100 of 100 is 100%', () => {
            assert.strictEqual(calculateProgress(100, 100), 100);
        });

        test('1 of 3 rounds to 33%', () => {
            assert.strictEqual(calculateProgress(1, 3), 33);
        });

        test('2 of 3 rounds to 67%', () => {
            assert.strictEqual(calculateProgress(2, 3), 67);
        });

        test('0 total files returns 100%', () => {
            assert.strictEqual(calculateProgress(0, 0), 100);
        });

        test('partial batch progress', () => {
            // After migrating 25 of 75 total files
            assert.strictEqual(calculateProgress(25, 75), 33);
        });

        test('multi-group cumulative progress', () => {
            // Group 1: 5 youtube files, Group 2: 3 vimeo files = 8 total
            // After group 1 complete (5/8)
            assert.strictEqual(calculateProgress(5, 8), 63);
            // After group 2 complete (8/8)
            assert.strictEqual(calculateProgress(8, 8), 100);
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
                assert.notStrictEqual(TYPE_BADGES[type], undefined);
                assert.strictEqual(typeof TYPE_BADGES[type], 'string');
            }
        });

        test('unknown types fall back gracefully', () => {
            const badge = TYPE_BADGES['nonexistent'] || 'secondary';
            assert.strictEqual(badge, 'secondary');
        });
    });
});
