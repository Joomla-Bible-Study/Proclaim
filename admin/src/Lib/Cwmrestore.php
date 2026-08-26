<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Lib;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmmigrationHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmtemplatemigrationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Component\Installer\Administrator\Model\DatabaseModel;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

/**
 * Restore class
 *
 * @package  Proclaim.Admin
 * @since    7.0.4
 */
class Cwmrestore
{
    /**
     * Core Joomla tables that Cwmbackup's own export methods legitimately write
     * to (component config, ACL rules, scheduled tasks) alongside Proclaim's own
     * `#__bsms_*` tables (which are discovered dynamically via CwmdbHelper).
     *
     * @var string[]
     *
     * @since 10.5.6
     */
    private const array ALLOWED_RESTORE_TABLES = ['#__extensions', '#__assets', '#__scheduler_tasks'];

    /**
     * Verdicts from classifyStatement().
     *
     * ALLOWED   — run it.
     * PRESERVED — targets a table the restore must leave alone; skip quietly.
     * SESSION   — dump preamble that names no table; skip quietly.
     * REJECTED  — names a table this component has no business writing to.
     *
     * @var string
     *
     * @since __DEPLOY_VERSION__
     */
    public const string STATEMENT_ALLOWED = 'allowed';

    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    public const string STATEMENT_PRESERVED = 'preserved';

    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    public const string STATEMENT_SESSION = 'session';

    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    public const string STATEMENT_REJECTED = 'rejected';

    /**
     * Cached list of Proclaim's own table names, populated on first use.
     *
     * @var string[]|null
     *
     * @since 10.5.6
     */
    private static ?array $bsmsTables = null;

    /**
     * Reject any SQL statement from a restore file that isn't one of the shapes
     * (and target tables) Cwmbackup itself is known to generate.
     *
     * restoreDB()/installdb() execute every statement in an uploaded/selected
     * backup file with essentially no validation -- only two substr_count()
     * checks for literal marker strings, trivially satisfied by embedding them
     * anywhere (even a comment) while the rest of the file contains arbitrary
     * SQL. This is Super-User-gated, so not a remote-anonymous vector, but a
     * compromised admin account or a supply-chain-tainted backup file shared
     * between sites has far more reach than this feature needs. Restricting to
     * the statement shapes and tables Cwmbackup actually produces is defense in
     * depth, not a hard sandbox -- for the full threat discussion.
     *
     * @param   string  $statement  A single SQL statement (already trimmed, non-empty, non-comment)
     *
     * @return  bool
     *
     * @since 10.5.6
     */
    private static function isSafeRestoreStatement(string $statement): bool
    {
        $verdict = self::classifyStatement($statement);

        if ($verdict === self::STATEMENT_ALLOWED) {
            return true;
        }

        if ($verdict === self::STATEMENT_SESSION) {
            Log::add('Rejected restore statement (unrecognized shape): ' . substr($statement, 0, 120), Log::WARNING, 'com_proclaim');
        } else {
            Log::add('Rejected restore statement targeting non-Proclaim table: ' . substr($statement, 0, 120), Log::WARNING, 'com_proclaim');
        }

        return false;
    }

    /**
     * Decide what a restore should do with one statement.
     *
     * isSafeRestoreStatement() answers the same question as a bool, which is
     * all the file-based restore paths need. A dump written by anything other
     * than Cwmbackup also carries session preamble — SET NAMES, the
     * character_set_client save and restore either side of every CREATE TABLE,
     * ALTER TABLE ... DISABLE KEYS — and none of it targets a table. Counting
     * those as failures buries the ones that matter: a 15 MB mysqldump of this
     * component splits into 214 statements, and 137 of them are preamble.
     *
     * @param   string  $statement  A single SQL statement, trimmed and non-empty
     *
     * @return  string  One of the STATEMENT_* constants
     *
     * @since __DEPLOY_VERSION__
     */
    public static function classifyStatement(string $statement): string
    {
        if (self::targetsPreservedTable($statement)) {
            return self::STATEMENT_PRESERVED;
        }

        $pattern = '/^(?:CREATE\s+TABLE|DROP\s+TABLE(?:\s+IF\s+EXISTS)?|ALTER\s+TABLE|INSERT\s+INTO|UPDATE|DELETE\s+FROM)\s+`?(?<table>#__[a-z0-9_]+)`?/i';

        if (!preg_match($pattern, $statement, $matches)) {
            // No table in the leading clause. Either dump preamble, which is
            // skipped without comment, or a shape this restore has no business
            // running either way.
            return self::STATEMENT_SESSION;
        }

        $table = strtolower($matches['table']);

        // ⚠️ Ownership is decided from the name, not from the live table list.
        //
        // getOwnObjects() reads SHOW TABLES, and the batch importer drops every
        // Proclaim table before it runs a single statement — so by the time the
        // first statement was classified there was nothing left to match and
        // the whole restore was rejected as foreign. Each batch is its own
        // request, so no amount of caching survives it either. The name is the
        // one thing that is true before and after the drop, and it is also what
        // lets a backup from a newer version create a table this site does not
        // have yet.
        //
        // getScriptureTables() is a fixed list, so excluding the shared stack
        // costs nothing here. targetsPreservedTable() has already caught those
        // above; this is the second lock on the same door.
        if (\in_array($table, CwmdbHelper::getScriptureTables(), true)) {
            return self::STATEMENT_PRESERVED;
        }

        if (str_starts_with($table, '#__bsms_') || \in_array($table, self::ALLOWED_RESTORE_TABLES, true)) {
            return self::STATEMENT_ALLOWED;
        }

        return self::STATEMENT_REJECTED;
    }

