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
 * Only the comment part of a line is searched, so an inline comment after code
 * is covered and a `#` inside code or a string literal is not. Tests are not
 * scanned: a regression test's docblock naming the issue it guards is the one
 * place the number is the subject rather than a pointer away from it.
 *
 * Four digits are required. Every citation the sweep found had four, Proclaim's
 * numbering passed 1000 years ago, and three would collide with the CSS
 * shorthand colours (`#000`, `#666`, `#999`) that appear in comments.
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

/**
 * Return only the comment text on a line.
 *
 * `$inBlock` carries `/* … *\/` state across lines, so continuation lines of a
 * docblock are read as comment without relying on them starting with `*`.
 * Quotes are tracked so a `//` inside a string is code, and a `//` preceded by
 * `:` is left alone so a URL in code does not read as a comment.
 *
 * PSR-12 forbids `#` line comments, and PHP CS Fixer enforces PSR-12 here, so
 * that form is not handled — `#[` attributes would be the ambiguous case.
 *
 * @param   string  $line       The line to read.
 * @param   bool    $inBlock    Whether a block comment is already open.
 * @param   bool    $lineForms  Whether `//` opens a comment (false for CSS).
 *
 * @return  string  The comment text, empty when the line carries none.
 */
function commentText(string $line, bool &$inBlock, bool $lineForms): string
{
    $out    = '';
    $len    = \strlen($line);
    $quote  = null;
    $i      = 0;

    while ($i < $len) {
        $c    = $line[$i];
        $next = $i + 1 < $len ? $line[$i + 1] : '';

        if ($inBlock) {
            if ($c === '*' && $next === '/') {
                $inBlock = false;
                $i += 2;

                continue;
            }

            $out .= $c;
            $i++;

            continue;
        }

        if ($quote !== null) {
            if ($c === '\\') {
                $i += 2;

                continue;
            }

            if ($c === $quote) {
                $quote = null;
            }

            $i++;

            continue;
        }

        if ($c === '"' || $c === "'") {
            $quote = $c;
            $i++;

            continue;
        }

        if ($c === '/' && $next === '*') {
            $inBlock = true;
            $i += 2;

            continue;
        }

        if ($lineForms && $c === '/' && $next === '/' && ($i === 0 || $line[$i - 1] !== ':')) {
            $out .= substr($line, $i + 2);

            break;
        }

        $i++;
    }

    return $out;
}

/**
 * Find issue references in a comment.
 *
 * `owner/repo#123` points at somebody else's tracker, which blame cannot lead
 * anyone to, so it earns its place and is left alone. A bare `#1234`, or one
 * introduced by a word such as `PR#1234`, is a pointer into this project's
 * history and is reported.
 *
 * @param   string  $comment  Comment text.
 *
 * @return  bool  True when the comment cites an issue.
 */
function citesIssue(string $comment): bool
{
    if (!preg_match_all('~#\d{4}(?![0-9a-fA-F])~', $comment, $matches, PREG_OFFSET_CAPTURE)) {
        return false;
    }

    foreach ($matches[0] as [$_, $offset]) {
        preg_match('~[\w./-]*$~', substr($comment, 0, $offset), $preceding);

        // A slash in the token before `#` makes it another project's tracker.
        if (!str_contains($preceding[0], '/')) {
            return true;
        }
    }

    return false;
}

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

        $lineForms = !str_ends_with($path, '.css');
        $inBlock   = false;

        foreach ($lines as $i => $line) {
            $comment = commentText($line, $inBlock, $lineForms);

            if ($comment !== '' && citesIssue($comment)) {
                $offenders[] = [$path, $i + 1, trim($line)];
            }
        }
    }
}

if ($offenders === []) {
    echo "No issue references in comments.\n";

    exit(0);
}

echo 'Issue references found in comments (' . \count($offenders) . "):\n\n";

foreach ($offenders as [$path, $line, $text]) {
    echo "  {$path}:{$line}\n";
    echo '      ' . (\strlen($text) > 96 ? substr($text, 0, 96) . '…' : $text) . "\n";
}

echo "\nMove the reference to the commit body or the pull request, and leave the\n";
echo "comment saying what the code does. `owner/repo#123` for another project's\n";
echo "tracker is allowed; so is a hex colour.\n";

exit(1);
