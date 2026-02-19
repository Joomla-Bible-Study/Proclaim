/* eslint-disable no-console */
"use strict";

const fs = require('fs');
const path = require('path');

const srcBase = path.join(__dirname, 'media_source');
const mediaBase = path.join(__dirname, '../media');

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

// Copy images
console.log('Copying images...');
copyDir(path.join(srcBase, 'images'), path.join(mediaBase, 'images'));

// Copy each vendor library
console.log('Copying vendor libraries...');
const vendorSrc = path.join(srcBase, 'vendor');
const vendorEntries = fs.readdirSync(vendorSrc, { withFileTypes: true });
vendorEntries.forEach(entry => {
    if (entry.isDirectory()) {
        const dest = path.join(mediaBase, 'vendor', entry.name);
        copyDir(path.join(vendorSrc, entry.name), dest);
        console.log(`  Copied vendor/${entry.name}`);
    }
});

// Also copy fancybox to media/fancybox/ for backward compatibility with WAM registration
// (WAM uses full literal path "media/com_proclaim/fancybox/...")
copyDir(
    path.join(vendorSrc, 'fancybox'),
    path.join(mediaBase, 'fancybox')
);
console.log('  Copied vendor/fancybox -> fancybox/ (WAM compat)');

// Copy Sortable.js to media/vendor/sortable/ (consistent with chart.js, intl-tel-input)
const sortableSrc = path.join(__dirname, '../node_modules/sortablejs/Sortable.min.js');
const sortableDest = path.join(mediaBase, 'vendor/sortable/Sortable.min.js');
fs.mkdirSync(path.dirname(sortableDest), { recursive: true });
fs.copyFileSync(sortableSrc, sortableDest);
console.log('  Copied sortablejs -> vendor/sortable/Sortable.min.js');

console.log('Asset copy complete.');