    /**
     * DatabaseDriver::splitSql(), with the same answer for less work.
     *
     * ⚠️ This is a port, not an improvement. The state machine below is the
     * core one line for line, and it has to stay that way: the two are checked
     * against each other on a real dump, and any difference in how a quote or a
     * comment is read would split a statement in the wrong place and corrupt an
     * import in a way nothing downstream could detect.
     *
     * What changes is the cost per character. The core reads `substr($sql, $i, 1)`,
     * `substr($sql, $i, 2)`, `substr($sql, $i, 3)` and a fourth for the
     * terminator on *every* character, so a 15 MB dump allocates something like
     * 60 million short-lived strings and takes 63 seconds — past the browser's
     * own timeout, which is how this surfaced. Indexing with `$sql[$i]` and
     * building the two- and three-character lookaheads only when the current
     * character could begin a quote or a comment leaves the behaviour alone and
     * takes seconds instead.
     *
     * @param   string  $sql  One or more SQL statements
     *
     * @return  string[]  The statements, exactly as splitSql() would return them
     *
     * @since __DEPLOY_VERSION__
     */
    public static function splitSqlFast(string $sql): array
    {
        $start     = 0;
        $open      = false;
        $comment   = false;
        $endString = '';
        $end       = \strlen($sql);
        $queries   = [];
        $query     = '';

        for ($i = 0; $i < $end; $i++) {
            $current      = $sql[$i];
            $lenEndString = \strlen($endString);

            // The terminator being scanned for is one character for a quote and
            // two for a block comment, so the common cases need no allocation.
            if ($lenEndString === 0) {
                $testEnd = '';
            } elseif ($lenEndString === 1) {
                $testEnd = $current;
            } else {
                $testEnd = substr($sql, $i, $lenEndString);
            }

            // Only these can open a quote or a comment, or close one already
            // open. Every other character — which is nearly all of them — skips
            // the lookaheads entirely.
            $isQuote = $current === '"' || $current === "'";

            if (
                $isQuote
                || $current === '-' || $current === '/' || $current === '#'
                || ($comment && $testEnd === $endString)
            ) {
                $current2 = ($current === '-' || $current === '/') ? substr($sql, $i, 2) : '';
                $current3 = ($current2 === '/*' || $current === '#') ? substr($sql, $i, 3) : '';

                if (
                    $isQuote || $current2 === '--'
                    || ($current2 === '/*' && $current3 !== '/*!' && $current3 !== '/*+')
                    || ($current === '#' && $current3 !== '#__')
                    || ($comment && $testEnd === $endString)
                ) {
                    // Check if quoted with previous backslash. Left on substr:
                    // it runs only at a quote or comment character, and its
                    // negative-offset behaviour is part of what is being copied.
                    $n = 2;

                    while (substr($sql, $i - $n + 1, 1) === '\\' && $n < $i) {
                        $n++;
                    }

                    // Not quoted
                    if ($n % 2 === 0) {
                        if ($open) {
                            if ($testEnd === $endString) {
                                if ($comment) {
                                    $comment = false;

                                    if ($lenEndString > 1) {
                                        $i += ($lenEndString - 1);
                                        $current = $sql[$i] ?? '';
                                    }

                                    $start = $i + 1;
                                }

                                $open      = false;
                                $endString = '';
                            }
                        } else {
                            $open = true;

                            if ($current2 === '--') {
                                $endString = "\n";
                                $comment   = true;
                            } elseif ($current2 === '/*') {
                                $endString = '*/';
                                $comment   = true;
                            } elseif ($current === '#') {
                                $endString = "\n";
                                $comment   = true;
                            } else {
                                $endString = $current;
                            }

                            if ($comment && $start < $i) {
                                $query .= substr($sql, $start, $i - $start);
                            }
                        }
                    }
                }
            }

            if ($comment) {
                $start = $i + 1;
            }

            if (($current === ';' && !$open) || $i === $end - 1) {
                if ($start <= $i) {
                    $query .= substr($sql, $start, $i - $start + 1);
                }

                $query = trim($query);

                if ($query) {
                    if (($i === $end - 1) && ($current !== ';')) {
                        $query .= ';';
                    }

                    $queries[] = $query;
                }

                $query = '';
                $start = $i + 1;
            }
        }

        return $queries;
    }

