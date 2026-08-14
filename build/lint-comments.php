<?php

/**
 * Reject issue references inside code comments.
 *
 * A comment carries what a maintainer needs at the line. An issue number is a
 * pointer to the investigation instead, and git log / git blame already connect
 * any line to its commit, its pull request and its issue — so the citation adds
 * nothing to the file and goes stale the moment the numbering context does.
 *
 * This exists because the debt grows back. A sweep cleared it across three
 * repositories once, and within a week new citations had been written; the same
 * shape returns whenever someone explains a fix at the line instead of in the
 * commit body. A check makes that visible while it is still cheap to move.
 *
 * Two exclusions are deliberate rather than incidental:
 *
 *   - A six-digit hex colour contains a four-digit run, so `#996901` matches a
 *     naive `#\d{3,4}`. Stripping those edits colour values in a stylesheet.
 *   - `owner/repo#123` points at somebody else's tracker, which blame cannot
 *     lead anyone to, so it earns its place.
 *
 * Vendored trees, the scripturelinks submodule and minified output are other
 * projects' code and not this standard's to enforce.
 *
 * Usage: php build/lint-comments.php
 * Exit:  0 clean, 1 offenders found.
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

$roots = ['admin', 'api', 'site', 'modules', 'plugins', 'build/media_source'];

$skip = [
    '/vendor/',
    '/node_modules/',
    '/libraries/',
    'scripturelinks',
    '.min.',
];

/** A line that is a comment in PHP, JavaScript or CSS. */
$isComment = '~^\s*(//|\*|/\*)~';

/**
 * An issue reference: # then three or four digits, not continuing into a hex
 * colour, and not preceded by an owner/repo pointing at another tracker.
 */
$citation = '~(?<![\w./-])#\d{3,4}(?![0-9a-fA-F])~';

$offenders = [];

/**
 * Real paths already read. `admin/proclaim.script.php` is a symlink to the
 * manifest script at the repository root, so without this a file reachable
 * twice is reported twice, and a symlinked directory would recurse forever.
 */
$seen = [];

foreach ($roots as $root) {
    if (!is_dir($root)) {
        continue;
    }

    $walker = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($walker as $file) {
        $path = str_replace('\\', '/', $file->getPathname());

        if (!preg_match('~\.(php|js|css)$~', $path)) {
            continue;
        }

        foreach ($skip as $fragment) {
            if (str_contains($path, $fragment)) {
                continue 2;
            }
        }

        $real = realpath($path);

        if ($real === false || isset($seen[$real])) {
            continue;
        }

        $seen[$real] = true;

        $lines = file($path, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            continue;
        }

        foreach ($lines as $i => $line) {
            if (preg_match($isComment, $line) && preg_match($citation, $line)) {
                $offenders[] = [$path, $i + 1, trim($line)];
            }
        }
    }
}

if ($offenders === []) {
    echo "No issue references in comments.\n";

    exit(0);
}

echo "Issue references found in comments (" . \count($offenders) . "):\n\n";

foreach ($offenders as [$path, $line, $text]) {
    echo "  {$path}:{$line}\n";
    echo '      ' . (\strlen($text) > 96 ? substr($text, 0, 96) . '…' : $text) . "\n";
}

echo "\nMove the reference to the commit body or the pull request, and leave the\n";
echo "comment saying what the code does. `owner/repo#123` for another project's\n";
echo "tracker is allowed; so is a hex colour.\n";

exit(1);
