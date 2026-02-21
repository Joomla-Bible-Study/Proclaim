/* eslint-disable no-console */
"use strict";

/**
 * Vendor Dependency Version Checker
 *
 * Reports the status of all project dependencies in three groups:
 *   1. Vendor Libraries  — bundled with the component (chart.js, fancybox, intl-tel-input, sortablejs)
 *   2. Dev Dependencies  — npm build tools
 *   3. PHP Dependencies  — Composer packages
 *
 * Exit code 0 = all current, 1 = updates available (CI-friendly).
 *
 * Usage:  node build/check-vendor-versions.js
 *         composer vendor:check
 *
 * @since 10.1.0
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const ROOT = path.resolve(__dirname, '..');

// ─── Helpers ────────────────────────────────────────────────────────────────

/**
 * Run a shell command and return trimmed stdout (empty string on failure).
 */
function run(cmd, opts = {}) {
    try {
        return execSync(cmd, { cwd: ROOT, encoding: 'utf8', timeout: 30000, ...opts }).trim();
    } catch {
        return '';
    }
}

/**
 * Read a JSON file, return parsed object or null.
 */
function readJson(filePath) {
    try {
        return JSON.parse(fs.readFileSync(filePath, 'utf8'));
    } catch {
        return null;
    }
}

/**
 * Get the installed version of an npm package from node_modules.
 */
function npmInstalledVersion(pkg) {
    const pkgJson = readJson(path.join(ROOT, 'node_modules', pkg, 'package.json'));
    return pkgJson ? pkgJson.version : '?';
}

/**
 * Get the latest published version of an npm package.
 */
function npmLatestVersion(pkg) {
    const v = run(`npm view ${pkg} version 2>/dev/null`);
    return v || '?';
}

/**
 * Get fancybox latest version from npm (@fancyapps/ui).
 */
function fancyboxLatestVersion() {
    return npmLatestVersion('@fancyapps/ui');
}

// ─── Table Rendering ────────────────────────────────────────────────────────

/**
 * Render an ASCII table with box-drawing characters.
 *
 * @param {string[]} headers
 * @param {string[][]} rows
 */
function renderTable(headers, rows) {
    // Calculate column widths
    const widths = headers.map((h, i) =>
        Math.max(h.length, ...rows.map(r => (r[i] || '').length))
    );

    const pad = (str, w) => str + ' '.repeat(w - str.length);
    const line = (left, mid, right, fill) =>
        left + widths.map(w => fill.repeat(w + 2)).join(mid) + right;

    const top    = line('\u250C', '\u252C', '\u2510', '\u2500');
    const sep    = line('\u251C', '\u253C', '\u2524', '\u2500');
    const bottom = line('\u2514', '\u2534', '\u2518', '\u2500');

    const formatRow = (cols) =>
        '\u2502' + cols.map((c, i) => ' ' + pad(c || '', widths[i]) + ' ').join('\u2502') + '\u2502';

    console.log(top);
    console.log(formatRow(headers));
    console.log(sep);
    rows.forEach(r => console.log(formatRow(r)));
    console.log(bottom);
}

// ─── Section 1: Vendor Libraries ────────────────────────────────────────────

function checkVendorLibraries() {
    console.log('\nVendor Libraries (bundled with component)');

    const libs = [
        {
            name: 'chart.js',
            current: npmInstalledVersion('chart.js'),
            latest: npmLatestVersion('chart.js'),
            notes: 'https://github.com/chartjs/Chart.js/releases'
        },
        {
            name: 'fancybox',
            current: npmInstalledVersion('@fancyapps/ui'),
            latest: fancyboxLatestVersion(),
            notes: 'https://github.com/fancyapps/ui/releases'
        },
        {
            name: 'intl-tel-input',
            current: npmInstalledVersion('intl-tel-input'),
            latest: npmLatestVersion('intl-tel-input'),
            notes: 'https://github.com/jackocnr/intl-tel-input/releases'
        },
        {
            name: 'sortablejs',
            current: npmInstalledVersion('sortablejs'),
            latest: npmLatestVersion('sortablejs'),
            notes: 'https://github.com/SortableJS/Sortable/releases'
        }
    ];

    let hasUpdates = false;
    const rows = libs.map(lib => {
        const outdated = lib.current !== '?' && lib.latest !== '?' && lib.current !== lib.latest;
        if (outdated) {
            hasUpdates = true;
        }
        return [
            lib.name,
            lib.current,
            lib.latest,
            outdated ? '\u2717 Update' : '\u2713 OK',
            lib.notes
        ];
    });

    renderTable(['Library', 'Current', 'Latest', 'Status', 'Release Notes'], rows);
    return hasUpdates;
}

// ─── Section 2: Dev Dependencies ────────────────────────────────────────────

function checkDevDependencies() {
    console.log('\nDev Dependencies (build tools)');

    const json = run('npm outdated --json 2>/dev/null');
    let outdated = {};
    if (json) {
        try {
            outdated = JSON.parse(json);
        } catch {
            // ignore
        }
    }

    const pkgJson = readJson(path.join(ROOT, 'package.json'));
    const allDevDeps = pkgJson ? Object.keys(pkgJson.devDependencies || {}).sort() : [];

    let hasUpdates = false;
    const rows = [];

    for (const pkg of allDevDeps) {
        const info = outdated[pkg];
        if (info) {
            hasUpdates = true;
            rows.push([
                pkg,
                info.current || '?',
                info.latest || '?',
                '\u2717 Update',
                ''
            ]);
        }
    }

    if (rows.length === 0) {
        rows.push(['(all packages)', '', '', '\u2713 OK', '']);
    }

    renderTable(['Package', 'Current', 'Latest', 'Status', ''], rows);
    return hasUpdates;
}

// ─── Section 3: PHP Dependencies ────────────────────────────────────────────

function checkPhpDependencies() {
    console.log('\nPHP Dependencies (composer)');

    const json = run('composer outdated --direct --format=json 2>/dev/null');
    let packages = [];
    if (json) {
        try {
            const data = JSON.parse(json);
            packages = data.installed || [];
        } catch {
            // ignore
        }
    }

    let hasUpdates = false;
    const rows = [];

    for (const pkg of packages) {
        // Only show semver-outdated (not abandoned-only notices)
        if (pkg.version !== pkg.latest) {
            hasUpdates = true;
            rows.push([
                pkg.name || '?',
                (pkg.version || '?').replace(/^v/, ''),
                (pkg.latest || '?').replace(/^v/, ''),
                '\u2717 Update',
                ''
            ]);
        }
    }

    if (rows.length === 0) {
        rows.push(['(all packages)', '', '', '\u2713 OK', '']);
    }

    renderTable(['Package', 'Current', 'Latest', 'Status', ''], rows);
    return hasUpdates;
}

// ─── Main ───────────────────────────────────────────────────────────────────

let anyUpdates = false;
anyUpdates = checkVendorLibraries() || anyUpdates;
anyUpdates = checkDevDependencies() || anyUpdates;
anyUpdates = checkPhpDependencies() || anyUpdates;

console.log('');
if (anyUpdates) {
    console.log('Updates available. Run `composer vendor:update` to update vendor libraries.');
    process.exit(1);
} else {
    console.log('All dependencies are up to date.');
    process.exit(0);
}
