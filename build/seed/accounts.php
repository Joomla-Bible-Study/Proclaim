<?php

/**
 * Seed layer: test accounts, one per way a visitor can reach Proclaim.
 *
 * A registered member, an editor, a manager and an API user, declared in `accounts.json`. They
 * exist so the access rules can be checked from the inside: which studies each role sees, and
 * what the API answers to a token.
 *
 * ⚠️ Every account has the password in `accounts.json`, which is published. The layer therefore
 * refuses any site that is not disposable (see disposable() in lib.php) and says why. Never
 * relax that: a dev install with real data behind it must not gain a known login.
 *
 * Run two ways:
 *
 *   php build/seed/accounts.php apply|remove|check|token
 *       Standalone: every `role = test` install in build.properties.
 *
 *   cwm-seed ... (layer `accounts`)
 *       Under cwm-seed, which names one site through CWM_SEED_* variables.
 *
 * `token` prints the API user's bearer token, for tests that call the API. It is derived from the
 * seed stored for that user and the site secret, the way Joomla's token plugin checks it, so it
 * changes whenever `apply` runs.
 *
 * Idempotent: `apply` removes what it wrote before writing; `remove` deletes by the marker.
 *
 * @package    Proclaim.Build
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

declare(strict_types=1);

use CWM\BuildTools\Dev\TestSite;

$root = \dirname(__DIR__, 2);

require $root . '/libraries/vendor/autoload.php';
require __DIR__ . '/lib.php';

$action = $argv[1] ?? 'apply';

if (!\in_array($action, ['apply', 'remove', 'check', 'token'], true)) {
    fwrite(STDERR, "Usage: php build/seed/accounts.php apply|remove|check|token\n");

    exit(2);
}

$declared = json_decode((string) file_get_contents(__DIR__ . '/accounts.json'), true, 512, JSON_THROW_ON_ERROR);
$marker   = getenv('CWM_SEED_MARKER') ?: markerFromConfig($root);

if (($reason = disposable()) !== null && $action !== 'remove') {
    fwrite(STDERR, "Refusing to {$action} test accounts: {$reason}.\n");

    exit(1);
}

$failures = 0;

foreach (targets($root) as $label => $makeSite) {
    if ($action !== 'token') {
        echo "=== {$action} accounts on {$label} ===\n";
    }

    try {
        $site = $makeSite();

        $failures += match ($action) {
            'apply'  => applyAccounts($site, $declared, $marker),
            'remove' => removeAccounts($site, $marker),
            'token'  => printToken($site, $declared, $marker, $label),
            default  => checkAccounts($site, $declared, $marker),
        };
    } catch (\RuntimeException | \PDOException $e) {
        fwrite(STDERR, '  ' . $e->getMessage() . "\n");
        $failures++;
    }

    if ($action !== 'token') {
        echo "\n";
    }
}

if ($failures > 0) {
    fwrite(STDERR, "Account seeding ({$action}) failed: {$failures} problem(s).\n");

    exit(1);
}

if ($action !== 'token') {
    echo 'Accounts ' . ($action === 'apply' ? 'applied' : ($action === 'remove' ? 'removed' : 'checked')) . ".\n";
}

/**
 * The id of a user group, by title.
 *
 * @throws \RuntimeException  when the group does not exist
 */
function groupId(TestSite $site, string $title): int
{
    $id = scalar($site->db(), 'SELECT id FROM ' . $site->table('#__usergroups') . ' WHERE title = ' . $site->db()->quote($title));

    if ($id === null) {
        throw new \RuntimeException("no \"{$title}\" user group on this site.");
    }

    return (int) $id;
}

/**
 * Create the declared accounts.
 *
 * @param  array<string, mixed>  $declared
 *
 * @return int  Problems found (none: a failure throws)
 */
function applyAccounts(TestSite $site, array $declared, string $marker): int
{
    $db = $site->db();

    removeAccounts($site, $marker, true);

    $insert = $db->prepare(
        'INSERT INTO ' . $site->table('#__users')
        . ' (name, username, email, password, block, sendEmail, registerDate, params, requireReset, activation) '
        . "VALUES (?, ?, ?, ?, 0, 0, ?, '{}', 0, '')"
    );
    $map     = $db->prepare('INSERT INTO ' . $site->table('#__user_usergroup_map') . ' (user_id, group_id) VALUES (?, ?)');
    $profile = $db->prepare('INSERT INTO ' . $site->table('#__user_profiles') . ' (user_id, profile_key, profile_value, ordering) VALUES (?, ?, ?, 0)');
    $hash    = password_hash((string) $declared['password'], PASSWORD_BCRYPT);

    foreach ($declared['accounts'] as $account) {
        $username = $marker . $account['key'];

        $insert->execute([$account['name'], $username, $username . '@example.invalid', $hash, gmdate('Y-m-d H:i:s')]);

        $id = (int) $db->lastInsertId();
        $map->execute([$id, groupId($site, $account['group'])]);

        if ($account['token'] ?? false) {
            $profile->execute([$id, 'joomlatoken.token', base64_encode(random_bytes(32))]);
            $profile->execute([$id, 'joomlatoken.enabled', '1']);
        }

        printf("  + %-20s id %-5d %s%s\n", $username, $id, $account['group'], ($account['token'] ?? false) ? ', API token' : '');
    }

    return 0;
}