    /**
     * Rewrite a dump so its generated columns can be loaded.
     *
     * MySQL refuses any statement that supplies a value for a generated column
     * (error 3105). Cwmbackup leaves them out of its own exports, but a
     * mysqldump of the same database lists them like any other column, so every
     * INSERT naming one is rejected — on the file that prompted this, all 14
     * `#__bsms_studies` statements, which is every message on the site.
     *
     * Dropping the column from the CREATE TABLE does not help: the INSERT still
     * names it and fails as an unknown column instead. So the column is demoted
     * to an ordinary one for the load and converted back afterwards, which also
     * recomputes every value from the expression.
     *
     * ⚠️ Any index spanning the column has to go before the column does.
     * Dropping `studynumber_uk` out from under
     * `UNIQUE (series_id, studynumber_uk)` would otherwise leave a UNIQUE index
     * on `series_id` alone, which no site with two messages in a series can
     * satisfy.
     *
     * @param   string  $sql  The dump's full text
     *
     * @return  array{sql: string, restore: string[]}  Rewritten dump, and the
     *                                                 statements that put the
     *                                                 generated columns back
     *
     * @since __DEPLOY_VERSION__
     */
    public static function normaliseGeneratedColumns(string $sql): array
    {
        $restore = [];

        // Each CREATE TABLE block, from the opening line to the closing paren.
        $blocks = preg_match_all(
            '/CREATE TABLE(?: IF NOT EXISTS)? `(?<table>[^`]+)` \((?<body>.*?)\n\) ?(?<tail>[^;]*);/s',
            $sql,
            $matches,
            PREG_SET_ORDER
        );

        if (!$blocks) {
            return ['sql' => $sql, 'restore' => []];
        }

        foreach ($matches as $block) {
            $table = $block['table'];
            $body  = $block['body'];

            if (stripos($body, 'GENERATED ALWAYS') === false) {
                continue;
            }

            $newBody = $body;

            // A generated column definition, capturing its type and expression.
            $found = preg_match_all(
                '/^\s*`(?<col>[^`]+)` (?<type>[a-z]+(?:\([^)]*\))?(?: unsigned)?) GENERATED ALWAYS AS (?<expr>.+?) (?<kind>STORED|VIRTUAL),?$/mi',
                $body,
                $columns,
                PREG_SET_ORDER
            );

            if (!$found) {
                continue;
            }

            foreach ($columns as $column) {
                $col = $column['col'];

                // Demote to a plain nullable column so the dump's INSERT, which
                // supplies a value for it, is accepted.
                $newBody = str_replace(
                    $column[0],
                    '  `' . $col . '` ' . $column['type'] . ' DEFAULT NULL,',
                    $newBody
                );

                // An index spanning the column is left in place: a plain column
                // indexes exactly as a generated one does, and the values are
                // the same either way. It only has to come off to let the
                // column be dropped at the end, and go straight back.
                $indexes = [];
                preg_match_all(
                    '/^\s*(?<unique>UNIQUE )?KEY `(?<name>[^`]+)` \((?<cols>[^)]*`' . preg_quote($col, '/') . '`[^)]*)\),?$/mi',
                    $body,
                    $indexes,
                    PREG_SET_ORDER
                );

                foreach ($indexes as $index) {
                    $restore[] = 'ALTER TABLE `' . $table . '` DROP INDEX `' . $index['name'] . '`';
                }

                $restore[] = 'ALTER TABLE `' . $table . '` DROP COLUMN `' . $col . '`';
                $restore[] = 'ALTER TABLE `' . $table . '` ADD COLUMN `' . $col . '` '
                    . strtoupper($column['type']) . ' GENERATED ALWAYS AS ' . $column['expr']
                    . ' ' . strtoupper($column['kind']);

                foreach ($indexes as $index) {
                    $restore[] = 'ALTER TABLE `' . $table . '` ADD '
                        . ($index['unique'] ? 'UNIQUE ' : '')
                        . 'KEY `' . $index['name'] . '` (' . $index['cols'] . ')';
                }
            }

            $sql = str_replace($body, $newBody, $sql);
        }

        return ['sql' => $sql, 'restore' => $restore];
    }

    /**
     * Tables a restore must leave exactly as it found them.
     *
     * These belong to lib_cwmscripture, not to this component. They only carry
     * the `bsms_` prefix because the library inherited Proclaim's historical
     * naming, which is why the prefix-driven table list sweeps them up at all.
     * None of them is part of the user's *content*, and all of them describe
     * the target site rather than the backed-up one.
     *
     * ⚠️ The catalogue travels with the verses. #__bsms_bible_verses was
     * preserved alone up to 10.5.9, and #__bsms_bible_translations — which
     * records which of those verses are downloaded, via `installed` — was
     * dropped and replaced from the backup. Splitting the pair makes the Local
     * Translations panel wrong in both directions: restoring a backup taken
     * before a download leaves ~93,000 orphaned verses with the catalogue
     * claiming nothing is installed, and restoring one taken after a download
     * into a site without it claims translations that have no verses.
     *
     * The provider cache expires and rebuilds itself, so carrying another
     * site's rows in only risks serving stale passages. The consumer registry
     * is derived state guarding a destructive operation — it must describe the
     * extensions installed *here*, so it is never taken from a backup.
     *
     * @var string[]
     * @since 10.1.0
     */
    private static array $preserveTables = [
        '#__bsms_bible_verses',
        '#__bsms_bible_translations',
        '#__bsms_scripture_cache',
        '#__bsms_scripture_consumers',
    ];

