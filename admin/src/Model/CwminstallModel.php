<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;

/**
 * class Migration model
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class CwminstallModel extends ListModel
{
    /** @var int Total numbers of Versions
     *
     * @since 7.1
     */
    public $totalSteps = 0;

    /** @var int Numbers of Versions already processed
     *
     * @since 7.1
     */
    public $doneSteps = 0;

    /**
     * @var string
     * @since 7.0
     */
    public string $step = '';

    /** @var string Running Now
     *
     * @since 7.1
     */
    public $running = null;

    /** @var array Call stack for the Visioning System.
     *
     * @since 7.1
     */
    public array $callstack = [];
    /** @var array Array of assets to fix
     *
     * @since 7.1
     */
    public array $installQuery = [];

    /** @var string Path to Mysql files
     *
     * @since 7.1
     */
    protected string $filePath = '/components/com_proclaim/sql/updates/mysql/';
    /** @var string Path to PHP Version files
     *
     * @since 7.1
     */
    protected string $phpPath = '/components/com_proclaim/install/updates/';
    /** @var float The time the process started
     *
     * @since 7.1
     */
    private float $startTime;
    /** @var array The pre-versions to process
     *
     * @since 7.1
     */
    private array $versionStack = [];
    /** @var array The pre-versions sub sql array to process
     *
     * @since 7.1
     */
    private array $allupdates = [];
    /** @var string Version of Proclaim
     *
     * @since 7.1
     */
    private string $versionSwitch = '';
    /** @var int ID of Extinction Table
     *
     * @since 7.1
     */
    private int $biblestudyEid = 0;
    /** @var array Array of Finish Task
     *
     * @since 7.1
     */
    private array $finish = [];
    /** @var string Version number to be running
     *
     * @since 7.1
     */
    private string $version = "0.0.0";
    /** @var array PHP file steps for migrations
     *
     * @since 7.1
     */
    private array $subSteps = [];
    /** @var array Array of Sub Query from php files queries Task
     *
     * @since 7.1
     */
    private array $subQuery = [];
    /** @var array list of php files to work through
     *
     * @since 7.1
     */
    private array $subFiles = [];
    /** @var array Array of Install Task
     *
     * @since 9.0.14
     */
    private array $start = [];
    /** @var int If was imported
     *
     * @since 7.1
     */
    private int $isimport = 0;

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @throws  Exception
     * @since   7.1
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        // Joomla 4 drop including db driver.
        $this->_db = Factory::getContainer()->get(DatabaseInterface::class);

        $this->name = 'cwminstall';
    }

    /**
     * Start Looking through the Versions
     *
     * @return bool
     *
     * @throws  Exception
     * @since   7.1
     */
    public function startScanning(): bool
    {
        $this->resetStack();
        $this->resetTimer();
        $this->getSteps();
        $this->postinstallclenup();

        if (empty($this->versionStack)) {
            $this->versionStack = [];
        }

        asort($this->versionStack);

        $this->saveStack();

        if (!$this->haveEnoughTime()) {
            return true;
        }

        return $this->run(false);
    }

    /**
     * Resets the Versions/SQL/After stack saved in the session
     *
     * @return void
     *
     * @throws Exception
     * @since 7.1
     */
    private function resetStack(): void
    {
        $session = Factory::getApplication()->getSession();
        $session->set('migration_stack', '', 'CWM');
        $this->version       = '0.0.0';
        $this->versionStack  = [];
        $this->versionSwitch = '';
        $this->allupdates    = [];
        $this->finish        = [];
        $this->start         = [];
        $this->subFiles      = [];
        $this->subQuery      = [];
        $this->subSteps      = [];
        $this->isimport      = 0;
        $this->callstack     = [];
        $this->totalSteps    = 0;
        $this->doneSteps     = 0;
        $this->running       = Text::_('JBS_MIG_STARTING');
        $this->installQuery  = [];
    }

    /**
     * Starts or resets the internal timer
     *
     * @return void
     *
     * @since 7.1
     */
    private function resetTimer(): void
    {
        $this->startTime = $this->microtimeFloat();
    }

    /**
     * Returns the current timestamps in decimal seconds
     *
     * @return float
     *
     * @since 7.1
     */
    private function microtimeFloat(): float
    {
        [$usec, $sec] = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }

    /**
     * Get to migrate versions of DB after import/copy has finished.
     *
     * @return void
     *
     * @throws  Exception
     * @since   7.1
     */
    private function getSteps(): void
    {
        $app = Factory::getApplication();

        // Set Finishing Steps
        $this->finish     = array(
            'updateversion',
            'fixassets',
            'fixmenus',
            'fixemptyaccess',
            'fixemptylanguage',
            'rmoldurl',
            'setupdateurl',
            'finish'
        );
        $this->totalSteps += count($this->finish);

        /**
         * First, we check to see if there is a current version database installed. This will have a #__bsms_version
         * table, so we check for its existence.
         * Check to be sure a really early version is not installed $versiontype: 1 = current version type 2 = older version type 3 = no version
         */
        $this->callstack['versionttype'] = 1;

        // Find the Last updated Version in Update table
        $query = $this->_db->getQuery(true);
        $query->select('version')
            ->from('#__bsms_update')
            ->order($this->_db->qn('id') . ' DESC');
        $this->_db->setQuery($query, 0, 1);
        $updates             = $this->_db->loadObject();
        $version             = $updates->version;
        $this->versionSwitch = $version;

        $this->callstack['subversiontype_version'] = $version;

        if ($this->callstack['subversiontype_version'] > 000) {
            $files = str_replace('.sql', '', Folder::files(JPATH_ADMINISTRATOR . $this->filePath, '\.sql$'));

            $php = null;

            if (is_dir(JPATH_ADMINISTRATOR . $this->phpPath)) {
                $php = str_replace('.php', '', Folder::files(JPATH_ADMINISTRATOR . $this->phpPath, '\.php$'));
            }

            if (is_array($files)) {
                usort($files, 'version_compare');
            }

            // Find Extension ID of Proclaim
            $query = $this->_db->getQuery(true);
            $query
                ->select('extension_id')
                ->from('#__extensions')
                ->where($this->_db->qn('name') . ' = ' . $this->_db->q('com_proclaim'));
            $this->_db->setQuery($query);
            $eid                 = $this->_db->loadResult();
            $this->biblestudyEid = $eid;

            foreach ($files as $i => $value) {
                $update = $this->versionSwitch;

                if ($update && $eid) {
                    // Set new Schema Version
                    $this->setSchemaVersion($update, $eid);
                } else {
                    $value = '9.0.0';
                }

                if (version_compare($value, $update) <= 0) {
                    unset($files[$i]);
                } elseif ($files) {
                    $this->totalSteps   += count($files);
                    $this->versionStack = (array)$files;
                } else {
                    $app->enqueueMessage(Text::_('JBS_INS_NO_UPDATE_SQL_FILES'), 'warning');

                    return;
                }
            }

            if (is_array($php)) {
                usort($php, 'version_compare');

                foreach ($php as $i => $value) {
                    if (version_compare($value, $this->versionSwitch) <= 0) {
                        unset($php[$i]);
                    } elseif ($php) {
                        $this->totalSteps += count($files);
                        $this->subFiles   = $php;
                    }
                }
            }
        }

        $this->isimport = Factory::getApplication()->input->getInt('cwmalt', 0);
        ++$this->totalSteps;
    }

    /**
     * Set the schema version for an extension by looking at its latest update
     *
     * @param   string  $version  Version number
     * @param   int     $eid      Extension ID
     *
     * @return  bool
     *
     * @throws  Exception
     * @since   7.1.0
     */
    private function setSchemaVersion(string $version, int $eid): bool
    {
        $app = Factory::getApplication();

        if ($version && $eid) {
            // Update the database
            $query = $this->_db->getQuery(true);
            $query
                ->delete()
                ->from('#__schemas')
                ->where('extension_id = ' . $eid);
            $this->_db->setQuery($query);

            if ($this->_db->execute()) {
                $query->clear();
                $query->insert($this->_db->quoteName('#__schemas'));
                $query->columns(array($this->_db->quoteName('extension_id'), $this->_db->quoteName('version_id')));
                $query->values($eid . ', ' . $this->_db->quote(substr($version, 0, 20)));
                $this->_db->setQuery($query);

                if (!$this->_db->execute()) {
                    $app->enqueueMessage('Error inserting ID', 'Error');

                    return false;
                }

                return true;
            }

            $app->enqueueMessage('Could not locate extension id in schemas table');

            return false;
        }

        return false;
    }

    /**
     * Cleanup postInstall before migration
     *
     * @return void
     *
     * @since 7.1
     */
    private function postinstallclenup(): void
    {
        // Post Install Messages Cleanup for Component
        $query = $this->_db->getQuery(true);
        $query->delete('#__postinstall_messages')
            ->where($this->_db->qn('language_extension') . ' = ' . $this->_db->q('com_proclaim'));
        $this->_db->setQuery($query);
        $this->_db->execute();
        Log::add('PostInstallCleanup', Log::INFO, 'com_proclaim');
    }

    /**
     * Saves the Versions/SQL/After stack in the session
     *
     * @return void
     *
     * @throws \JsonException
     * @since 7.1
     */
    private function saveStack(): void
    {
        $stack = array(
            'aversion'   => $this->version,
            'version'    => $this->versionStack,
            'switch'     => $this->versionSwitch,
            'allupdates' => $this->allupdates,
            'finish'     => $this->finish,
            'start'      => $this->start,
            'subFiles'   => $this->subFiles,
            'subQuery'   => $this->subQuery,
            'subSteps'   => $this->subSteps,
            'isimport'   => $this->isimport,
            'callstack'  => $this->callstack,
            'total'      => $this->totalSteps,
            'done'       => $this->doneSteps,
            'run'        => $this->running,
            'query'      => $this->installQuery,
        );
        $stack = json_encode($stack, JSON_THROW_ON_ERROR);

        if (function_exists('base64_encode') && function_exists('base64_decode')) {
            if (function_exists('gzdeflate') && function_exists('gzinflate')) {
                $stack = gzdeflate($stack, 9);
            }

            $stack = base64_encode($stack);
        }

        $session = Factory::getApplication()->getSession();
        $session->set('migration_stack', $stack, 'CWM');
    }

    /**
     * Makes sure that no more than 5 seconds since the start of the timer have elapsed
     *
     * @return bool
     *
     * @since 7.1
     */
    private function haveEnoughTime(): bool
    {
        $now     = $this->microtimeFloat();
        $elapsed = abs($now - $this->startTime);

        return $elapsed < 2;
    }

    /**
     *  Run the Migration will there is time.
     *
     * @param   bool  $resetTimer  If the time must be reset
     *
     * @return bool
     *
     * @throws Exception
     * @since 7.1
     */
    public function run(bool $resetTimer = true): bool
    {
        if ($resetTimer) {
            $this->resetTimer();
        }

        $this->loadStack();
        $result = true;

        while ($result && $this->haveEnoughTime()) {
            $result = $this->realRun();
        }

        $this->saveStack();

        return $result;
    }

    /**
     * Loads the Versions/SQL/After stack from the session
     *
     * @return void
     *
     * @throws \JsonException
     * @since 7.1
     */
    private function loadStack(): void
    {
        $session = Factory::getApplication()->getSession();
        $stack   = $session->get('migration_stack', '', 'CWM');

        if (empty($stack)) {
            $this->version       = '0.0.0';
            $this->versionStack  = [];
            $this->versionSwitch = '';
            $this->allupdates    = [];
            $this->finish        = [];
            $this->start         = [];
            $this->subFiles      = [];
            $this->subQuery      = [];
            $this->subSteps      = [];
            $this->isimport      = 0;
            $this->callstack     = [];
            $this->totalSteps    = 0;
            $this->doneSteps     = 0;
            $this->running       = Text::_('JBS_MIG_STARTING');
            $this->installQuery  = [];

            return;
        }

        if (function_exists('base64_encode') && function_exists('base64_decode')) {
            $stack = base64_decode($stack);

            if (function_exists('gzdeflate') && function_exists('gzinflate')) {
                $stack = gzinflate($stack);
            }
        }

        $stack = json_decode($stack, true, 512, JSON_THROW_ON_ERROR);

        $this->version       = $stack['aversion'];
        $this->versionStack  = $stack['version'];
        $this->versionSwitch = $stack['switch'];
        $this->allupdates    = $stack['allupdates'];
        $this->finish        = $stack['finish'];
        $this->start         = $stack['start'];
        $this->subFiles      = $stack['subFiles'];
        $this->subQuery      = $stack['subQuery'];
        $this->subSteps      = $stack['subSteps'];
        $this->isimport      = $stack['isimport'];
        $this->callstack     = $stack['callstack'];
        $this->totalSteps    = $stack['total'];
        $this->doneSteps     = $stack['done'];
        $this->running       = $stack['run'];
        $this->installQuery  = $stack['query'];
    }

    /**
     * Start the Run through the Pre Versions then SQL files then After PHP functions.
     *
     * @return bool
     *
     * @throws  Exception
     * @since   7.1
     */
    private function realRun(): bool
    {
        $app = Factory::getApplication();
        $run = true;

        if (!empty($this->start)) {
            $this->running = 'Backup DB';
            $this->doneSteps++;
            $export = new Cwmbackup();
            $export->exportdb(2);
            Log::add('Backup DB', Log::INFO, 'com_proclaim');
            $this->start = [];
        }

        if ($this->isimport) {
            $this->fixImport();
            $this->running  = 'Fixing Imported Params';
            $this->isimport = 0;
            Log::add('Fixing Imported Params', Log::INFO, 'com_proclaim');
            $this->doneSteps++;
        }

        if (!empty($this->versionStack)) {
            krsort($this->versionStack);

            while (!empty($this->versionStack) && $this->haveEnoughTime()) {
                $version       = array_pop($this->versionStack);
                $this->running = $version;
                $this->doneSteps++;
                $run = $this->allUpdate($version);

                if (!$run) {
                    Factory::getApplication()->enqueueMessage(
                        'Error Updating Update version ' . (string)$version,
                        'error'
                    );
                    Log::add('Error Updating Update version ' . (string)$version, Log::ERROR, 'com_proclaim');
                }
            }
        }

        if ((!empty($this->allupdates) || !empty($this->subFiles)) && empty($this->versionStack)) {
            ksort($this->allupdates);

            while ((!empty($this->allupdates) || !empty($this->subFiles)) && $this->haveEnoughTime()) {
                $this->version = key($this->allupdates);

                if (isset($this->allupdates[$this->version]) && @!empty($this->allupdates[$this->version])) {
                    if (strpos($this->running, $this->version)) {
                        $this->totalSteps += count((array)$this->allupdates[$this->version]);
                    }

                    // Used for Install array.
                    if (!is_array($this->allupdates[$this->version])) {
                        $this->allupdates[$this->version] = array($this->allupdates[$this->version]);
                    }

                    $string = array_shift($this->allupdates[$this->version]);

                    $this->running = $this->version . ' String: ' . $string;
                    $run           = $this->runUpdates($string);
                    $this->doneSteps++;
                } elseif (
                    in_array(
                        $this->version,
                        $this->subFiles,
                        true
                    ) && @empty($this->allupdates[$this->version])
                ) {
                    // Check for the corresponding PHP file and run migration
                    $migrationfile = JPATH_ADMINISTRATOR . '/components/com_proclaim/install/updates/' . $this->version . '.php';

                    require_once $migrationfile;
                    $migrationClass = "Migration" . str_ireplace(".", '', $this->version);
                    $migration      = new $migrationClass();

                    if (!class_exists($migrationClass)) {
                        Log::add('Missing Class' . $migrationClass, Log::WARNING, 'com_proclaim');

                        return true;
                    }

                    if (!empty($this->subSteps) && !empty($this->subFiles)) {
                        while (!empty($this->subFiles) && $this->haveEnoughTime()) {
                            $query = [];
                            $step  = $this->versionSwitch;

                            if (!isset($this->subQuery[$this->version][$step]) && !empty($this->subSteps[$this->version])) {
                                $step = $this->versionSwitch = array_shift($this->subSteps[$this->version]);
                                Log::add('Change step : ' . $step, Log::INFO, 'com_proclaim');
                            } elseif (empty($this->subSteps[$this->version])) {
                                Log::add('Unset Last Step : ' . $step, Log::INFO, 'com_proclaim');

                                $step = $this->versionSwitch = null;
                                unset($this->subSteps[$this->version], $this->subQuery[$this->version], $this->allupdates[$this->version]);

                                if (($key = array_search($this->version, $this->subFiles, true)) !== false) {
                                    unset($this->subFiles[$key]);
                                }
                            }

                            if (isset($this->subQuery[$this->version][$step]) && !empty($this->subQuery[$this->version][$step])) {
                                $query            = array_shift($this->subQuery[$this->version][$step]);
                                $migration->query = $this->subQuery[$this->version];
                            } elseif (isset($this->subQuery[$this->version][$step]) && empty($this->subQuery[$this->version][$step])) {
                                unset($this->subQuery[$this->version][$step]);
                                $this->versionSwitch = null;
                                Log::add(
                                    'UnSet Sub Query if empty : ' . $step . ' ' . $this->version,
                                    Log::INFO,
                                    'com_proclaim'
                                );
                            }

                            if (empty($step) && empty($query)) {
                                unset($this->subFiles[$this->version], $this->subSteps[$this->version]);
                                Log::add('UnSet Version in All updates : ' . $this->version, Log::INFO, 'com_proclaim');
                            } else {
                                $this->running = 'PHP Sub Process: ' . $this->version . ' - ' . $step;
                                $migration->$step(Factory::getDbo(), $query);

                                // Pull back the Query form PHP file if any.
                                if (isset($migration->query) && !empty($migration->query)) {
                                    $this->subQuery[$this->version] = $migration->query;
                                }

                                $queryString = null;

                                if (!empty($query) && is_array($query)) {
                                    $queryString = (string)$query['id'];
                                    $queryString = str_replace(
                                        array("\r", "\n"),
                                        array('', ' '),
                                        substr($queryString, 0, 80)
                                    );
                                    $queryString = ' ID:' . $queryString . ' Query count: ' . count(
                                        $this->subQuery[$this->version][$step]
                                    );
                                }

                                Log::add(
                                    'Doing Step in ' . $migrationClass . ' Step: ' . $step . $queryString,
                                    Log::INFO,
                                    'com_proclaim'
                                );

                                $this->doneSteps++;
                            }
                        }
                    }
                } else {
                    unset($this->allupdates[$this->version]);
                    Log::add('UnSet Version if no steps : ' . $this->version, Log::INFO, 'com_proclaim');
                }

                if ($run === false) {
                    CwmdbHelper::resetdb();
                    $this->resetStack();
                    $app->enqueueMessage(Text::_('JBS_CMN_DATABASE_NOT_MIGRATED'), 'warning');

                    return false;
                }
            }
        }

        if (!empty($this->finish) && empty($this->versionStack) && empty($this->allupdates) && empty($this->subFiles)) {
            while (!empty($this->finish) && $this->haveEnoughTime()) {
                $finish = array_pop($this->finish);
                $this->doneSteps++;
                $this->running = $finish;
                $this->finish($finish);
            }
        }

        /** We are going to walk thought the assets that need to be fixed that were found form the finish lookup. */
        if (
            !empty($this->installQuery)
            && empty($this->finish)
            && empty($this->versionStack)
            && empty($this->allupdates)
            && empty($this->subFiles)
        ) {
            krsort($this->installQuery);

            while (!empty($this->installQuery) && $this->haveEnoughTime()) {
                $this->versionSwitch = (string)key($this->installQuery);

                if (isset($this->installQuery[$this->versionSwitch]) && @!empty($this->installQuery[$this->versionSwitch])) {
                    $version = (object)array_pop($this->installQuery[$this->versionSwitch]);
                    $this->doneSteps++;
                    $this->running = 'Fixing Assets that are not right';
                    Cwmassets::fixAssets($this->versionSwitch, $version);
                } else {
                    unset($this->installQuery[$this->versionSwitch]);
                }
            }
        }

        if (
            empty($this->installQuery)
            && empty($this->finish)
            && empty($this->versionStack)
            && empty($this->allupdates)
            && empty($this->subFiles)
        ) {
            $admin = new CwmadminModel();
            $admin->fix();

            // Just finished
            $this->resetStack();
            $this->running = Text::_('JBS_MIGFINISED');

            return false;
        }

        // If we have more Versions or SQL files, continue in the next step
        return true;
    }

    /**
     * Fix an Import problem
     *
     * @return void True if fix complete, False if failure
     *
     * @since 7.1
     */
    private function fixImport(): void
    {
        $tables = CwmdbHelper::getObjects();
        $set    = false;

        foreach ($tables as $table) {
            if (!str_contains($table['name'], '_bsms_timeset')) {
                $query = $this->_db->getQuery(true);
                $query->select('*')->from($table);
                $this->_db->setQuery($query);
                $data = $this->_db->loadObjectList();

                foreach ($data as $row) {
                    if (isset($row->params)) {
                        $row->params = stripslashes($row->params);
                        $set         = true;
                    }

                    if (isset($row->metadata)) {
                        $row->metadata = stripslashes($row->metadata);
                        $set           = true;
                    }

                    if (isset($row->stylecode)) {
                        $row->stylecode = stripslashes($row->stylecode);
                        $set            = true;
                    }

                    if ($set) {
                        $this->_db->updateObject($table['name'], $row, array('id'));
                    }
                }
            }
        }
    }

    /**
     * Function to update using the version number for sql files
     *
     * @param   string  $value  The File name.
     *
     * @return bool
     *
     * @throws  Exception
     * @since   7.1.4
     */
    private function allUpdate(string $value): bool
    {
        $buffer = file_get_contents(JPATH_ADMINISTRATOR . $this->filePath . $value . '.sql');

        // Graceful exit and rollback if read not successful
        if ($buffer === false) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('JBS_INS_ERROR_SQL_READBUFFER'), 'WARNING');
            Log::add(Text::sprintf('JBS_INS_ERROR_SQL_READBUFFER'), Log::WARNING, 'com_proclaim');

            return false;
        }

        // Create an array of queries from the sql file
        $queries = DatabaseDriver::splitSql($buffer);

        if ((int)count($queries) === 0) {
            return false;
        }

        $this->totalSteps += count($queries);

        $this->allupdates = array_merge($this->allupdates, array($value => $queries));

        // Build php steps now.
        $migrationFile = JPATH_ADMINISTRATOR . '/components/com_proclaim/install/updates/' . $value . '.php';

        if (file_exists($migrationFile)) {
            require_once $migrationFile;
            $migrationClass = "Migration" . str_ireplace(".", '', $value);

            if (class_exists($migrationClass)) {
                $migration = new $migrationClass();

                if (isset($migration->postinstallMessages)) {
                    $steps            = $migration->steps;
                    $this->totalSteps += count($steps);

                    // If Steps build is mandatory.
                    $migration->build($this->_db);

                    if (isset($migration->count)) {
                        $this->totalSteps += (int)$migration->count;
                    }

                    $this->subSteps = array_merge($this->subSteps, array($value => $steps));
                    $this->subQuery = array_merge($this->subQuery, array($value => $migration->query));
                } else {
                    $this->subSteps = array_merge($this->subSteps, array($value => array('up')));
                    ++$this->totalSteps;
                }
            }
        }

        return true;
    }

    /**
     * Run updates SQL
     *
     * @param   string  $string  String of SQL to process.
     *
     * @return bool
     *
     * @throws  Exception
     * @since   7.1
     */
    private function runUpdates(string $string): bool
    {
        // Process each query in the $queries array (split out of sql file).
        $string = trim($string);

        if ($string !== '' && $string[0] !== '#') {
            $this->_db->setQuery($this->_db->convertUtf8mb4QueryToUtf8($string));
            $this->doneSteps++;

            try {
                $this->_db->execute();
            } catch (\RuntimeException $e) {
                Log::add($e->getMessage(), Log::WARNING, 'com_proclaim');

                return false;
            }

            $queryString = (string)$string;
            $queryString = str_replace(array("\r", "\n"), array('', ' '), substr($queryString, 0, 80));
            Log::add(
                Text::sprintf('JLIBINSTALLER_UPDATE_LOG_QUERY', $this->running, $queryString),
                Log::INFO,
                'com_proclaim'
            );
        }

        return true;
    }

    /**
     * Finish the system
     *
     * @param   string  $step  Step to process
     *
     * @return void
     *
     * @throws  Exception
     * @since   7.1
     */
    private function finish(string $step): void
    {
        $app = Factory::getApplication();

        switch ($step) {
            case 'updateversion':
                $update = $this->getUpdateVersion();

                // Set new Schema Version
                $run           = $this->setSchemaVersion($update, $this->biblestudyEid);
                $this->running = 'Update Version';
                break;
            case 'fixassets':
                // Final step is to fix assets by building what need to be fixed.
                $assets             = new Cwmassets();
                $string             = $assets->build();
                $this->installQuery = $string->query;
                $this->totalSteps   += $string->count;
                break;
            case 'fixmenus':
                $run           = $this->fixMenus();
                $this->running = 'Fix Menus';
                break;
            case 'fixemptyaccess':
                $run           = $this->fixemptyaccess();
                $this->running = 'Fix Empty Access';
                break;
            case 'fixemptylanguage':
                $run           = $this->fixemptylanguage();
                $this->running = 'Fix Empty Language';
                break;
            case 'rmoldurl':
                // Removes all other update urls except package url.
                $conditions = $this->rmoldurl();
                $query      = $this->_db->getQuery(true);
                $query->delete($this->_db->qn('#__update_sites'));
                $query->where($conditions, $glue = 'OR');
                $this->_db->setQuery($query);
                $this->_db->execute();
                $this->running = 'Remove Old Update URL\'s';
                break;
            case 'setupdateurl':
                // Find Extension ID of component
                $query = $this->_db->getQuery(true);
                $query
                    ->select('extension_id')
                    ->from('#__extensions')
                    ->where($this->_db->qn('name') . ' = ' . $this->_db->q('com_proclaim'));
                $this->_db->setQuery($query);
                $eid = $this->_db->loadResult();

                $conditions = array(
                    $this->_db->qn('name') . ' = ' .
                    $this->_db->q('Proclaim Package'),
                );
                $query      = $this->_db->getQuery(true);
                $query->delete($this->_db->qn('#__update_sites'));
                $query->where($conditions, $glue = 'OR');
                $this->_db->setQuery($query);
                $this->_db->execute();

                $conditions = array(
                    $this->_db->qn('extension_id') . ' = ' .
                    $this->_db->q($eid),
                );
                $query      = $this->_db->getQuery(true);
                $query->delete($this->_db->qn('#__update_sites_extensions'));
                $query->where($conditions, $glue = 'OR');
                $this->_db->setQuery($query);
                $this->_db->execute();

                $updateurl           = new \stdClass();
                $updateurl->name     = 'Proclaim Package';
                $updateurl->type     = 'extension';
                $updateurl->location = 'https://www.christianwebministries.org/index.php?option=com_ars&amp;view=update&amp;task=stream&amp;id=2&amp;format=xml';
                $updateurl->enabled  = '1';
                $this->_db->insertObject('#__update_sites', $updateurl);
                $lastid                     = $this->_db->insertid();
                $updateurl1                 = new \stdClass();
                $updateurl1->update_site_id = $lastid;
                $updateurl1->extension_id   = $eid;
                $this->_db->insertObject('#__update_sites_extensions', $updateurl1);
                $this->running = 'Set New Update URL';
                break;
            default:
                $app->enqueueMessage(
                    '' . Text::_('JBS_CMN_OPERATION_SUCCESSFUL') .
                    Text::_('SIMPLEMODEMESSAGE_BODY') .
                    Text::_('JBS_IBM_REVIEW_ADMIN_TEMPLATE')
                );
                break;
        }
    }

    /**
     * Returns Update Version form Table
     *
     * @return string Returns the Last Version in the #_bsms_update table
     *
     * @since 7.1
     */
    private function getUpdateVersion(): string
    {
        // Find Last updated Version in Update table

        $query = $this->_db->getQuery(true);
        $query
            ->select('version')
            ->from('#__bsms_update');
        $this->_db->setQuery($query);
        $updates = $this->_db->loadObjectList();

        return end($updates)->version;
    }

    /**
     * Fix Menus
     *
     * @return   bool
     * @since 7.1.0
     *
     */
    public function fixMenus(): bool
    {
        $query = $this->_db->getQuery(true);
        $query->select('*')
            ->from('#__menu')
            ->where($this->_db->qn('menutype') . ' != ' . $this->_db->q('main'))
            ->where($this->_db->qn('link') . ' LIKE ' . $this->_db->q('%com_proclaim%'));
        $this->_db->setQuery($query);
        $menus = $this->_db->loadObjectList();

        foreach ($menus as $menu) {
            $menu->link = str_replace('teacherlist', 'cwmteachers', $menu->link);
            $menu->link = str_replace('teacherdisplay', 'cwmteacher', $menu->link);
            $menu->link = str_replace('studydetails', 'cwmsermon', $menu->link);
            $menu->link = str_replace('serieslist', 'cwmseriesdisplays', $menu->link);
            $menu->link = str_replace('seriesdetail', 'cwmseriesdisplay', $menu->link);
            $menu->link = str_replace('studieslist', 'cwmsermons', $menu->link);
            $query      = $this->_db->getQuery(true);
            $query->update('#__menu')
                ->set("link = " . $this->_db->q($menu->link))
                ->where('id = ' . $this->_db->q($menu->id));
            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        return true;
    }

    /**
     * Function to Find empty access in the db and set them to Public
     *
     * @return   bool
     * @throws Exception
     * @since 7.1.0
     *
     */
    public function fixemptyaccess(): bool
    {
        // Tables to fix
        $tables = array(
            array('table' => '#__bsms_admin'),
            array('table' => '#__bsms_mediafiles'),
            array('table' => '#__bsms_message_type'),
            array('table' => '#__bsms_podcast'),
            array('table' => '#__bsms_series'),
            array('table' => '#__bsms_servers'),
            array('table' => '#__bsms_studies'),
            array('table' => '#__bsms_studytopics'),
            array('table' => '#__bsms_teachers'),
            array('table' => '#__bsms_templates'),
            array('table' => '#__bsms_topics'),
        );

        // Get Public id
        $id = Factory::getApplication()->getConfig()->get('access', 1);

        // Correct blank or not set records
        foreach ($tables as $table) {
            $query = $this->_db->getQuery(true);
            $query->update($table['table'])
                ->set('access = ' . $id)
                ->where("access = " . $this->_db->q('0'), $glue = 'OR')
                ->where("access = " . $this->_db->q(' '));
            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        return true;
    }

    /**
     * Function to find empty language field and set them to "*"
     *
     * @return   bool
     * @since 7.1.0
     *
     */
    public function fixemptylanguage(): bool
    {
        // Tables to fix
        $tables = array(
            array('table' => '#__bsms_comments'),
            array('table' => '#__bsms_mediafiles'),
            array('table' => '#__bsms_series'),
            array('table' => '#__bsms_studies'),
            array('table' => '#__bsms_teachers'),
        );

        // Correct blank records
        foreach ($tables as $table) {
            $query = $this->_db->getQuery(true);
            $query->update($table['table'])
                ->set('language = ' . $this->_db->q('*'))
                ->where('language = ' . $this->_db->q(''));
            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        return true;
    }

    /**
     * Old Update URLs
     *
     * @return array
     *
     * @since 7.1
     */
    public function rmoldurl()
    {
        return array(
            $this->_db->qn('name') . ' = ' .
            $this->_db->q('Proclaim Module'),
            $this->_db->qn('name') . ' = ' .
            $this->_db->q('Proclaim Podcast Module'),
            $this->_db->qn('name') . ' = ' .
            $this->_db->q('Proclaim Finder Plg'),
            $this->_db->qn('name') . ' = ' .
            $this->_db->q('Proclaim Backup Plg'),
            $this->_db->qn('name') . ' = ' .
            $this->_db->q('Proclaim Podcast Plg'),
            $this->_db->qn('name') . ' = ' .
            $this->_db->q('Proclaim')
        );
    }

    /**
     * Uninstall of CWM
     *
     * @return bool
     *
     * @throws  Exception
     * @since   7.1
     */
    public function uninstall(): bool
    {
        // Check if CWM can be found from the database
        $table = $this->_db->getPrefix() . 'bsms_admin';
        $this->_db->setQuery("SHOW TABLES LIKE {$this->_db->quote($table)}");
        $drop_result = '';

        if ($this->_db->loadResult()) {
            $query = $this->_db->getQuery(true);
            $query->select('*')
                ->from('#__bsms_admin')
                ->where('id = 1');
            $this->_db->setQuery($query);
            $adminsettings = $this->_db->loadObject();
            $drop_tables   = $adminsettings->drop_tables;

            if ($drop_tables > 0) {
                // We must remove the assets manually each time
                $query = $this->_db->getQuery(true);
                $query->select('id')
                    ->from('#__assets')
                    ->where('name = ' . $this->_db->q(BIBLESTUDY_COMPONENT_NAME));
                $this->_db->setQuery($query);
                $parent_id = $this->_db->loadResult();
                $query     = $this->_db->getQuery(true);

                if ($parent_id !== '0') {
                    $query->delete()
                        ->from('#__assets')
                        ->where('parent_id = ' . $this->_db->q($parent_id))
                        ->where('name != ' . $this->_db->q('root.1'));
                    $this->_db->setQuery($query);
                    $this->_db->execute();
                }

                $query = $this->_db->getQuery(true);
                $query->delete()
                    ->from('#__assets')
                    ->where('name LIKE ' . $this->_db->q(BIBLESTUDY_COMPONENT_NAME))
                    ->where('name != ' . $this->_db->q('root.1'));
                $this->_db->setQuery($query);
                $this->_db->execute();
                $buffer = file_get_contents(
                    JPATH_ADMINISTRATOR . '/components/com_proclaim/install/sql/uninstall-dbtables.sql'
                );

                // Graceful exit and rollback if read not successful
                if ($buffer === false) {
                    die('no uninstall-dbtables.sql');
                }

                $queries = DatabaseDriver::splitSql($buffer);

                foreach ($queries as $querie) {
                    $querie = trim($querie);

                    if ($querie !== '' && $querie[0] !== '#' && $querie !== '`') {
                        $this->_db->setQuery($querie);
                        $this->_db->execute();
                    }
                }
            }
        } else {
            $drop_result = '<h3>' . Text::_('JBS_INS_NO_DATABASE_REMOVED') . '</h3>';
        }

        // Post Install Messages Cleanup for Component
        $query = $this->_db->getQuery(true);
        $query->delete('#__postinstall_messages')
            ->where($this->_db->qn('language_extension') . ' = ' . $this->_db->q('com_proclaim'));
        $this->_db->setQuery($query);
        $this->_db->execute();
        Factory::getApplication()->enqueueMessage(
            '<h2>' . Text::_('JBS_INS_UNINSTALLED') . ' ' .
            BIBLESTUDY_VERSION . '</h2> <div>' . $drop_result . '</div>'
        );

        return true;
    }

    /**
     * Update messages
     *
     * @param   object  $message  Install object
     *
     * @return void
     *
     * @since 7.1
     */
    public function postinstallMessages($message)
    {
        // Find Extension ID of component
        $query = $this->_db->getQuery(true);
        $query
            ->select('extension_id')
            ->from('#__extensions')
            ->where($this->_db->qn('name') . ' = ' . $this->_db->q('com_proclaim'));
        $this->_db->setQuery($query);
        $eid                   = $this->_db->loadResult();
        $this->biblestudyEid   = $eid;
        $message->extension_id = $this->biblestudyEid;

        if ($this->_db->insertObject('#__postinstall_messages', $message) !== true) {
            exit('Bad install');
        }
    }

    /**
     * Correct problem in update table under 7.0.2 systems
     *
     * @return void
     *
     * @throws  Exception
     * @since   7.1
     */
    private function correctVersions(): void
    {
        // Find the Last updated Version in Update table
        $query = $this->_db->getQuery(true);
        $query->select('*')
            ->from('#__bsms_update');
        $this->_db->setQuery($query);
        $updates = $this->_db->loadObjectList();

        foreach ($updates as $value) {
            // Check to see if a Bad version is in key 3

            if (($value->id === '3') && ($value->version !== '7.0.1.1')) {
                // Find the Last updated Version in Update table
                $query = "INSERT INTO `#__bsms_update` (id,version) VALUES (3,'7.0.1.1')
                            ON DUPLICATE KEY UPDATE version= '7.0.1.1';";
                $this->_db->setQuery($query);

                if (!$this->_db->execute()) {
                    Factory::getApplication()->enqueueMessage(Text::_('JBS_CMN_OPERATION_FAILED'), 'error');

                    return;
                }
            }
        }
    }
}
