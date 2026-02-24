 
"use strict";

const fs = require('fs');
const path = require('path');
const csso = require('csso');

const srcBase = path.join(__dirname, 'media_source');
const mediaBase = path.join(__dirname, '../media');
const nodeModules = path.join(__dirname, '../node_modules');

/**
 * Recursively copy a directory from src to dest
 * @param {string} src - Source directory
 * @param {string} dest - Destination directory
 */
function copyDir(src, dest) {
    if (!fs.existsSync(dest)) {
        fs.mkdirSync(dest, { recursive: true });
    }

    const entries = fs.readdirSync(src, { withFileTypes: true });
    entries.forEach(entry => {
        const srcPath = path.join(src, entry.name);
        const destPath = path.join(dest, entry.name);
        if (entry.isDirectory()) {
            copyDir(srcPath, destPath);
        } else {
            fs.copyFileSync(srcPath, destPath);
        }
    });
}

/**
 * Copy a single file, creating parent directories as needed.
 * @param {string} src - Source file path
 * @param {string} dest - Destination file path
 */
function copyFile(src, dest) {
    fs.mkdirSync(path.dirname(dest), { recursive: true });
    fs.copyFileSync(src, dest);
}

/**
 * Recursively remove a directory and all its contents.
 * @param {string} dir - Directory to remove
 */
function cleanDir(dir) {
    if (fs.existsSync(dir)) {
        fs.rmSync(dir, { recursive: true, force: true });
    }
}

/**
 * Copy a CSS file and generate a minified version alongside it.
 * @param {string} src - Source CSS file path
 * @param {string} dest - Destination CSS file path (unminified)
 */
function copyCssWithMin(src, dest) {
    copyFile(src, dest);
    const css = fs.readFileSync(src, 'utf8');
    const minDest = dest.replace('.css', '.min.css');
    fs.writeFileSync(minDest, csso.minify(css).css);
}

// Copy images
console.log('Copying images...');
copyDir(path.join(srcBase, 'images'), path.join(mediaBase, 'images'));

// Copy any remaining manually-managed vendor files from media_source
console.log('Copying vendor libraries...');
const vendorSrc = path.join(srcBase, 'vendor');
if (fs.existsSync(vendorSrc)) {
    const vendorEntries = fs.readdirSync(vendorSrc, { withFileTypes: true });
    vendorEntries.forEach(entry => {
        if (entry.isDirectory()) {
            const dest = path.join(mediaBase, 'vendor', entry.name);
            copyDir(path.join(vendorSrc, entry.name), dest);
            console.log(`  Copied vendor/${entry.name} (from media_source)`);
        }
    });
}

// ─── npm vendor packages (authoritative source: node_modules) ───────────────

// chart.js — UMD bundle
copyFile(
    path.join(nodeModules, 'chart.js/dist/chart.umd.min.js'),
    path.join(mediaBase, 'vendor/chart.js/chart.umd.min.js')
);
console.log('  Copied chart.js (from node_modules)');

// fancybox (@fancyapps/ui) — UMD JS, CSS + minified CSS, l10n
// Clean first to remove stale files from previous versions
const fancySrc = path.join(nodeModules, '@fancyapps/ui/dist/fancybox');
const fancyDest = path.join(mediaBase, 'vendor/fancybox');
cleanDir(fancyDest);

copyFile(
    path.join(fancySrc, 'fancybox.umd.js'),
    path.join(fancyDest, 'fancybox.umd.js')
);
copyCssWithMin(
    path.join(fancySrc, 'fancybox.css'),
    path.join(fancyDest, 'fancybox.css')
);

// Copy l10n UMD files
const l10nSrc = path.join(fancySrc, 'l10n');
const l10nDest = path.join(fancyDest, 'l10n');
if (fs.existsSync(l10nSrc)) {
    fs.mkdirSync(l10nDest, { recursive: true });
    fs.readdirSync(l10nSrc)
        .filter(f => f.endsWith('.umd.js'))
        .forEach(f => {
            fs.copyFileSync(path.join(l10nSrc, f), path.join(l10nDest, f));
        });
}
console.log('  Copied fancybox (from node_modules)');

// intl-tel-input — CSS, JS, and flag images
copyFile(
    path.join(nodeModules, 'intl-tel-input/build/css/intlTelInput.min.css'),
    path.join(mediaBase, 'vendor/intl-tel-input/css/intlTelInput.min.css')
);
copyFile(
    path.join(nodeModules, 'intl-tel-input/build/js/intlTelInputWithUtils.min.js'),
    path.join(mediaBase, 'vendor/intl-tel-input/js/intlTelInputWithUtils.min.js')
);
copyDir(
    path.join(nodeModules, 'intl-tel-input/build/img'),
    path.join(mediaBase, 'vendor/intl-tel-input/img')
);
console.log('  Copied intl-tel-input (from node_modules)');

// sortablejs — UMD bundle
copyFile(
    path.join(nodeModules, 'sortablejs/Sortable.min.js'),
    path.join(mediaBase, 'vendor/sortable/Sortable.min.js')
);
console.log('  Copied sortablejs (from node_modules)');

console.log('Asset copy complete.');
