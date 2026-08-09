<?php

/**
 * Post-release check: is the published update stream actually able to reach every site?
 *
 * Run AFTER `composer release`, once ARS has the new item. Everything else in
 * the release gate runs before publish and cannot see what the stream serves.
 *
 * Two properties, both of which fail silently if broken — valid XML, clean
 * release, and some population of sites simply never sees an update again:
 *
 *   1. A `com_proclaim` entry exists AT the version just released.
 *
 *      Sites that installed the old standalone component have no
 *      `pkg_proclaim` registered, so the package update never matches them.
 *      plg_system_cwmarsmigrate on the ARS server clones the newest
 *      `pkg_proclaim` block, re-types the clone as a component, and inserts it
 *      at the top of the feed. Because it is generated per request it cannot go
 *      stale — but it can stop appearing entirely if the plugin is disabled
 *      during maintenance, its stream id drifts, or an ARS upgrade breaks the
 *      onAfterRender hook. Nothing on the release side would notice: the feed
 *      stays valid and the package entry is still served, while component-only
 *      sites quietly go back to seeing "no updates".
 *
 *   2. It sorts BEFORE the `pkg_proclaim` entry of the same version.
 *
 *      Joomla keeps a single $latest per feed and replaces it only on a
 *      strictly greater version, so at equal versions the first block parsed
 *      wins. The plugin inserts first for exactly this reason; asserting it
 *      catches a regression in that insertion point.
 *
 * Also asserts php_minimum and targetplatform are present. Null ARS
 * environments mis-shipped 10.3.4 through 10.4.0, and the symptom was the same
 * shape: published fine, offered to nobody.
 *
 * Usage:
 *   php build/verify-update-stream.php            # version from versions.json `current`
 *   php build/verify-update-stream.php 10.5.6
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

declare(strict_types=1);

/**
 * When the com_proclaim entry stops being required.
 *
 * It exists to carry sites that never had a pkg_proclaim manifest — everything
 * installed before 10.3.0 (2026-05-01). Those sites convert themselves: taking
 * one update installs the package and they are served by either entry
 * afterwards. So the window only has to cover one update check per stranded
 * site, which is gated on an administrator logging in — days for an active
 * site, up to a year for one touched annually.
 *
 * Past this date the check stops requiring the entry rather than nagging, so
 * retiring the plugin is a deliberate act and not something that rots into a
 * warning everyone ignores. Revisit rather than extend blindly: if the two
 * entries are ever split across separate update sites, the straggler count
 * becomes measurable and this date can be replaced with evidence (#1666).
 *
 * See the wiki's ARS-Migration-Plugin page for how the entry is produced.
 */
const COMPONENT_ENTRY_SUNSET = '2027-08-09';

$root   = \dirname(__DIR__);
$red    = "\033[31m";
$green  = "\033[32m";
$yellow = "\033[33m";
$reset  = "\033[0m";

// Resolve the version under test.
$version = $argv[1] ?? null;

if ($version === null) {
    $versions = json_decode((string) file_get_contents($root . '/build/versions.json'), true);
    $version  = $versions['current']['version'] ?? null;
}

if ($version === null || preg_match('/^\d+\.\d+\.\d+/', $version) !== 1) {
    fwrite(STDERR, "Could not determine an x.y.z version to check.\n");

    exit(1);
}

// Read the stream URL from the manifest rather than hardcoding it, so a
// manifest pointing at the wrong ARS stream is caught here too.
$manifest = $root . '/build/pkg_proclaim.xml';

if (!is_file($manifest)) {
    fwrite(STDERR, "{$manifest} not found.\n");

    exit(1);
}

$doc = new DOMDocument();

if (!@$doc->load($manifest)) {
    fwrite(STDERR, "{$manifest} is not parseable.\n");

    exit(1);
}

$serverNode = (new DOMXPath($doc))->query('/extension/updateservers/server')->item(0);

if ($serverNode === null) {
    fwrite(STDERR, "No <updateservers><server> in the package manifest — the package cannot self-update.\n");

    exit(1);
}

$url = trim($serverNode->textContent);

// Override for testing this check against a fixture, and for pointing at a
// staging stream. Without it the assertions could only ever be exercised
// against production, which is no way to know they can fail.
if (getenv('PROCLAIM_UPDATE_STREAM') !== false && getenv('PROCLAIM_UPDATE_STREAM') !== '') {
    $url = (string) getenv('PROCLAIM_UPDATE_STREAM');
}

echo "========================================================================\n";
echo " UPDATE STREAM CHECK — pkg_proclaim {$version}\n";
echo "========================================================================\n";
echo "  stream: {$url}\n\n";

$context = stream_context_create(['http' => ['timeout' => 30], 'https' => ['timeout' => 30]]);
$body    = @file_get_contents($url, false, $context);

if ($body === false || $body === '') {
    echo "{$red}FAIL{$reset} could not fetch the update stream.\n";

    exit(1);
}

