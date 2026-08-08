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
use CWM\Component\Proclaim\Administrator\Helper\Cwmmime;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use CWM\Component\Proclaim\Administrator\Helper\Version;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

/**
 * JBS Export class
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class Cwmbackup
{
    /*
    **********************************************************************
    * File handling fields
    **********************************************************************
     */

    /** @var string Absolute path to dump file; must be writable (optional; if left blank it is automatically calculated)
     *
     * @since 9.0.0
     */
    protected string $dumpFile = '';

    /** @var string Relative path of how the file should be saved in the archive
     *
     * @since 9.0.0
     */
    protected string $saveAsName = '';

    /**
     * Cached list of this component's own table names (with `#__` prefix), as
     * returned by CwmdbHelper::getObjects(). Populated on first use.
     *
     * @var string[]|null
     *
     * @since 10.5.6
     */
    private static ?array $knownTables = null;

    /**
     * Reject any table name that isn't one of this component's own tables.
     *
     * The table-export methods below take `$table` from AJAX request input
     * (see CwmbackupController::exportTableXHR()) and interpolate it directly
     * into `SHOW CREATE TABLE`/`SELECT *` queries. Without this check, a
     * request for e.g. `#__users` would export Joomla's user table — including
     * password hashes — through an endpoint meant only for this component's
     * own `#__bsms_*` tables. Validating here (not just in the controller)
     * keeps every export method safe regardless of caller.
     *
     * @param   string  $table  Full table name (with `#__` prefix) to check
     *
     * @return  bool
     *
     * @since 10.5.6
     */
    private static function isKnownProclaimTable(string $table): bool
    {
        if (self::$knownTables === null) {
            self::$knownTables = array_column(CwmdbHelper::getObjects(), 'name');
        }

        if (\in_array($table, self::$knownTables, true)) {
            return true;
        }

        Log::add('Rejected export/count request for non-Proclaim table: ' . $table, Log::WARNING, 'com_proclaim');

        return false;
    }

    /**
     * Cached per-table ORDER BY clause (built from the table's actual
     * primary key), keyed by table name. Populated on first use.
     *
     * @var array<string, string>
     *
     * @since 10.5.6
     */
    private static array $primaryKeyOrderClauses = [];

    /**
     * Cached per-table set of generated column names, keyed by table name.
     * Populated on first use.
     *
     * @var array<string, array<string, true>>
     *
     * @since 10.5.6
     */
    private static array $generatedColumns = [];

    /**
     * List the columns a table computes for itself.
     *
     * A generated column's value comes from its expression, and MySQL rejects
     * any statement that writes one. The restored dump recreates the column
     * from SHOW CREATE TABLE, so an exported INSERT naming it fails the whole
     * restore. #__bsms_studies.studynumber_uk and
     * #__bsms_playlists.remote_playlist_uk are two such columns.
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   string             $table  Full table name (with `#__` prefix)
     *
     * @return  array<string, true>  Column names as keys
     *
     * @since 10.5.6
     */
    private static function getGeneratedColumns(DatabaseInterface $db, string $table): array
    {
        if (isset(self::$generatedColumns[$table])) {
            return self::$generatedColumns[$table];
        }

        $generated = [];

        try {
            foreach ($db->getTableColumns($table, false) as $name => $column) {
                if (stripos($column->Extra ?? '', 'GENERATED') !== false) {
                    $generated[$name] = true;
                }
            }
        } catch (\RuntimeException) {
            // Treat an unreadable schema as "nothing generated" rather than
            // aborting the backup; the restore would surface any real problem.
        }

        self::$generatedColumns[$table] = $generated;

        return $generated;
    }

    /**
     * Build a deterministic ORDER BY clause from a table's primary key.
     *
     * Handles composite keys by ordering on every key column in index
     * sequence. Returns an empty string for a table with no primary key
     * (rare; the caller falls back to unordered in that case).
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   string             $table  Full table name (with `#__` prefix)
     *
     * @return  string
     *
     * @since 10.5.6
     */
    private static function getPrimaryKeyOrderClause(DatabaseInterface $db, string $table): string
    {
        if (isset(self::$primaryKeyOrderClauses[$table])) {
            return self::$primaryKeyOrderClauses[$table];
        }

        $keys              = $db->getTableKeys($table);
        $primaryKeyColumns = [];

        foreach ($keys as $key) {
            if (($key->Key_name ?? '') === 'PRIMARY') {
                $primaryKeyColumns[(int) $key->Seq_in_index] = $key->Column_name;
            }
        }

        ksort($primaryKeyColumns);

        $clause = implode(', ', array_map(
            static fn ($column) => $db->quoteName($column) . ' ASC',
            $primaryKeyColumns
        ));

        return self::$primaryKeyOrderClauses[$table] = $clause;
    }

    /**
     * Generate a standardized backup filename
     *
     * Format: proclaim-backup_SiteName_YYYY-MM-DD_vX.X.X.sql
     *
     * @return string The generated filename
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public static function generateBackupFilename(): string
    {
        $app    = Factory::getApplication();
        $config = $app->getConfig();

        // Get site name and sanitize it
        $siteName = $config->get('sitename', 'site');
        $siteName = strtolower(trim(preg_replace('#[^a-zA-Z0-9]+#', '-', $siteName), '-'));

        // Limit site name length
        if (\strlen($siteName) > 30) {
            $siteName = substr($siteName, 0, 30);
        }

        // Get current date in ISO format
        $date = date('Y-m-d');

        // Get Proclaim version.
        //
        // From the installed manifest, not Version.php's constants: those are
        // maintained by hand and are not touched by the release tooling, so
        // they drift. They read 10.3.1 while versions.json says 10.5.5, which
        // put a version two minor releases stale into every backup filename --
        // exactly the thing a filename like this exists to record.
        $version = CwmproclaimHelper::getVersion();

        return \sprintf('proclaim-backup_%s_%s_v%s.sql', $siteName, $date, $version);
    }

    /**
     * Export component configuration from #__extensions table.
     *
     * Returns SQL UPDATE statement to restore component params.
     *
     * @return string SQL statement
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function getComponentConfigExport(): string
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Always emit the section header so backup dumps stay self-documenting
        // even on systems where com_proclaim isn't yet registered.
        $export  = "\n-- --------------------------------------------------------\n";
        $export .= "-- Component Configuration (com_proclaim)\n";
        $export .= "-- --------------------------------------------------------\n\n";

        try {
            $query = $db->createQuery()
                ->select($db->quoteName(['extension_id', 'params']))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->q('com_proclaim'))
                ->where($db->quoteName('type') . ' = ' . $db->q('component'));
            $db->setQuery($query);
            $result = $db->loadObject();
        } catch (\Throwable $e) {
            return $export . "-- Extensions table not available: " . $e->getMessage() . "\n\n";
        }

        if (!$result || empty($result->params)) {
            return $export . "-- No component configuration found\n\n";
        }

        // UPDATE statement (not INSERT — we overwrite an existing row).
        $export .= "UPDATE " . $db->quoteName('#__extensions') . " SET ";
        $export .= $db->quoteName('params') . " = " . $db->q($result->params);
        $export .= " WHERE " . $db->quoteName('element') . " = " . $db->q('com_proclaim');
        $export .= " AND " . $db->quoteName('type') . " = " . $db->q('component') . ";\n\n";

        return $export;
    }

    /**
     * Export Proclaim asset permissions from `#__assets`.
     *
     * Dumps two things in one block:
     *   1. The component-level ACL — the `rules` column on the row where
     *      `name = 'com_proclaim'`. That's the Permissions tab shown in
     *      the component Options dialog (who can core.edit / core.create /
     *      etc. at the component level).
     *   2. Per-record ACL — any `com_proclaim.<type>.<id>` row whose rules
     *      column is NOT in `Cwmassets::EMPTY_RULE_VARIANTS`. The empty
     *      variants contribute nothing to permission decisions and are
     *      intentionally skipped to keep backups lean.
     *
     * Replay strategy (the SQL is written to be idempotent and
     * id-insensitive, since `#__assets.id` is auto-increment and will
     * differ between source and destination):
     *
     *   - Parent: `UPDATE #__assets SET rules=... WHERE name='com_proclaim'`
     *     assumes the destination has Proclaim installed so the parent
     *     row already exists.
     *   - Per-record: `DELETE ... WHERE name=<full name>` followed by an
     *     `INSERT ... SELECT p.id, ...` that looks up the destination's
     *     com_proclaim asset id via its name, plus an `UPDATE #__bsms_<type>
     *     SET asset_id = (SELECT id FROM #__assets WHERE name=<full name>)
     *     WHERE id=<record id>` that re-links the source record.
     *   - `lft`, `rgt`, `level` are seeded to 0/0/2; the restore flow
     *     calls `Cwmassets::fixAllAssets()` afterward, which rebuilds the
     *     nested-set tree and assigns correct positions.
     *
     * @return string SQL statements (may be empty if nothing to export)
     *
     * @throws \Exception
     * @since 10.3.0
     */
    public function getProclaimAssetsExport(): string
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $export  = "\n-- --------------------------------------------------------\n";
        $export .= "-- Proclaim Asset Permissions (component-level + per-record ACL)\n";
        $export .= "-- --------------------------------------------------------\n\n";

        // 1. Component-level ACL (rules on the `com_proclaim` parent row).
        $query = $db->createQuery()
            ->select($db->quoteName('rules'))
            ->from($db->quoteName('#__assets'))
            ->where($db->quoteName('name') . ' = ' . $db->quote('com_proclaim'));
        $db->setQuery($query);
        $parentRules = (string) $db->loadResult();

        if ($parentRules !== '' && $parentRules !== '{}' && $parentRules !== '[]') {
            $export .= "-- Component-level ACL (the Permissions tab in Options)\n";
            $export .= 'UPDATE ' . $db->quoteName('#__assets') . ' SET '
                . $db->quoteName('rules') . ' = ' . $db->quote($parentRules)
                . ' WHERE ' . $db->quoteName('name') . ' = ' . $db->quote('com_proclaim') . ";\n\n";
        } else {
            $export .= "-- Component-level ACL inherits from Joomla root (nothing to export)\n\n";
        }

        // 2. Per-record ACL rows with genuine non-default rules.
        $emptyQuoted = implode(
            ',',
            array_map(static fn ($v) => $db->quote($v), Cwmassets::EMPTY_RULE_VARIANTS)
        );

        $query = $db->createQuery()
            ->select($db->quoteName(['name', 'title', 'rules']))
            ->from($db->quoteName('#__assets'))
            ->where($db->quoteName('name') . ' LIKE ' . $db->quote('com_proclaim.%'))
            ->where($db->quoteName('name') . ' <> ' . $db->quote('com_proclaim'))
            ->where($db->quoteName('rules') . ' NOT IN (' . $emptyQuoted . ')');
        $db->setQuery($query);
        $customRules = $db->loadObjectList() ?: [];

        if (empty($customRules)) {
            $export .= "-- No per-record ACL rows with custom permissions\n\n";

            return $export;
        }

        // Map `com_proclaim.<type>.<id>` → source table so we can relink
        // the record's asset_id after the new asset row is inserted.
        $typeToTable = array_column(Cwmassets::getAssetObjects(), 'name', 'assetname');

        $export .= '-- ' . \count($customRules) . " per-record ACL row(s) with custom permissions\n";

        foreach ($customRules as $row) {
            // Idempotent: delete any row with this name on the destination
            // before we re-insert. Raw DELETE is safe because the restore
            // flow calls Cwmassets::fixAllAssets() afterward, which runs
            // Asset::rebuild() to repair lft/rgt.
            $export .= 'DELETE FROM ' . $db->quoteName('#__assets')
                . ' WHERE ' . $db->quoteName('name') . ' = ' . $db->quote($row->name) . ";\n";

            // Resolve the destination's com_proclaim asset id by name and
            // insert the new row as its child. lft/rgt seeded to 0; the
            // nested-set rebuild fixes positions.
            $export .= 'INSERT INTO ' . $db->quoteName('#__assets') . ' ('
                . $db->quoteName('parent_id') . ', '
                . $db->quoteName('lft') . ', '
                . $db->quoteName('rgt') . ', '
                . $db->quoteName('level') . ', '
                . $db->quoteName('name') . ', '
                . $db->quoteName('title') . ', '
                . $db->quoteName('rules') . ') '
                . 'SELECT p.' . $db->quoteName('id') . ', 0, 0, 2, '
                . $db->quote($row->name) . ', '
                . $db->quote((string) ($row->title ?? $row->name)) . ', '
                . $db->quote((string) $row->rules) . ' '
                . 'FROM ' . $db->quoteName('#__assets') . ' p '
                . 'WHERE p.' . $db->quoteName('name') . ' = ' . $db->quote('com_proclaim') . ";\n";

            // Reconnect the source record's asset_id by name. Name format
            // is `com_proclaim.<type>.<id>`.
            $parts = explode('.', $row->name, 3);

            if (\count($parts) === 3) {
                $assetType = $parts[1];
                $recordId  = (int) $parts[2];
                $sourceTbl = $typeToTable[$assetType] ?? '';

                if ($sourceTbl !== '' && $recordId > 0) {
                    $export .= 'UPDATE ' . $db->quoteName($sourceTbl) . ' SET '
                        . $db->quoteName('asset_id') . ' = ('
                        . 'SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__assets')
                        . ' WHERE ' . $db->quoteName('name') . ' = ' . $db->quote($row->name) . ') '
                        . 'WHERE ' . $db->quoteName('id') . ' = ' . $recordId . ";\n";
                }
            }
        }

        $export .= "\n";

        return $export;
    }

    /**
     * Export Proclaim scheduled tasks from #__scheduler_tasks table.
     *
     * Returns SQL DELETE + INSERT statements to restore tasks.
     *
     * @return string SQL statements
     *
     * @throws \Exception
     * @since 10.1.0
     */
    public function getScheduledTasksExport(): string
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Always emit the section header so backup dumps stay self-documenting
        // even on systems that don't have #__scheduler_tasks yet.
        $export  = "\n-- --------------------------------------------------------\n";
        $export .= "-- Scheduled Tasks (proclaim)\n";
        $export .= "-- --------------------------------------------------------\n\n";

        $tables         = $db->getTableList();
        $prefix         = $db->getPrefix();
        $schedulerTable = $prefix . 'scheduler_tasks';

        if (!\in_array($schedulerTable, $tables, true)) {
            return $export . "-- Scheduler tasks table not found (Joomla 4+ required)\n\n";
        }

        // Get all Proclaim tasks
        $query = $db->createQuery();
        $query->select('*')
            ->from($db->quoteName('#__scheduler_tasks'))
            ->where($db->quoteName('type') . ' LIKE ' . $db->q('proclaim.%'));
        $db->setQuery($query);
        $results = $db->loadObjectList();

        if (empty($results)) {
            return $export . "-- No scheduled tasks found\n\n";
        }

        // DELETE existing Proclaim tasks (clean slate on restore)
        $export .= "DELETE FROM " . $db->quoteName('#__scheduler_tasks');
        $export .= " WHERE " . $db->quoteName('type') . " LIKE " . $db->q('proclaim.%') . ";\n\n";

        // INSERT each task
        foreach ($results as $task) {
            $data = [];
            $export .= 'INSERT INTO ' . $db->quoteName('#__scheduler_tasks') . ' SET ';

            foreach ($task as $key => $value) {
                // Skip auto-increment id (will be regenerated on restore)
                if ($key === 'id') {
                    continue;
                }

                if ($value === null) {
                    $data[] = $db->quoteName($key) . " = NULL";
                } else {
                    $data[] = $db->quoteName($key) . " = " . $db->q($value);
                }
            }

            $export .= implode(', ', $data) . ";\n";
        }

        $export .= "\n";

        return $export;
    }

    /**
     * Number of rows read and written per chunk in exportdb()'s per-table
     * loop. Keeps peak memory bounded to one chunk's worth of row objects
     * plus generated SQL text, regardless of table size.
     *
     * @var int
     *
     * @since 10.5.6
     */
    private const int EXPORT_CHUNK_SIZE = 500;

    /**
     * Export DB//
     *
     * @param   int  $run  ID
     *
     * @return bool
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function exportdb(int $run): bool
    {
        $this->saveAsName = self::generateBackupFilename();
        $objects          = CwmdbHelper::getObjects();
        $config           = Factory::getApplication()->getConfig();
        $path             = $config->get('tmp_path') . '/' . $this->saveAsName;

        $this->dumpFile = $run === 2
            ? JPATH_SITE . '/media/com_proclaim/backup/' . $this->saveAsName
            : $path;

        // Truncate/create the dump file before the first append below.
        if (file_put_contents($this->dumpFile, '') === false) {
            return false;
        }

        // Stream each table's DDL + rows in bounded chunks and flush to
        // disk immediately, rather than buffering the entire database dump
        // in memory before a single write.
        foreach ($objects as $object) {
            $table = $object['name'];

            if (!$this->writeln($this->getExportTableStructure($table))) {
                return false;
            }

            $rowCount = $this->getTableRowCount($table);

            for ($offset = 0; $offset < $rowCount; $offset += self::EXPORT_CHUNK_SIZE) {
                if (!$this->writeln($this->getExportTableRows($table, $offset, self::EXPORT_CHUNK_SIZE))) {
                    return false;
                }
            }

            if (!$this->writeln("\n-- --------------------------------------------------------\n\n")) {
                return false;
            }
        }

        // Append component configuration, scheduled tasks, and asset ACLs.
        if (
            !$this->writeln($this->getComponentConfigExport())
            || !$this->writeln($this->getScheduledTasksExport())
            || !$this->writeln($this->getProclaimAssetsExport())
        ) {
            return false;
        }

        switch ($run) {
            case 1:
                $mime_type = 'text/x-sql';

                if (Factory::getApplication()->getInput()->getInt('jbs_compress', 1)) {
                    $mime_type = 'application/zip';
                    $path      = $this->compress();
                }

                $this->outputFile($path, basename($path), $mime_type);

                break;
            case 2:
                if (Factory::getApplication()->getInput()->getInt('jbs_compress', 1)) {
                    $path = $this->compress();
                }

                Factory::getApplication()->enqueueMessage('Backup File Stored at: ' . $path, 'notice');

                break;
        }

        // Clean up files for only set amount. Files to keep (Default 5)
        $this->updatefiles(Cwmparams::getAdmin()->params);

        return true;
    }

    /**
     * Function to compress a backup file.
     *
     * @return string Zip File Path
     * @since 10.0.0
     */
    private function compress(): string
    {
        $files = (array)$this->dumpFile;
        $path1 = $this->dumpFile . '.zip';
        $zip   = new \ZipArchive();

        //create the file and throw the error if unsuccessful
        if ($zip->open($path1, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("cannot open <$path1>");
        }

        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();
        File::delete($this->dumpFile);

        return $path1;
    }

    /**
     * Get Export Table Data as string (for AJAX export)
     *
     * @param   string  $table  Table name (or virtual table name like '_component_config')
     *
     * @return string The SQL export data for the table
     *
     * @since 10.1.0
     */
    public function getExportTableData(string $table): string
    {
        if (!$table) {
            return '';
        }

        // Handle virtual "tables" for component config and scheduled tasks
        if ($table === '_component_config') {
            return $this->getComponentConfigExport();
        }

        if ($table === '_scheduled_tasks') {
            return $this->getScheduledTasksExport();
        }

        if ($table === '_proclaim_assets') {
            return $this->getProclaimAssetsExport();
        }

        if (!self::isKnownProclaimTable($table)) {
            return '';
        }

        // Delegate to the chunked pair (limit 0 = unbounded) so the DDL
        // and row-dump logic lives in exactly one place.
        return $this->getExportTableStructure($table)
            . $this->getExportTableRows($table, 0, 0)
            . "\n-- --------------------------------------------------------\n\n";
    }

    /**
     * Count the number of rows in a Proclaim table.
     *
     * @param   string  $table  Full table name (with prefix)
     *
     * @return  int  Row count
     *
     * @since   10.2.0
     */
    public function getTableRowCount(string $table): int
    {
        if (!self::isKnownProclaimTable($table)) {
            return 0;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName($table));
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Export table structure (DDL) only — DROP + CREATE statements.
     *
     * @param   string  $table  Full table name (with prefix)
     *
     * @return  string  SQL DDL statements
     *
     * @since   10.2.0
     */
    public function getExportTableStructure(string $table): string
    {
        if (!self::isKnownProclaimTable($table)) {
            return '';
        }

        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $prefix = $db->getPrefix();
        $export = '';

        $export .= "--\n-- Table structure for table " . $db->quoteName($table) . "\n--\n\n";
        $export .= 'DROP TABLE IF EXISTS ' . $db->quoteName($table) . ";\n";

        $query = 'SHOW CREATE TABLE ' . $db->quoteName($table);
        $db->setQuery($query);
        $table_def = $db->loadObject();

        foreach ($table_def as $value) {
            if (substr_count($value, 'CREATE')) {
                $export .= str_replace($prefix, '#__', $value) . ";\n";
                $export = str_replace('TYPE=', 'ENGINE=', $export);
            }
        }

        $export .= "\n\n--\n-- Dumping data for table " . $db->quoteName($table) . "\n--\n\n";

        return $export;
    }

    /**
     * Export a range of rows from a table as INSERT statements.
     *
     * @param   string  $table   Full table name (with prefix)
     * @param   int     $offset  Starting row offset
     * @param   int     $limit   Maximum rows to export
     *
     * @return  string  SQL INSERT statements for the row range
     *
     * @since   10.2.0
     */
    public function getExportTableRows(string $table, int $offset, int $limit): string
    {
        if (!self::isKnownProclaimTable($table)) {
            return '';
        }

        if (\function_exists('set_time_limit')) {
            set_time_limit(\ini_get('max_execution_time'));
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName($table));

        // A bounded LIMIT/OFFSET with no ORDER BY has no guaranteed row
        // order across separate queries -- paging in chunks (as exportdb()
        // now does) could then skip or duplicate rows between calls. Order
        // by the table's actual primary key (composite-key safe) so paging
        // is deterministic; tables with no primary key fall back to
        // unordered (matches the pre-chunking behavior for that edge case).
        $orderBy = self::getPrimaryKeyOrderClause($db, $table);

        if ($orderBy !== '') {
            $query->order($orderBy);
        }

        $db->setQuery($query, $offset, $limit);
        $results = $db->loadObjectList();

        $export = '';

        $generated = self::getGeneratedColumns($db, $table);

        if ($results) {
            foreach ($results as $result) {
                $data   = [];
                $export .= 'INSERT INTO ' . $db->quoteName($table) . ' SET ';

                foreach ($result as $key => $value) {
                    // The table computes these itself and rejects any attempt to
                    // write them, so naming one here would fail the restore.
                    if (isset($generated[$key])) {
                        continue;
                    }

                    if ($value === null) {
                        $data[] = $db->quoteName($key) . "=NULL";
                    } else {
                        $data[] = $db->quoteName($key) . "=" . $db->q(trim(str_replace(["\r\n", "\r"], "\n", $value)));
                    }
                }

                $export .= implode(',', $data) . ";\n";
            }
        }

        return $export;
    }

    /**
     * Saves the string in $fileData to the file.
     *
     * @param   string  $fileData  Data to write. Set to null to close the file handle.
     *
     * @return bool TRUE if saving to the file succeeded
     *
     * @throws \Exception
     * @since 9.0.0
     */
    protected function writeln(string $fileData): bool
    {
        // file_put_contents() returns 0 (falsy) for an empty string, which
        // would otherwise read as a write failure and abort the export.
        // Every chunk exportdb() currently produces is non-empty (each
        // export method emits a header/section comment unconditionally),
        // but that's true by accident of the string content, not by any
        // contract this method enforces -- guard it directly now that this
        // is called per-chunk instead of once for the whole dump.
        if ($fileData === '') {
            return true;
        }

        if (file_put_contents($this->dumpFile, $fileData, FILE_APPEND)) {
            return true;
        }

        return false;
    }

    /**
     * File output
     *
     * @param   string  $file       File Name
     * @param   string  $name       Name of File
     * @param   string  $mime_type  Meme_Type
     *
     * @return bool
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function outputFile(string $file, string $name, string $mime_type = ''): bool
    {
        if (!is_readable($file)) {
            throw new \RuntimeException('File not found or inaccessible!');
        }

        // Clears file status cache
        clearstatcache();

        // Turn off output buffering to decrease cpu usage
        @ob_end_clean();

        // Verify MimeType or Extract the MimeType
        $mime_type = Cwmmime::forDownload($mime_type, $file);

        // Reset the execution time limit for file output
        if (\function_exists('set_time_limit')) {
            set_time_limit(\ini_get('max_execution_time'));
        }

        // Decode URL-encoded strings
        $name = rawurldecode($name);

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        header("Accept-Ranges: bytes");

        // Set File Size Header
        $this->fileSizeHeader($file);

        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Transfer-Encoding: binary');

        ob_end_flush();
        $fp = fopen($file, 'rb');

        ob_start();
        $chunkSize = 1024 * 1024;

        if ($fp !== false) {
            while (!feof($fp)) {
                $buffer = fread($fp, $chunkSize);
                // Now will push to the browser the chunk of data using the buffer.
                echo $buffer;
                ob_flush();
                flush();
            }
            fclose($fp);
        } else {
            @readfile($file);
            ob_flush();
            flush();
        }
        return true;
    }

    /**
     * Build File Size Header
     *
     * @param $file string File with full Path
     *
     * @since 10.0.0
     */
    private function fileSizeHeader(string $file): void
    {
        // Get File Size. filesize() is safe from the historical 32-bit
        // signed-integer overflow under PHP 8.3+ (64-bit builds only).
        $size = filesize($file);

        // HTTP Range - see RFC2616 for more information's (http://www.ietf.org/rfc/rfc2616.txt)
        $newFileSize = $size - 1;

        // Default values! Will be overridden if a valid range header field was detected!
        $resultLength = (string)$size;
        $resultRange  = "0-" . $newFileSize;

        $httpRangeHeader = Factory::getApplication()->getInput()->server->get('HTTP_RANGE', '', 'raw');

        /* We support requests for a single range only.
                 * So we check if we have a range field.
                 * If yes, ensure that it is valid.
                 * If it is not valid, we ignore it and send the whole file.
                 * */
        if ($httpRangeHeader !== '' && preg_match('%^bytes=\d*\-\d*$%', $httpRangeHeader)) {
            // Let's take the right side
            [, $httpRange] = explode('=', $httpRangeHeader);

            // And get the two values (as strings!)
            $httpRange = explode('-', $httpRange);

            // Check if we have values! If not, we have nothing to do!
            if (!empty($httpRange[0]) || !empty($httpRange[1])) {
                // We need the new content length ...
                $resultLength = $size - $httpRange[0] - $httpRange[1];

                // ... and we can add the 206 Status.
                header("HTTP/1.1 206 Partial Content");

                // Now we need the content-range, so we have to build it depending on the given range!
                // ex.: -500 -> the last 500 bytes
                if (empty($httpRange[0])) {
                    $resultRange = $resultLength . '-' . $size;
                } elseif (empty($httpRange[1])) {
                    // Ex.: 500- -> from 500 bytes to file size
                    $resultRange = $httpRange[0] . '-' . $size;
                } else {
                    // Ex.: 500-1000 -> from 500 to 1000 bytes
                    $resultRange = $httpRange[0] . '-' . $httpRange[1];
                }
            }
        }

        header('Content-Length: ' . $resultLength);
        header('Content-Range: bytes ' . $resultRange . '/' . $size);
    }

    /**
     * Update files
     *
     * @param  ?Registry  $params  Proclaim Params
     *
     * @return void
     *
     * @since 7.1.0
     */
    public function updatefiles(?Registry $params): void
    {
        $path = JPATH_SITE . '/media/com_proclaim/backup';

        if (!is_dir($path)) {
            Folder::create($path);
        }

        $exclude       = ['.git', '.svn', 'CVS', '.DS_Store', '__MACOSX', '.html'];
        $excludefilter = ['^\..*', '.*~'];
        $files         = Folder::files($path, '.', 'false', 'true', $exclude, $excludefilter);
        arsort($files, SORT_STRING);
        $parts       = [];
        $numfiles    = \count($files);
        $totalnumber = $params?->get('filestokeep', 5) ?? 5;

        for ($counter = $numfiles; $counter > $totalnumber; $counter--) {
            $parts[] = array_pop($files);
        }

        foreach ($parts as $part) {
            File::delete($part);
        }
    }
}