/**
 * Delete the accounts this layer wrote, and what hangs off them.
 */
function removeAccounts(TestSite $site, string $marker, bool $quiet = false): int
{
    $db = $site->db();

    $find = $db->prepare('SELECT id FROM ' . $site->table('#__users') . ' WHERE username LIKE ?');
    $find->execute([$marker . '%']);
    $ids = array_map('intval', $find->fetchAll(PDO::FETCH_COLUMN));

    if ($ids === []) {
        return 0;
    }

    $in = implode(',', $ids);

    foreach (['#__user_usergroup_map', '#__user_profiles'] as $table) {
        $db->exec('DELETE FROM ' . $site->table($table) . " WHERE user_id IN ({$in})");
    }

    $db->exec('DELETE FROM ' . $site->table('#__users') . " WHERE id IN ({$in})");

    if (!$quiet) {
        echo '  - ' . \count($ids) . " account(s)\n";
    }

    return 0;
}

/**
 * Check each account exists, is unblocked, is in its group, and the API user has an enabled token.
 *
 * @param  array<string, mixed>  $declared
 *
 * @return int  Problems found
 */
function checkAccounts(TestSite $site, array $declared, string $marker): int
{
    $db       = $site->db();
    $problems = 0;

    $find = $db->prepare(
        'SELECT u.id, u.block, g.title AS grp FROM ' . $site->table('#__users') . ' AS u '
        . 'LEFT JOIN ' . $site->table('#__user_usergroup_map') . ' AS m ON m.user_id = u.id '
        . 'LEFT JOIN ' . $site->table('#__usergroups') . ' AS g ON g.id = m.group_id WHERE u.username = ?'
    );
    $token = $db->prepare('SELECT profile_value FROM ' . $site->table('#__user_profiles') . ' WHERE user_id = ? AND profile_key = ?');

    foreach ($declared['accounts'] as $account) {
        $username = $marker . $account['key'];
        $find->execute([$username]);
        $row = $find->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            fwrite(STDERR, "  ! {$username}: not seeded\n");
            $problems++;

            continue;
        }

        $wrong = [];

        if ((int) $row['block'] !== 0) {
            $wrong[] = 'blocked';
        }

        if ($row['grp'] !== $account['group']) {
            $wrong[] = "group {$row['grp']} (expected {$account['group']})";
        }

        if ($account['token'] ?? false) {
            $token->execute([(int) $row['id'], 'joomlatoken.enabled']);

            if ($token->fetchColumn() !== '1') {
                $wrong[] = 'API token not enabled';
            }

            $token->execute([(int) $row['id'], 'joomlatoken.token']);

            if (!\is_string($token->fetchColumn())) {
                $wrong[] = 'no API token seed';
            }
        }

        if ($wrong !== []) {
            fwrite(STDERR, "  ! {$username}: " . implode(', ', $wrong) . "\n");
            $problems++;
        } else {
            echo "  ok {$username}\n";
        }
    }

    return $problems;
}

/**
 * Print the API user's bearer token on its own line.
 *
 * @param  array<string, mixed>  $declared
 *
 * @throws \RuntimeException  when the API user has no token
 */
function printToken(TestSite $site, array $declared, string $marker, string $label): int
{
    $path = (string) getenv('CWM_SEED_SITE_PATH');

    if ($path === '' && preg_match('/\((.*)\)$/', $label, $m) === 1) {
        $path = $m[1];
    }

    foreach ($declared['accounts'] as $account) {
        if (!($account['token'] ?? false)) {
            continue;
        }

        $token = apiToken($site, $marker . $account['key'], $path);

        if ($token === null) {
            throw new \RuntimeException('the API account is not seeded — run the accounts layer first.');
        }

        echo $token . "\n";

        return 0;
    }

    throw new \RuntimeException('accounts.json declares no API account.');
}