$stream = new DOMDocument();

if (!@$stream->loadXML($body)) {
    echo "{$red}FAIL{$reset} the update stream is not valid XML.\n";
    echo '       ' . substr(strip_tags($body), 0, 200) . "\n";

    exit(1);
}

$xpath   = new DOMXPath($stream);
$updates = $xpath->query('//update');

/**
 * Entries for the released version, in document order.
 *
 * Document order is the whole point: it is what decides the tie.
 *
 * @var array<int, array{element: string, position: int, php: string, platform: string}>
 */
$matching = [];
$position = 0;

foreach ($updates as $update) {
    $position++;
    $versionNode = $xpath->query('version', $update)->item(0);

    if ($versionNode === null || trim($versionNode->textContent) !== $version) {
        continue;
    }

    $elementNode  = $xpath->query('element', $update)->item(0);
    $phpNode      = $xpath->query('php_minimum', $update)->item(0);
    $platformNode = $xpath->query('targetplatform', $update)->item(0);

    $matching[] = [
        'element'  => $elementNode === null ? '(none)' : trim($elementNode->textContent),
        'position' => $position,
        'php'      => $phpNode === null ? '' : trim($phpNode->textContent),
        'platform' => $platformNode === null ? '' : $platformNode->getAttribute('version'),
    ];
}

echo "  entries for {$version}:\n";

foreach ($matching as $entry) {
    printf(
        "    #%-3d %-16s php_minimum=%-8s targetplatform=%s\n",
        $entry['position'],
        $entry['element'],
        $entry['php'] === '' ? '(none)' : $entry['php'],
        $entry['platform'] === '' ? '(none)' : $entry['platform']
    );
}

echo "\n";

$failures = 0;
$sunset   = strtotime(COMPONENT_ENTRY_SUNSET) < time();

$component = null;
$package   = null;

foreach ($matching as $entry) {
    if ($entry['element'] === 'com_proclaim' && $component === null) {
        $component = $entry;
    }

    if ($entry['element'] === 'pkg_proclaim' && $package === null) {
        $package = $entry;
    }
}

// --- the package entry is non-negotiable -----------------------------------
if ($package === null) {
    echo "{$red}FAIL{$reset} no pkg_proclaim entry for {$version} — no site can be offered this release.\n";
    $failures++;
} else {
    echo "{$green}PASS{$reset} pkg_proclaim entry present for {$version}\n";
}

// --- the component entry, until the sunset ---------------------------------
if ($sunset) {
    echo "{$yellow}SKIP{$reset} com_proclaim entry no longer required (sunset "
        . COMPONENT_ENTRY_SUNSET . " has passed).\n";
    echo "       If the entry is still published, decide deliberately whether to retire it (#1666).\n";
} elseif ($component === null) {
    echo "{$red}FAIL{$reset} no com_proclaim entry at {$version}.\n";
    echo "       Component-only sites are reached ONLY by this entry, which\n";
    echo "       plg_system_cwmarsmigrate injects into the feed on the ARS server.\n";
    echo "       Its absence means that plugin is disabled, misconfigured (stream id),\n";
    echo "       or no longer firing. Those sites now see 'no updates', silently.\n";
    $failures++;
} else {
    echo "{$green}PASS{$reset} com_proclaim entry present at {$version} (not stale)\n";

    // --- the tie-break ------------------------------------------------------
    if ($package !== null && $component['position'] > $package['position']) {
        echo "{$red}FAIL{$reset} com_proclaim is parsed AFTER pkg_proclaim "
            . "(positions {$component['position']} vs {$package['position']}).\n";
        echo "       Equal versions never replace one another, so the first entry parsed wins\n";
        echo "       and the other is discarded. In this order the component entry is dropped\n";
        echo "       and component-only sites stop being offered updates.\n";
        echo "       plg_system_cwmarsmigrate must insert its clone at the TOP of the feed.\n";
        $failures++;
    } elseif ($package !== null) {
        echo "{$green}PASS{$reset} com_proclaim sorts before pkg_proclaim "
            . "(positions {$component['position']} < {$package['position']})\n";
    }
}

// --- environments ----------------------------------------------------------
foreach ($matching as $entry) {
    if ($entry['php'] === '' || $entry['platform'] === '') {
        echo "{$red}FAIL{$reset} {$entry['element']} is missing "
            . ($entry['php'] === '' ? 'php_minimum' : 'targetplatform') . ".\n";
        echo "       Null ARS environments mis-shipped 10.3.4-10.4.0: published, offered to nobody.\n";
        $failures++;
    }
}

if ($failures === 0) {
    echo "\n{$green}Update stream OK for {$version}.{$reset}\n";

    exit(0);
}

echo "\n{$red}{$failures} update-stream assertion(s) FAILED for {$version}.{$reset}\n";

exit(1);
