/* eslint-disable no-console */
"use strict";

/**
 * Vendor Dependency Updater
 *
 * Updates all bundled vendor libraries to their latest versions:
 *   - chart.js, @fancyapps/ui (fancybox), intl-tel-input, sortablejs — via `npm update`
 *   - Rebuilds media assets so updated vendors are copied to media/
 *
 * Does NOT auto-update devDependencies or Composer packages (those can have
 * breaking changes). Use `composer vendor:check` to see what's outdated.
 *
 * Usage:  node build/update-vendors.js
 *         composer vendor:update
 *
 * @since 10.1.0
 */

const { execSync } = require('child_process');
const path = require('path');

const ROOT = path.resolve(__dirname, '..');

// ─── Helpers ────────────────────────────────────────────────────────────────

function run(cmd, label) {
    console.log(`  ${label || cmd}`);
    try {
        execSync(cmd, { cwd: ROOT, stdio: 'inherit', timeout: 120000 });
        return true;
    } catch {
        console.error(`  FAILED: ${cmd}`);
        return false;
    }
}

function readJson(filePath) {
    try {
        return JSON.parse(require('fs').readFileSync(filePath, 'utf8'));
    } catch {
        return null;
    }
}

function npmInstalledVersion(pkg) {
    const pkgJson = readJson(path.join(ROOT, 'node_modules', pkg, 'package.json'));
    return pkgJson ? pkgJson.version : '?';
}

// ─── Vendor packages to update ──────────────────────────────────────────────

const VENDORS = [
    { npm: 'chart.js',       label: 'chart.js' },
    { npm: '@fancyapps/ui',  label: 'fancybox' },
    { npm: 'intl-tel-input', label: 'intl-tel-input' },
    { npm: 'sortablejs',     label: 'sortablejs' }
];

// ─── Main ───────────────────────────────────────────────────────────────────

console.log('Proclaim Vendor Updater');
console.log('======================');

// Record versions before update
const before = {};
VENDORS.forEach(v => { before[v.npm] = npmInstalledVersion(v.npm); });

// Update all vendor packages via npm
console.log('\n1. Updating npm vendor packages...');
const npmPkgs = VENDORS.map(v => v.npm).join(' ');
run(`npm update ${npmPkgs}`, `npm update ${npmPkgs}`);

// Record versions after update
const after = {};
VENDORS.forEach(v => { after[v.npm] = npmInstalledVersion(v.npm); });

// Report changes
const changes = [];
VENDORS.forEach(v => {
    if (before[v.npm] !== after[v.npm]) {
        changes.push(`  ${v.label}: ${before[v.npm]} -> ${after[v.npm]}`);
    }
});

if (changes.length) {
    console.log('  Updated:');
    changes.forEach(c => console.log(c));
} else {
    console.log('  All vendor packages already up to date.');
}

// Rebuild assets
console.log('\n2. Rebuilding media assets...');
run('npm run build', 'npm run build');

// Summary
console.log('\n' + '='.repeat(50));
if (changes.length) {
    console.log('Summary of updates:');
    changes.forEach(c => console.log(c));
    console.log('\nRemember to test and commit the changes.');
} else {
    console.log('All vendor libraries are already up to date.');
}
