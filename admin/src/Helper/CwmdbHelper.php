<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmadminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;

/**
 * Database Helper class for version 7.1.0
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class CwmdbHelper
{
    /**
     * Extension Name
     *
     * @var string
     *
     * @since 1.5
     */
    public static string $extension = 'com_proclaim';

    /**
     * Install State
     *
     * @var bool
     *
     * @since 1.5
     */
    public static bool $install_state = false;

    /**
     * System to Check if Table Exists
     *
     * @param   string  $cktable  Table to check for exp:"#__bsms_admin
     *
     * @return bool  If table is there True else False if not.
     *
     * @since 7.0
     */
    public static function checkIfTable($cktable): bool
    {
        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();

        foreach ($tables as $table) {
            $tableAF = str_replace($prefix, "#__", $table);

            if ($tableAF == $cktable) {
                return true;
            }
        }

        return false;
    }

    /**
     * Alters a table
     * command is only needed for MODIFY. Can be used to ADD, DROP, MODIFY, or CHANGE tables.
     *
     * @param   array    $tables  Tables is an array of tables, fields, type of query and optional command line
     * @param   ?string  $from    Where the query is coming from for msg
     *
     * @return bool
     *
     * @throws  \Exception
     * @since   7.0
     */
    public static function alterDB(array $tables, ?string $from = null): bool
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        foreach ($tables as $t) {
            $type    = strtolower($t['type']);
            $command = $t['command'];
            $table   = $t['table'];
            $field   = $t['field'];

            switch ($type) {
                case 'drop':
                    if (!$table || !$field) {
                        break;
                    }

                    // Check the field to see if it exists first
                    if (self::checkTables($table, $field) === true) {
                        $query = 'ALTER TABLE ' . $db->quoteName($table) . ' DROP ' . $db->quoteName($field);

                        if (!self::performDB($query, $from)) {
                            return false;
                        }
                    }
                    break;

                case 'index':
                    if (!$table || !$field) {
                        break;
                    }

                    $query = 'ALTER TABLE ' . $db->quoteName($table) . ' ADD INDEX ' . $db->quoteName($field) . ' ' . $command;

                    if (!self::performDB($query, $from)) {
                        return false;
                    }

                    break;

                case 'add':
                    if (!$table || !$field) {
                        break;
                    }

                    if (self::checkTables($table, $field) !== true) {
                        $query = 'ALTER TABLE ' . $db->quoteName($table) . ' ADD ' . $db->quoteName($field) . ' ' . $command;

                        if (!self::performDB($query, $from)) {
                            return false;
                        }
                    }
                    break;

                case 'column':
                    if (!$table || !$field) {
                        break;
                    }

                    if (self::checkTables($table, $field) !== true) {
                        $query = 'ALTER TABLE ' . $db->quoteName($table) . ' ADD COLUMN' . $db->quoteName($field) . ' ' . $command;

                        if (!self::performDB($query, $from)) {
                            return false;
                        }
                    }
                    break;

                case 'modify':
                    if (!$table || !$field) {
                        break;
                    }

                    if (self::checkTables($table, $field) === true) {
                        $query = 'ALTER TABLE ' . $db->quoteName($table) . ' MODIFY ' . $db->quoteName($field) . ' ' . $command;

                        if (!self::performDB($query, $from)) {
                            return false;
                        }
                    }
                    break;

                case 'change':
                    if (!$table || !$field) {
                        break;
                    }

                    if (self::checkTables($table, $field) === true) {
                        $query = 'ALTER TABLE ' . $db->quoteName($table) . ' CHANGE ' . $db->quoteName($field) . ' ' . $command;

                        if (!self::performDB($query, $from)) {
                            return false;
                        }
                    }
            }
        }

        return true;
    }

    /**
     * Discover the fields in a table
     *
     * @param   string  $table  Is the table you are checking
     * @param   string  $field  Checking against.
     *
     * @return bool false equals field does not exist
     *
     * @since 7.0
     */
    public static function checkTables($table, $field): bool
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $fields = $db->getTableColumns($table, 'false');

        if ($fields) {
            if (\array_key_exists($field, $fields) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * performs a database query
     *
     * @param   string   $query  Is a Joomla ready query
     * @param   ?string  $from   Where the source of the query comes from
     * @param   ?int     $limit  Set the Limit of the query
     *
     * @return bool true if the query ran, false if it failed
     *
     * @throws  \Exception
     * @since   7.0
     */
    public static function performDB($query, ?string $from = null, ?int $limit = null): bool
    {
        if (!$query) {
            return false;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // DatabaseDriver::execute() returns true or throws -- it never returns
        // false -- so a `if (!$db->execute())` check is unreachable and every
        // real SQL failure escapes as an uncaught exception instead of the false
        // this method's contract promises. CwmadminController::copyTables()
        // calls this three times per table with no try/catch and relies on that
        // false return to abort cleanly.
        //
        // setQuery() is inside the try on purpose: the MySQLi driver prepares
        // the statement eagerly, so a bad table name or syntax error -- the
        // most common failure here -- throws from setQuery(), before execute()
        // is ever reached. Guarding execute() alone would still let those
        // escape.
        try {
            $db->setQuery($query, 0, $limit);
            $db->execute();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage(
                $from . Text::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $e->getMessage()),
                'warning'
            );
            Log::add($from . 'FAILED: ' . $query . ' -- ' . $e->getMessage(), Log::ERROR, 'com_proclaim');

            return false;
        }

        Log::add($from . $query, Log::INFO, 'com_proclaim');

        return true;
    }

    /**
     * Checks a table for the existence of a field, if it does not find it, runs the Admin model fix()
     *
     * @param   string  $table  table is the table you are checking
     * @param   string  $field  field you are checking
     *
     * @return bool
     *
     * @throws \Exception
     * @since 7.0
     */
    public static function checkDB($table, $field): bool
    {
        $done = self::checkTables($table, $field);

        if (!$done) {
            /** @var CwmadminModel $admin */
            $admin = Factory::getApplication()
                ->bootComponent('com_proclaim')
                ->getMVCFactory()
                ->createModel('Cwmadmin', 'Administrator');
            $admin->fix();

            return true;
        }

        return true;
    }

    /**
     * Whether a table exists in this site's database.
     *
     * ⚠️ Not `SHOW TABLES LIKE`. In a LIKE pattern `_` matches any single
     * character, and every Joomla prefix ends in one -- so
     * `SHOW TABLES LIKE 'jos_bsms_admin'` also matches a table literally named
     * `josXbsmsYadmin`. The query reads as an equality test and is not one.
     *
     * A collision needs a table of identical length differing only at the
     * underscore positions, which is why nothing has gone wrong in practice:
     * measured across a real 104-table schema, zero pairs collide. It is still
     * the wrong question to ask, and asking the right one costs nothing.
     *
     * information_schema keeps the property the old query was chosen for --
     * it does not enumerate every table the way getTableList() does.
     *
     * @param   string  $table  Table name, with either the `#__` placeholder or
     *                          the real prefix.
     *
     * @return  bool
     *
     * @since __DEPLOY_VERSION__
     */
    public static function tableExists(string $table): bool
    {
        $db   = Factory::getContainer()->get(DatabaseInterface::class);
        $real = str_replace('#__', $db->getPrefix(), $table);

        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('information_schema.TABLES'))
            ->where($db->quoteName('TABLE_SCHEMA') . ' = DATABASE()')
            ->where($db->quoteName('TABLE_NAME') . ' = :name')
            ->bind(':name', $real);

        $db->setQuery($query);

        return (int) $db->loadResult() > 0;
    }

    /**
     * Whether a column exists on a table.
     *
     * The same wildcard problem as {@see tableExists()}: `SHOW COLUMNS FROM x
     * LIKE 'teacher_id'` also matches a column named `teacherXid`, because `_`
     * is a single-character wildcard. Less likely to collide than a table name
     * carrying a prefix underscore, and the same wrong question.
     *
     * @param   string  $table   Table name, `#__` placeholder or real prefix.
     * @param   string  $column  Column name, matched exactly.
     *
     * @return  bool
     *
     * @since __DEPLOY_VERSION__
     */
    public static function columnExists(string $table, string $column): bool
    {
        $db   = Factory::getContainer()->get(DatabaseInterface::class);
        $real = str_replace('#__', $db->getPrefix(), $table);

        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('information_schema.COLUMNS'))
            ->where($db->quoteName('TABLE_SCHEMA') . ' = DATABASE()')
            ->where($db->quoteName('TABLE_NAME') . ' = :table')
            ->where($db->quoteName('COLUMN_NAME') . ' = :column')
            ->bind(':table', $real)
            ->bind(':column', $column);

        $db->setQuery($query);

        return (int) $db->loadResult() > 0;
    }

    /**
     * The scripture stack: tables that carry the `bsms_` prefix but belong to
     * lib_cwmscripture, not to Proclaim.
     *
     * They are shared. Any consumer of the library -- Proclaim, CWMLivingWord,
     * a third party -- reads and writes them, and the library alone decides
     * when they are created or dropped. The same ownership question was
     * settled for uninstall: Proclaim leaves the stack alone there too.
     *
     * `#__bsms_scripture_consumers` is the sharpest case. It is derived state
     * describing which extensions are installed on *this* site right now, and
     * every uninstall guard consults it before dropping anything. A copy of it
     * taken at another moment is not a backup, it is a wrong answer waiting to
     * be believed.
     *
     * @return  string[]  Table names with the `#__` prefix.
     *
     * @since __DEPLOY_VERSION__
     */
    public static function getScriptureTables(): array
    {
        return [
            '#__bsms_bible_translations',
            '#__bsms_bible_verses',
            '#__bsms_scripture_cache',
            '#__bsms_scripture_consumers',
        ];
    }

    /**
     * getObjects() minus the shared scripture stack.
     *
     * What Proclaim may back up, restore and drop as its own. getObjects() is
     * prefix-driven, so it cannot tell the difference by itself: all four
     * scripture tables are named `bsms_*` despite belonging to the library.
     *
     * Kept separate from getObjects() rather than filtered at source. The
     * migration and upgrade helpers also call getObjects(), and narrowing what
     * they see is a different decision from narrowing what a backup carries.
     *
     * @return  array<int, array{name: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    public static function getOwnObjects(): array
    {
        $shared = self::getScriptureTables();

        return array_values(
            array_filter(
                self::getObjects(),
                static fn (array $object): bool => !\in_array($object['name'], $shared, true)
            )
        );
    }

    /**
     * Get Objects for tables
     *
     * @return array
     *
     * @since 7.0
     */
    public static function getObjects(): array
    {
        $db        = Factory::getContainer()->get(DatabaseInterface::class);
        $tables    = $db->getTableList();
        $prefix    = $db->getPrefix();
        $prelength = \strlen($prefix);
        $bsms      = 'bsms_';
        $objects   = [];

        foreach ($tables as $table) {
            // Anchored, not a substring test. getTableList() returns every
            // table in the schema, which on shared-database hosting includes
            // other Joomla installs' tables. An unanchored
            // str_contains($table, $prefix) matched a sibling table that merely
            // contained this prefix somewhere -- e.g. with prefix 'jos_', the
            // table 'myjos_bsms_studies' matched, and substr_replace() then
            // stripped a fixed prefix-length from the front regardless,
            // yielding the mangled '#__s_bsms_studies'. That bogus name was
            // handed to every consumer of this list: the backup/restore and
            // migration loops, and Cwmbackup's export allow-list.
            if (str_starts_with($table, $prefix . $bsms)) {
                $table = substr_replace($table, '#__', 0, $prelength);

                // Skip legacy version tracking table (replaced by #__schemas)
                if ($table === '#__bsms_update') {
                    continue;
                }

                $objects[] = ['name' => $table];
            }
        }

        return $objects;
    }

    /**
     * Get State of install for Main Admin Controller
     *
     * @return  bool false if table exists | true if dos not
     *
     * @since 7.1.0
     */
    public static function getInstallState(): bool
    {
        if (!\is_bool(self::$install_state)) {
            // Comparing the returned name (rather than testing truthiness)
            // made this immune to the LIKE wildcard by accident. tableExists()
            // makes that deliberate, and says so.
            if (!self::tableExists('#__bsms_admin')) {
                self::$install_state = true;
            }
        }

        return self::$install_state;
    }

    /**
     * Fix up css.
     *
     * @param   string    $filename  Name of css file
     * @param   bool      $parent    if coming form the update script
     * @param   string    $newcss    New css style
     * @param   ?int      $id        this is the id of record to be fixed
     *
     * @return bool
     *
     * @throws  \Exception
     * @since   7.1.0
     */
    public static function fixupcss(string $filename, bool $parent, string $newcss, ?int $id = null): bool
    {
        $app = Factory::getApplication();

        // Start by getting existing Style
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery();
        $query->select('*')->from($db->quoteName('#__bsms_styles'));

        if ($filename) {
            $query->where($db->quoteName('filename') . ' = ' . $db->q($filename));
        } else {
            $query->where($db->quoteName('id') . ' = ' . (int)$id);
        }

        $db->setQuery($query);
        $result = $db->loadObject();
        $oldcss = (string)$result->stylecode;

        // Now the arrays of changes that need to be done.

        $oldlines = [
            ".bsm_teachertable_list",
            "#bslisttable",
            "#bslisttable",
            "#landing_table",
            "#landing_separator",
            "#landing_item",
            "#landing_title",
            "#landinglist",
        ];
        $newlines = [
            "#bsm_teachertable_list",
            ".bslisttable",
            ".bslisttable",
            ".landing_table",
            ".landing_separator",
            ".landing_item",
            ".landing_title",
            ".landinglist",
        ];
        $oldcss   = (string)str_replace($oldlines, $newlines, $oldcss);

        // Now see if we are adding new css to the db css

        if ($parent || $newcss) {
            $newcss = $db->escape($newcss) . ' ' . $oldcss;
        } else {
            $newcss = (string)$oldcss;
        }

        // No apply the new css back to the table

        $query = $db->createQuery();
        $query->update($db->quoteName('#__bsms_styles'))->set($db->quoteName('stylecode') . ' = ' . $db->q($newcss));

        if ($filename) {
            $query->where($db->quoteName('filename') . ' = ' . $db->q($filename));
        } else {
            $query->where($db->quoteName('id') . ' = ' . (int)$id);
        }

        $db->setQuery($query);

        if (!$db->execute()) {
            $app->enqueueMessage(Text::sprintf('JBS_INS_SQL_UPDATE_ERRORS', ''), 'error');

            return false;
        }

        // If we are not coming from the upgrade scripts we update the table and let them know what was updated.

        if (!$parent) {
            self::reloadtable($result, 'Style');
            $app->enqueueMessage(Text::_('JBS_STYLE_CSS_FIX_COMPLETE') . ': ' . $result->filename, 'notice');
        }

        return true;
    }

    /**
     * Set table store()
     *
     * @param   object  $result  Object list that we will get the id from.
     * @param   string  $table   Table to be reloaded.
     *
     * @return bool
     *
     * @throws \Exception
     *
     * @since 7.0
     */
    public static function reloadtable(object $result, string $table = 'Style'): bool
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Store new Recorder so it can be seen.
        $table = Factory::getApplication()
            ->bootComponent('com_proclaim')
            ->getMVCFactory()
            ->createTable($table, 'Administrator', ['dbo' => Factory::getContainer()->get(DatabaseInterface::class)]);

        try {
            $table->load($result->id);

            // This is a Joomla bug for currentAssetId being missing in table.php. When fixed in Joomla should be removed
            @$table->store();
        } catch (\Exception $e) {
            throw new \RuntimeException('Caught exception: ' . $e->getMessage(), 500);
        }

        return true;
    }

    /**
     * Reset Database back to defaults
     *
     * @param   bool  $install  If coming from the installer true|false not form installer
     *
     * @return bool|int
     *
     * @throws \Exception
     * @since  7.0
     */
    public static function resetdb(bool $install = false): bool|int
    {
        $app  = Factory::getApplication();
        $db   = Factory::getContainer()->get(DatabaseInterface::class);
        $path = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components/com_proclaim/sql';

        $files = str_replace('.sql', '', Folder::files($path, '\.sql$'));
        $files = array_reverse($files, true);

        if ($install === true) {
            foreach ($files as $a => $file) {
                if (str_contains($file, 'uninstall')) {
                    unset($files[$a]);
                }
            }
        }

        foreach ($files as $value) {
            // Get file contents
            $buffer = file_get_contents($path . '/' . $value . '.sql');

            // Graceful exit and rollback if read not successful
            if ($buffer === false) {
                $app->enqueueMessage(Text::_('JBS_INS_ERROR_SQL_READBUFFER'), 'error');

                return false;
            }

            // Create an array of queries from the sql file
            $queries = $db->splitSql($buffer);

            if (\count($queries) === 0) {
                // No queries to process
                return 0;
            }

            // Process each query in the $queries array (split out of sql file).
            foreach ($queries as $query) {
                $query = trim($query);

                if ($query !== '' && $query[0] !== '#') {
                    // execute() throws rather than returning false, so the
                    // documented false return has to be produced here. The
                    // caller that depends on it, CwminstallModel::realRun(),
                    // uses resetdb() as its migration-rollback recovery step
                    // and has no try/catch: an escaping exception skips
                    // resetStack() and leaves a dirty migration stack plus a
                    // fatal instead of the intended "not migrated" warning --
                    // in exactly the case this recovery path exists for, a
                    // half-created table erroring on re-create.
                    //
                    // setQuery() is inside the try because the MySQLi driver
                    // prepares eagerly and throws there first.
                    try {
                        $db->setQuery($query);
                        $db->execute();
                    } catch (\RuntimeException $e) {
                        $app->enqueueMessage(
                            Text::sprintf('JBS_INS_SQL_UPDATE_ERRORS', ' in ' . $value . ': ' . $e->getMessage()),
                            'error'
                        );
                        Log::add(
                            'resetdb() failed in ' . $value . ': ' . $e->getMessage(),
                            Log::ERROR,
                            'com_proclaim'
                        );

                        return false;
                    }
                }
            }
        }

        // Remove old assets.
        $query = $db->createQuery();
        $query->delete($db->quoteName('#__assets'))
            ->where($db->quoteName('name') . ' LIKE ' . $db->q('com_proclaim.%'));
        $db->setQuery($query);
        $db->execute();

        if (!$install) {
            $app->enqueueMessage(Text::_('JBS_INS_RESETDB'), 'message');
        }

        return true;
    }

    /**
     * Clean up Study Topics Duplicates
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   8.0.0
     *
     */
    public static function cleanStudyTopics(): void
    {
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery();
        $query->select($db->quoteName('id'))->from($db->quoteName('#__bsms_studies'));
        $db->setQuery($query);
        $results = $db->loadObjectList();

        foreach ($results as $result) {
            $query = $db->createQuery();
            $query->select($db->quoteName(['id', 'topic_id']))
                ->from($db->quoteName('#__bsms_studytopics'))
                ->where($db->quoteName('study_id') . ' = ' . (int) $result->id);
            $db->setQuery($query);
            $resulta = $db->loadObjectList();
            $c       = \count($resulta);

            if ($resulta && $c > 1) {
                $t = 1;

                foreach ($resulta as $study_topics) {
                    $query = $db->createQuery();
                    $query->select($db->quoteName('id'))
                        ->from($db->quoteName('#__bsms_studytopics'))
                        ->where($db->quoteName('study_id') . ' = ' . (int) $result->id)
                        ->where($db->quoteName('topic_id') . ' = ' . (int) $study_topics->topic_id)
                        ->order($db->quoteName('id') . ' DESC');
                    $db->setQuery($query);
                    $results = $db->loadObjectList();
                    $records = \count($results);

                    if ($records > 1) {
                        foreach ($results as $id) {
                            if ($t < $records) {
                                $query = $db->createQuery();
                                $query->delete($db->quoteName('#__bsms_studytopics'))
                                    ->where($db->quoteName('id') . ' = ' . (int) $id->id);
                                $db->setQuery($query);

                                if (!$db->execute()) {
                                    $app->enqueueMessage(
                                        'Error with Deleting duplicat topics record ' . $id->id,
                                        'error'
                                    );
                                } else {
                                    $app->enqueueMessage('Removed Duplicat topic Record ' . $id->id, 'notice');
                                }

                                $t++;
                            }
                        }
                    }
                }
            }
        }
    }
}