    /**
     * Does this statement target a table the restore must not touch?
     *
     * ⚠️ Excluding a table from the DROP phase is not enough on its own. The
     * backup file still carries that table's own `INSERT INTO` statements, and
     * isSafeRestoreStatement() is a security allow-list — it asks whether the
     * target is a Proclaim table, not whether it is preserved — so those
     * inserts ran against the "preserved" table anyway. The docblock above used
     * to argue that `CREATE TABLE IF NOT EXISTS` made the backup's copy a
     * no-op, which is true of the DDL and says nothing about the rows.
     *
     * @param   string  $statement  A single SQL statement
     *
     * @return  bool  True when the statement should be skipped entirely
     *
     * @since __DEPLOY_VERSION__
     */
    private static function targetsPreservedTable(string $statement): bool
    {
        if (!preg_match('/`?(?<table>#__bsms_[a-z0-9_]+)`?/i', $statement, $matches)) {
            return false;
        }

        return \in_array(strtolower($matches['table']), self::$preserveTables, true);
    }

    /**
     * Get Objects for tables
     *
     * @return array
     *
     * @since 7.0.0
     */
    protected static function getObjects(): array
    {
        $db        = Factory::getContainer()->get(DatabaseInterface::class);
        $tables    = $db->getTableList();
        $prefix    = $db->getPrefix();
        $prelength = \strlen($prefix);
        $bsms      = 'bsms_';
        $objects   = [];

        foreach ($tables as $table) {
            // Anchored, not a substring test. getTableList() returns every table
            // in the schema, so on shared-database hosting a sibling install's
            // `myjos_bsms_studies` matched here while substr_replace() stripped a
            // fixed prefix length regardless, yielding a mangled
            // `#__s_bsms_studies`. CwmdbHelper::getObjects() was corrected for
            // exactly this; that fix never reached this copy.
            if (str_starts_with($table, $prefix . $bsms)) {
                $table     = substr_replace($table, '#__', 0, $prelength);

                // Skip tables that should survive a restore
                if (\in_array($table, self::$preserveTables, true)) {
                    continue;
                }

                $objects[] = ['name' => $table];
            }
        }

        return $objects;
    }

    /**
     * Modify tables to Text
     *
     * Import DB
     *
     * @param bool $parent Switch to see if it is coming from migration or restore.
     *
     * @return bool|array
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function importdb(bool $parent): bool|array
    {
        $input         = Factory::getApplication()->getInput();
        $installType   = $input->getPath('install_directory');
        $backupRestore = $input->getCmd('backuprestore', '');
        $dBo           = Factory::getContainer()->get(DatabaseInterface::class);

        // Restore form prior backup files located on the server.
        if (substr_count($backupRestore, '.sql')) {
            $restored      = self::restoreDB($backupRestore);

            if ($restored) {
                return true;
            }
            return false;
        }

        // Start finding how to restore files.
        if (
            !empty($installType) && $installType !== '/' && $installType !== Factory::getApplication()->getConfig()->get(
                'tmp_path'
            ) . '/'
        ) {
            $uploadresults = self::getPackageFromFolder();
        } else {
            $uploadresults = $this->getPackageFromUpload();
        }
        $result = $uploadresults;

        if ($result) {
            switch ($result['type']) {
                case 'dir':
                    $src     = Folder::files($result['dir'], '.', true, true);
                    $tmp_src = $src[0];
                    break;
                case 'file':
                    $tmp_src = $result['dir'];
                    break;
                default:
                    throw new \InvalidArgumentException('Unknown Archive Type');
            }

            $result = self::installdb($tmp_src, $parent);

            if ($result) {
                // Get Proclaim extension ID
                $query = $dBo->createQuery();
                $query->select($dBo->quoteName('extension_id'));
                $query->from($dBo->quoteName('#__extensions'));
                $query->where($dBo->quoteName('element') . ' = ' . $dBo->q('com_proclaim'));
                $dBo->setQuery($query);
                $cid = (int) $dBo->loadResult();

                // Reset #__schemas so DatabaseModel::fix() re-runs all migrations.
                // The restore replaced all bsms_* tables with backup data, but
                // #__schemas (a Joomla core table) still claims the latest version.
                // Without this reset, fix() thinks everything is up-to-date and
                // skips creating tables that were added after the backup was made.
                if ($cid) {
                    self::resetSchemaVersion($cid);
                }

                // Fix the Proclaim Database schema after restore
                /** @var DatabaseModel $databaseModel */
                $databaseModel = Factory::getApplication()->bootComponent('com_installer')
                    ->getMVCFactory()->createModel('Database', 'Administrator', ['ignore_request' => true]);
                $databaseModel->fix([$cid]);

                // Run PHP data migration steps that ChangeSet cannot handle
                self::runPostRestoreDataFixes();

                // Fix Proclaim assets (ACL permissions)
                self::fixAssetsAfterRestore();

                // Fix object ownership for migrated data
                self::fixOwnershipAfterRestore();

