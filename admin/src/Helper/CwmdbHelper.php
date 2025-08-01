<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmadminModel;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

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
        $db     = Factory::getContainer()->get('DatabaseDriver');
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
     * @throws  Exception
     * @since   7.0
     */
    public static function alterDB(array $tables, ?string $from = null): bool
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

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
                        $query = 'ALTER TABLE ' . $db->qn($table) . ' DROP ' . $db->qn($field);

                        if (!self::performDB($query, $from)) {
                            return false;
                        }
                    }
                    break;

                case 'index':
                    if (!$table || !$field) {
                        break;
                    }

                    $query = 'ALTER TABLE ' . $db->qn($table) . ' ADD INDEX ' . $db->qn($field) . ' ' . $command;

                    if (!self::performDB($query, $from)) {
                        return false;
                    }

                    break;

                case 'add':
                    if (!$table || !$field) {
                        break;
                    }

                    if (self::checkTables($table, $field) !== true) {
                        $query = 'ALTER TABLE ' . $db->qn($table) . ' ADD ' . $db->qn($field) . ' ' . $command;

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
                        $query = 'ALTER TABLE ' . $db->qn($table) . ' ADD COLUMN' . $db->qn($field) . ' ' . $command;

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
                        $query = 'ALTER TABLE ' . $db->qn($table) . ' MODIFY ' . $db->qn($field) . ' ' . $command;

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
                        $query = 'ALTER TABLE ' . $db->qn($table) . ' CHANGE ' . $db->qn($field) . ' ' . $command;

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
        $db = Factory::getContainer()->get('DatabaseDriver');

        $fields = $db->getTableColumns($table, 'false');

        if ($fields) {
            if (array_key_exists($field, $fields) === true) {
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
     * @return bool true if success, or error string if failed
     *
     * @throws  Exception
     * @since   7.0
     */
    public static function performDB($query, ?string $from = null, ?int $limit = null): bool
    {
        if (!$query) {
            return false;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery($query, 0, $limit);

        if (!$db->execute()) {
            Factory::getApplication()->enqueueMessage(
                $from . Text::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)),
                'warning'
            );

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
     * @return boolean
     *
     * @throws Exception
     * @since 7.0
     */
    public static function checkDB($table, $field): bool
    {
        $done = self::checkTables($table, $field);

        if (!$done) {
            /** @var CwmadminModel $admin */
            $admin = BaseDatabaseModel::getInstance('Cwmadmin', 'Model');
            $admin->fix();

            return true;
        }

        return true;
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
        $db        = Factory::getContainer()->get('DatabaseDriver');
        $tables    = $db->getTableList();
        $prefix    = $db->getPrefix();
        $prelength = strlen($prefix);
        $bsms      = 'bsms_';
        $objects   = array();

        foreach ($tables as $table) {
            if (str_contains($table, $prefix) && str_contains($table, $bsms)) {
                $table     = substr_replace($table, '#__', 0, $prelength);
                $objects[] = array('name' => $table);
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
        if (!is_bool(self::$install_state)) {
            $db = Factory::getContainer()->get('DatabaseDriver');

            // Check if JBSM can be found from the database
            $table = $db->getPrefix() . 'bsms_admin';
            $db->setQuery("SHOW TABLES LIKE {$db->quote($table)}");

            if ($db->loadResult() !== $table) {
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
     * @throws  Exception
     * @since   7.1.0
     */
    public static function fixupcss(string $filename, bool $parent, string $newcss, ?int $id = null)
    {
        $app = Factory::getApplication();

        // Start by getting existing Style
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*')->from('#__bsms_styles');

        if ($filename) {
            $query->where($db->qn('filename') . ' = ' . $db->q($filename));
        } else {
            $query->where($db->qn('id') . ' = ' . (int)$id);
        }

        $db->setQuery($query);
        $result = $db->loadObject();
        $oldcss = (string)$result->stylecode;

        // Now the arrays of changes that need to be done.

        $oldlines = array(
            ".bsm_teachertable_list",
            "#bslisttable",
            "#bslisttable",
            "#landing_table",
            "#landing_separator",
            "#landing_item",
            "#landing_title",
            "#landinglist"
        );
        $newlines = array(
            "#bsm_teachertable_list",
            ".bslisttable",
            ".bslisttable",
            ".landing_table",
            ".landing_separator",
            ".landing_item",
            ".landing_title",
            ".landinglist"
        );
        $oldcss   = (string)str_replace($oldlines, $newlines, $oldcss);

        // Now see if we are adding new css to the db css

        if ($parent || $newcss) {
            $newcss = $db->escape($newcss) . ' ' . $oldcss;
        } else {
            $newcss = (string)$oldcss;
        }

        // No apply the new css back to the table

        $query = $db->getQuery(true);
        $query->update('#__bsms_styles')->set('stylecode="' . $newcss . '"');

        if ($filename) {
            $query->where($db->qn('filename') . ' = ' . $db->q($filename));
        } else {
            $query->where($db->qn('id') . ' = ' . (int)$id);
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
     * @throws Exception
     *
     * @since 7.0
     */
    public static function reloadtable(object $result, string $table = 'Style')
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Store new Recorder so it can be seen.
        $table = Factory::getApplication()
            ->bootComponent('com_proclaim')
            ->getMVCFactory()
            ->createTable($table, 'Administrator', ['dbo' => Factory::getContainer()->get(DatabaseInterface::class)]);

        try {
            $table->load($result->id);

            // This is a Joomla bug for currentAssetId being missing in table.php. When fixed in Joomla should be removed
            @$table->store();
        } catch (Exception $e) {
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
     * @throws Exception
     * @since  7.0
     */
    public static function resetdb($install = false): bool|int
    {
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        $path = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components/com_proclaim/sql';

        $files = str_replace('.sql', '', Folder::files($path, '\.sql$'));
        $files = array_reverse($files, true);

        if ($install === true) {
            foreach ($files as $a => $file) {
                if (strpos($file, 'uninstall') !== false) {
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

            if (count($queries) === 0) {
                // No queries to process
                return 0;
            }

            // Process each query in the $queries array (split out of sql file).
            foreach ($queries as $query) {
                $query = trim($query);

                if ($query !== '' && $query[0] !== '#') {
                    $db->setQuery($query);

                    if (!$db->execute()) {
                        $app->enqueueMessage(Text::sprintf('JBS_INS_SQL_UPDATE_ERRORS', ' in ' . $value), 'error');

                        return false;
                    }
                }
            }
        }

        // Remove old assets.
        $query = $db->getQuery(true);
        $query->delete('#__assets')
            ->where('name LIKE ' . $db->q('com_proclaim.%'));
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
     * @throws  Exception
     * @since   8.0.0
     *
     */
    public static function cleanStudyTopics(): void
    {
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('id')->from('#__bsms_studies');
        $db->setQuery($query);
        $results = $db->loadObjectList();

        foreach ($results as $result) {
            $query = $db->getQuery(true);
            $query->select('id, topic_id')->from('#__bsms_studytopics')->where('study_id = ' . $result->id);
            $db->setQuery($query);
            $resulta = $db->loadObjectList();
            $c       = count($resulta);

            if ($resulta && $c > 1) {
                $t = 1;

                foreach ($resulta as $study_topics) {
                    $query = $db->getQuery(true);
                    $query->select('id')
                        ->from('#__bsms_studytopics')
                        ->where('study_id = ' . $result->id)
                        ->where('topic_id = ' . $study_topics->topic_id)
                        ->order('id desc');
                    $db->setQuery($query);
                    $results = $db->loadObjectList();
                    $records = count($results);

                    if ($records > 1) {
                        foreach ($results as $id) {
                            if ($t < $records) {
                                $query = $db->getQuery(true);
                                $query->delete('#__bsms_studytopics')
                                    ->where('id = ' . $id->id);
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