                /** @var UpdateModel $updateModel */
                $updateModel = Factory::getApplication()->bootComponent('com_joomlaupdate')
                    ->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);
                $updateModel->purge();

                // Refresh versionable assets cache
                Factory::getApplication()->flushAssets();
            }

            // Clean up the installation files.
            if (!is_file($uploadresults['packagefile'])) {
                $config                       = Factory::getApplication()->getConfig();
                $uploadresults['packagefile'] = $config->get('tmp_path') . '/' . $uploadresults['packagefile'];
            }

            InstallerHelper::cleanupInstall($uploadresults['packagefile'], $uploadresults['extractdir']);
        }

        return $result;
    }

    /**
     * Restore DB for Proclaim
     *
     * @param   string  $backuprestore  file name to restore
     *
     * @return bool See if the restore worked.
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public static function restoreDB(string $backuprestore): bool
    {
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get(DatabaseInterface::class);
        /**
         * Attempt to increase the maximum execution time for PHP scripts with a check for safe_mode.
         */
        set_time_limit(3000);

        $query = file_get_contents(JPATH_SITE . '/media/com_proclaim/backup/' . $backuprestore);

        // Check to see if this is a backup from an old DB and not a migration
        $sold    = substr_count($query, '#__bsms_admin_genesis');
        $isNot   = substr_count($query, '#__bsms_studies');

        if ($sold !== 0 && $isNot === 0) {
            $app->enqueueMessage(Text::_('JBS_IBM_OLD_DB'), 'warning');

            return false;
        }

        if ($isNot === 0) {
            $app->enqueueMessage(Text::_('JBS_IBM_NOT_DB'), 'warning');

            return false;
        }

        $queries = $db->splitSql($query);

        foreach ($queries as $query) {
            $query = trim($query);

            if ($query === '' || $query[0] === '#') {
                continue;
            }

            if (!self::isSafeRestoreStatement($query)) {
                $app->enqueueMessage(Text::_('JBS_IBM_NOT_DB'), 'error');

                return false;
            }

            // Preserved tables are excluded from the DROP phase; skipping their
            // statements here is what actually leaves the rows alone.
            if (self::targetsPreservedTable($query)) {
                continue;
            }

            $db->setQuery($query);
            $db->execute();
        }

        // After restoring, reset the schema version and run DatabaseModel::fix()
        // so that any tables/columns added after the backup was created get applied.
        try {
            $query = $db->createQuery();
            $query->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'));
            $db->setQuery($query);
            $cid = (int) $db->loadResult();

            if ($cid) {
                self::resetSchemaVersion($cid);

                /** @var DatabaseModel $databaseModel */
                $databaseModel = Factory::getApplication()->bootComponent('com_installer')
                    ->getMVCFactory()->createModel('Database', 'Administrator', ['ignore_request' => true]);
                $databaseModel->fix([$cid]);
            }
        } catch (\Exception $e) {
            $app->enqueueMessage('Schema repair notice: ' . $e->getMessage(), 'warning');
        }

        // Run PHP data migration steps that ChangeSet cannot handle
        self::runPostRestoreDataFixes();

        // Fix Proclaim assets (ACL permissions)
        self::fixAssetsAfterRestore();

        // Fix object ownership for migrated data
        self::fixOwnershipAfterRestore();

        // Verify restore integrity
        $integrity = self::verifyRestoreIntegrity();
        Log::add(
            \sprintf(
                'Restore verification: %d tables, %d tasks, config %s',
                $integrity['tables'],
                $integrity['tasks'],
                $integrity['config'] ? 'OK' : 'missing'
            ),
            Log::INFO,
            'com_proclaim'
        );

        return true;
    }

    /**
     * Get Package from Folder
     *
     * @return array|bool
     *
     * @throws \Exception
     * @since 9.0.0
     */
    private static function getPackageFromFolder(): bool|array
    {
        $input = Factory::getApplication()->getInput();

        // Get the path to the package to install.
        $p_dir = $input->getString('install_directory');
        $p_dir = Path::clean($p_dir);

        // Did you give us a valid directory?
        if (!is_dir($p_dir)) {
            throw new \RuntimeException(Text::_('COM_INSTALLER_MSG_INSTALL_PLEASE_ENTER_A_PACKAGE_DIRECTORY'), 502);
        }

        $package['packagefile'] = null;
        $package['extractdir']  = null;
        $package['dir']         = $p_dir;
        $package['type']        = 'dir';

        return $package;
    }

    /**
     * Get Package form Upload
     *
     * @return bool|array
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function getPackageFromUpload(): bool|array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        // Get the uploaded file information
        $userFile = $input->files->get('importdb', null, 'raw');

        // Make sure that file uploads are enabled in PHP
        if (!(bool)\ini_get('file_uploads')) {
            $app->enqueueMessage(Text::_('JBS_IBM_ERROR_PHP_UPLOAD_NOT_ENABLED'), 'warning');

            return false;
        }

        // Ensure that zlib is loaded so that the package can be unpacked.
        if (!\extension_loaded('zlib')) {
            $app->enqueueMessage(Text::_('JBS_IBM_ERROR_UPLOAD_FAILED_ZLIB'), 'error');

            return false;
        }

        // If there is no uploaded file, we have a problem...
        if (!\is_array($userFile)) {
            $app->enqueueMessage(Text::_('JBS_CMN_NO_FILE_SELECTED'), 'warning');

            return false;
        }

        // Is the PHP tmp directory missing?
        if ($userFile['error'] && ($userFile['error'] == UPLOAD_ERR_NO_TMP_DIR)) {
            $app->enqueueMessage(
                Text::_('JBS_IBM_ERROR_UPLOAD_FAILED') . '<br />' . Text::_(
                    'JBS_IBM_ERROR_UPLOAD_FAILED_PHPUPLOADNOTSET',
                    'error'
                )
            );

            return false;
        }

        // Is the max upload size too small in php.ini?
        if ($userFile['error'] && ($userFile['error'] == UPLOAD_ERR_INI_SIZE)) {
            $app->enqueueMessage(
                Text::_('JBS_IBM_ERROR_UPLOAD_FAILED') . '<br />' . Text::_(
                    'JBS_IBM_ERROR_UPLOAD_FAILED_SMALLUPLOADSIZE',
                    'error'
                )
            );

            return false;
        }

        // Check if there was a problem uploading the file.
        if ($userFile['error'] || $userFile['size'] < 1) {
            $app->enqueueMessage(Text::_('JBS_IBM_ERROR_UPLOAD_FAILED'), 'warning');

            return false;
        }

        // Build the appropriate paths
        $config   = Factory::getApplication()->getConfig();
        $safeName = File::makeSafe($userFile['name']);
        $tmp_dest = $config->get('tmp_path') . '/' . basename($safeName);
        $tmp_src  = $userFile['tmp_name'];

        // Move an uploaded file.
        File::upload($tmp_src, $tmp_dest, false, true);

        if (!str_ends_with($tmp_dest, 'sql') && str_ends_with($tmp_dest, 'sql.zip')) {
            // Unpack the downloaded package file.
            $package         = InstallerHelper::unpack($tmp_dest, true);
            if (!isset($package['dir'])) {
                throw new \RuntimeException('Compressed file did not extract.', 500);
            }
            $package['type'] = 'dir';
        } else {
            $package['packagefile'] = null;
            $package['extractdir']  = null;
            $package['dir']         = $tmp_dest;
            $package['type']        = 'file';
        }

        return $package;
    }

    /**
     * Install DB
     *
     * @param   string  $tmp_src  Temp info
     * @param   bool    $parent   To tell if coming from migration
     *
     * @return bool if db installed correctly.
     *
     * @throws \Exception
     * @since 9.0.0
     */
    protected static function installdb(string $tmp_src, bool $parent = true): bool
    {
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get(DatabaseInterface::class);

        $query = file_get_contents($tmp_src);

        // Graceful exit and rollback if read is not successful
        if ($query === false) {
            $app->enqueueMessage(Text::_('JBS_INS_ERROR_SQL_READBUFFER'), 'error');

            return false;
        }
        // Check if sql file is for Joomla! Bible Studies
        $sold    = substr_count($query, '#__bsms_admin_genesis');
        $isNot   = substr_count($query, '#__bsms_studies');

        if ($sold !== 0 && $isNot === 0) {
            $app->enqueueMessage(Text::_('JBS_IBM_OLD_DB'), 'warning');

            return false;
        }

        if ($isNot === 0) {
            $app->enqueueMessage('Extracted file: ' . basename($tmp_src), 'warning');
            $app->enqueueMessage(Text::_('JBS_IBM_NOT_DB'), 'warning');

            return false;
        }

        // First, we need to drop the existing JBS tables
        $objects = self::getObjects();

        foreach ($objects as $object) {
            $dropper = 'DROP TABLE IF EXISTS ' . $db->quoteName($object['name']);
            $db->setQuery($dropper);
            $db->execute();
        }

        // Create an array of queries from the SQL file
        $queries = $db->splitSql($query);

        if (\count($queries) === 0) {
            // No queries to process
            return false;
        }

        // Process each query in the $queries array (split out of the SQL file).
        foreach ($queries as $query) {
            $query = trim($query);

            if ($query === '' || $query[0] === '#') {
                continue;
            }

            if (!self::isSafeRestoreStatement($query)) {
                $app->enqueueMessage(Text::_('JBS_IBM_NOT_DB'), 'error');

                return false;
            }

            // Preserved tables are excluded from the DROP phase; skipping their
            // statements here is what actually leaves the rows alone.
            if (self::targetsPreservedTable($query)) {
                continue;
            }

            $db->setQuery($query);

            if (!$db->execute()) {
                $app->enqueueMessage(Text::sprintf('JBS_IBM_INSTALLDB_ERRORS', $db->stderr(true)), 'error');

                return false;
            }
        }

        return true;
    }

    /**
     * Reset the #__schemas version for Proclaim so that DatabaseModel::fix()
     * re-runs all SQL update files.
     *
     * After a restore, the bsms_* tables come from the backup but #__schemas
     * (a Joomla core table) still holds the version from before the restore.
     * This mismatch causes DatabaseModel::fix() to skip migrations, leaving
     * tables that were added after the backup was created are missing entirely.
     *
     * @param   int  $extensionId  The Proclaim extension ID
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected static function resetSchemaVersion(int $extensionId): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Delete the current schema entry
        $query = $db->createQuery();
        $query->delete($db->quoteName('#__schemas'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $db->setQuery($query);
        $db->execute();

        // Insert a baseline version before all update files so fix() runs everything
        $query = $db->createQuery();
        $query->insert($db->quoteName('#__schemas'))
            ->columns([$db->quoteName('extension_id'), $db->quoteName('version_id')])
            ->values($extensionId . ', ' . $db->quote('0.0.0'));
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Run PHP data migration steps after a database restore.
     *
     * DatabaseModel::fix() only runs SQL DDL via Joomla's ChangeSet — it skips
     * UPDATE/DELETE/INSERT statements. This method runs the PHP finish steps
     * that handles data migration, the ChangeSet cannot.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected static function runPostRestoreDataFixes(): void
    {
        try {
            $fixed = CwmmigrationHelper::fixTeacherAliases();
            Log::add('Post-restore: fixed ' . $fixed . ' teacher alias/duplicate issues', Log::INFO, 'com_proclaim');
        } catch (\Exception $e) {
            Log::add('Post-restore teacher alias fix failed: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
        }

        try {
            $inserted = CwmmigrationHelper::populateStudyTeachers();
            Log::add('Post-restore: populated ' . $inserted . ' study-teacher junction records', Log::INFO, 'com_proclaim');
        } catch (\Exception $e) {
            Log::add('Post-restore study-teacher population failed: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
        }

        // Populate scripture junction table from legacy flat columns
        try {
            $migrated = CwmscriptureMigration::migrate();
            Log::add('Post-restore: migrated ' . $migrated . ' scripture junction records', Log::INFO, 'com_proclaim');
        } catch (\Exception $e) {
            Log::add('Post-restore scripture migration failed: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
        }

        // Seed bible translations catalogue (INSERT IGNORE preserves existing data)
        try {
            $seeded = CwmmigrationHelper::seedBibleTranslations();
            Log::add('Post-restore: seeded ' . $seeded . ' bible translations', Log::INFO, 'com_proclaim');
        } catch (\Exception $e) {
            Log::add('Post-restore bible translation seed failed: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
        }

        // Fix legacy image paths in mediafile params (images/biblestudy/ -> media/com_proclaim/images/)
        try {
            $fixed = CwmmigrationHelper::fixMediafileLegacyPaths();
            Log::add('Post-restore: fixed legacy paths in ' . $fixed . ' mediafile rows', Log::INFO, 'com_proclaim');
        } catch (\Exception $e) {
            Log::add('Post-restore mediafile path fix failed: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
        }

        // Merge form XML defaults into template params
        try {
            $migration = new CwmtemplatemigrationHelper();
            $updated   = $migration->migrateAll();
            Log::add('Post-restore: updated ' . $updated . ' templates with default params', Log::INFO, 'com_proclaim');
        } catch (\Exception $e) {
            Log::add('Post-restore template defaults merge failed: ' . $e->getMessage(), Log::WARNING, 'com_proclaim');
        }
    }

    /**
     * Fix Proclaim assets after database restore
     *
     * This method rebuilds the asset relationships for all Proclaim tables
     * to ensure ACL permissions work correctly after a restore.
     *
     * @return void
     *
     * @since 10.1.0
     */
    protected static function fixAssetsAfterRestore(): void
    {
        $app = Factory::getApplication();

        try {
            Cwmassets::fixAllAssets();
            $app->enqueueMessage(Text::_('JBS_IBM_ASSETS_FIXED'), 'info');
        } catch (\Exception $e) {
            $app->enqueueMessage(
                Text::_('JBS_IBM_ASSETS_FIX_MANUAL') . ' ' . $e->getMessage(),
                'warning'
            );
        }
    }

    /**
     * Public wrapper for ownership fix (used by AJAX controller)
     *
     * @return void
     *
     * @since 10.1.0
     */
    public static function fixOwnershipPublic(): void
    {
        self::fixOwnershipAfterRestore();
    }

    /**
     * Fix object ownership after database restore
     *
     * When restoring a database from another site, created_by and modified_by
     * fields may reference user IDs that don't exist. This method updates
     * those fields to use the current user's ID.
     *
     * @return void
     *
     * @since 10.1.0
     */
    protected static function fixOwnershipAfterRestore(): void
    {
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get(DatabaseInterface::class);

        // Get the current user ID (the person doing the restore)
        $currentUserId = $app->getIdentity()->id;

        if (!$currentUserId) {
            return;
        }

        // Tables with ownership fields
        $tablesWithOwnership = [
            '#__bsms_studies',
            '#__bsms_teachers',
            '#__bsms_series',
            '#__bsms_mediafiles',
            '#__bsms_servers',
            '#__bsms_podcast',
        ];

        $totalFixed = 0;

        foreach ($tablesWithOwnership as $table) {
            try {
                // Fix created_by where user doesn't exist
                $query = $db->createQuery();
                $query->update($db->quoteName($table))
                    ->set($db->quoteName('created_by') . ' = ' . (int) $currentUserId)
                    ->where($db->quoteName('created_by') . ' NOT IN (SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__users') . ')')
                    ->where($db->quoteName('created_by') . ' != 0');
                $db->setQuery($query);
                $db->execute();
                $totalFixed += $db->getAffectedRows();

                // Fix modified_by where user doesn't exist
                $query = $db->createQuery();
                $query->update($db->quoteName($table))
                    ->set($db->quoteName('modified_by') . ' = ' . (int) $currentUserId)
                    ->where($db->quoteName('modified_by') . ' NOT IN (SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__users') . ')')
                    ->where($db->quoteName('modified_by') . ' != 0');
                $db->setQuery($query);
                $db->execute();
                $totalFixed += $db->getAffectedRows();
            } catch (\Exception $e) {
                // Table might not have the column, skip it
                continue;
            }
        }

        if ($totalFixed > 0) {
            $app->enqueueMessage(Text::sprintf('JBS_IBM_OWNERSHIP_FIXED', $totalFixed), 'info');
        }
    }

    /**
     * Correct AUTO_INCREMENT counters on all Proclaim tables.
     *
     * After a database restore the AUTO_INCREMENT value embedded in the
     * backup's CREATE TABLE may be lower than the actual MAX(id) — especially
     * for preserved tables like bible_verses that survive the DROP phase.
     * This causes duplicate-key errors on the next INSERT.
     *
     * @return int Number of tables whose AUTO_INCREMENT was corrected
     *
     * @since 10.1.0
     */
    public static function correctAutoIncrements(): int
    {
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        // Own tables only: the restore no longer writes the shared scripture
        // stack, so correcting its AUTO_INCREMENT would be this component
        // reaching into the library's tables for no reason.
        $tables  = CwmdbHelper::getOwnObjects();
        $prefix  = $db->getPrefix();
        $fixed   = 0;

        foreach ($tables as $tableInfo) {
            $abstractName = $tableInfo['name'];                       // e.g. #__bsms_studies
            $realName     = str_replace('#__', $prefix, $abstractName);

            try {
                // Check whether the table has an `id` column.
                //
                // 'id' carries no underscore, so this one was never exposed to
                // the LIKE wildcard the way a prefixed table name is. Converted
                // anyway: leaving the last example of the pattern in the tree is
                // how it gets copied into somewhere that does have one.
                if (!CwmdbHelper::columnExists($abstractName, 'id')) {
                    continue;
                }

                // Delete id=0 rows — these are restore artifacts, not real content.
                // Joomla uses id=0 as the "new record" sentinel in forms, so a
                // persisted row with id=0 causes conflicts and silent overwrites.
                $query = $db->createQuery();
                $query->delete($db->quoteName($abstractName))
                    ->where($db->quoteName('id') . ' = 0');
                $db->setQuery($query);
                $db->execute();
                $deletedZero = $db->getAffectedRows();

                if ($deletedZero > 0) {
                    Log::add(
                        \sprintf('Deleted %d id=0 row(s) from %s', $deletedZero, $abstractName),
                        Log::INFO,
                        'com_proclaim'
                    );
                    $fixed++;
                }

                // Get current AUTO_INCREMENT from table status
                $db->setQuery(
                    'SHOW TABLE STATUS WHERE ' . $db->quoteName('Name') . ' = ' . $db->quote($realName)
                );
                $status = $db->loadObject();

                if (!$status || $status->Auto_increment === null) {
                    continue;
                }

                $currentAuto = (int) $status->Auto_increment;

                // Get actual maximum ID in the table
                $query = $db->createQuery();
                $query->select('MAX(' . $db->quoteName('id') . ')')
                    ->from($db->quoteName($abstractName));
                $db->setQuery($query);
                $maxId = (int) $db->loadResult();

                // Fix if the counter is stale (max ID >= current auto_increment)
                if ($maxId >= $currentAuto) {
                    $newAuto = $maxId + 1;
                    $db->setQuery(
                        'ALTER TABLE ' . $db->quoteName($abstractName) . ' AUTO_INCREMENT = ' . $newAuto
                    );
                    $db->execute();
                    $fixed++;

                    Log::add(
                        \sprintf(
                            'Corrected AUTO_INCREMENT on %s: was %d, MAX(id)=%d, set to %d',
                            $abstractName,
                            $currentAuto,
                            $maxId,
                            $newAuto
                        ),
                        Log::INFO,
                        'com_proclaim'
                    );
                }
            } catch (\Exception $e) {
                Log::add(
                    'AUTO_INCREMENT check failed for ' . $abstractName . ': ' . $e->getMessage(),
                    Log::WARNING,
                    'com_proclaim'
                );
            }
        }

        return $fixed;
    }

    /**
     * Verify that component config and tasks were restored successfully
     *
     * @return array ['config' => bool, 'tasks' => int, 'tables' => int]
     *
     * @since 10.1.0
     */
    public static function verifyRestoreIntegrity(): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Check component config (params should be substantial JSON)
        $query = $db->createQuery();
        $query->select('LENGTH(' . $db->quoteName('params') . ')')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->q('com_proclaim'))
            ->where($db->quoteName('type') . ' = ' . $db->q('component'));
        $db->setQuery($query);
        $configSize = (int) $db->loadResult();

        // Check scheduled tasks count
        $tasksCount     = 0;
        $tables         = $db->getTableList();
        $prefix         = $db->getPrefix();
        $schedulerTable = $prefix . 'scheduler_tasks';

        if (\in_array($schedulerTable, $tables, true)) {
            $query = $db->createQuery();
            $query->select('COUNT(*)')
                ->from($db->quoteName('#__scheduler_tasks'))
                ->where($db->quoteName('type') . ' LIKE ' . $db->q('proclaim.%'));
            $db->setQuery($query);
            $tasksCount = (int) $db->loadResult();
        }

        // Check Proclaim tables count
        // Count what a restore actually covers, not every bsms_ table in the
        // schema -- the shared scripture stack is not part of the round trip.
        $proclaimTables = CwmdbHelper::getOwnObjects();

        return [
            'config' => $configSize > 10, // Params should be substantial JSON
            'tasks'  => $tasksCount,
            'tables' => \count($proclaimTables),
        ];
    }
}
