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

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * class Assets model
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class CwmassetsModel extends ListModel
{
    /**
     * Parent ID
     *
     * @var integer Parent ID of asset
     * @since 7.0
     */
    public int $parent_id = 0;

    /** @var integer Total numbers of Versions
     * @since 7.0
     */
    public int $totalSteps = 0;

    /** @var integer Numbers of Versions already processed
     * @since 7.0
     */
    public int $doneSteps = 0;

    /**
     * @var string
     * @since 7.0
     */
    public string $step = '';

    /**
     * @var array
     * @since 7.0
     */
    public array $assets = array();

    /** @var float The time the process started
     * @since 7.0
     */
    private float $startTime;

    /** @var array The pre versions to process
     * @since 7.0
     */
    private array $versionStack = array();

    /** @var array The pre versions sub sql array to process
     * @since 7.0
     */
    private array $allupdates = array();

    /** @var string Version of Proclaim
     * @since 7.0
     */
    private string $versionSwitch = '';

    /**
     * Constructor.
     *
     * @param   array                                             $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface|null  $factory  The factory.
     *
     * @throws Exception
     * @since 7.0
     */
    public function __construct($config = array(), MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $this->name = 'cwmassets';
    }

    /**
     * Start Looking though the Versions
     *
     * @return boolean
     *
     * @throws Exception
     * @since 7.0
     */
    public function startScanning(): bool
    {
        $this->resetStack();
        $this->resetTimer();
        $this->getSteps();

        if (empty($this->versionStack)) {
            $this->versionStack = array();
        }

        ksort($this->versionStack);

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
     * @since 7.0
     */
    private function resetStack(): void
    {
        $session = Factory::getApplication()->getSession();
        $session->set('asset_stack', '', 'CWM');
        $this->versionStack  = array();
        $this->versionSwitch = '';
        $this->allupdates    = array();
        $this->step          = '';
        $this->totalSteps    = 0;
        $this->doneSteps     = 0;
    }

    /**
     * Starts or resets the internal timer
     *
     * @return void
     *
     * @since 7.0
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
     * @since 7.0
     */
    private function microtimeFloat(): float
    {
        [$usec, $sec] = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }

    /**
     * Get migrate versions of DB after import/copy has finished.
     *
     * @return void
     *
     * @since 7.0
     */
    private function getSteps(): void
    {
        $fix     = new Cwmassets();
        $results = $fix->build();

        $this->versionStack = $results->query;
        $this->totalSteps   = $results->count;
    }

    /**
     * Saves the Versions/SQL/After stack in the session
     *
     * @return void
     *
     * @throws Exception
     * @since 7.0
     */
    private function saveStack(): void
    {
        $stack = array(
            'version'    => $this->versionStack,
            'step'       => $this->step,
            'switch'     => $this->versionSwitch,
            'allupdates' => $this->allupdates,
            'total'      => $this->totalSteps,
            'done'       => $this->doneSteps,
        );
        $stack = json_encode($stack, JSON_THROW_ON_ERROR);

        if (function_exists('base64_encode') && function_exists('base64_decode')) {
            if (function_exists('gzdeflate') && function_exists('gzinflate')) {
                $stack = gzdeflate($stack, 9);
            }

            $stack = base64_encode($stack);
        }

        $session = Factory::getApplication()->getSession();
        $session->set('asset_stack', $stack, 'CWM');
    }

    /**
     * Makes sure that no more than 5 seconds since the start of the timer have elapsed
     *
     * @return bool
     *
     * @since 7.0
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
     * @since 7.0
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
     * @throws Exception
     * @since 7.0
     */
    private function loadStack(): void
    {
        $session = Factory::getApplication()->getSession();
        $stack   = $session->get('asset_stack', '', 'CWM');

        if (empty($stack)) {
            $this->versionStack  = array();
            $this->versionSwitch = '';
            $this->allupdates    = array();
            $this->step          = '';
            $this->totalSteps    = 0;
            $this->doneSteps     = 0;

            return;
        }

        if (function_exists('base64_encode') && function_exists('base64_decode')) {
            $stack = base64_decode($stack);

            if (function_exists('gzdeflate') && function_exists('gzinflate')) {
                $stack = gzinflate($stack);
            }
        }

        $stack = json_decode($stack, true, 512, JSON_THROW_ON_ERROR);

        $this->versionStack  = (array)$stack['version'];
        $this->versionSwitch = (string)$stack['switch'];
        $this->allupdates    = $stack['allupdates'];
        $this->step          = (string)$stack['step'];
        $this->totalSteps    = (int)$stack['total'];
        $this->doneSteps     = (int)$stack['done'];
    }

    /**
     * Start the Run through the Pre Versions then SQL files then After PHP functions.
     *
     * @return bool
     *
     * @throws Exception
     * @since 7.0
     */
    private function realRun(): bool
    {
        if (!empty($this->versionStack)) {
            krsort($this->versionStack);

            while (!empty($this->versionStack) && $this->haveEnoughTime()) {
                $this->step = key($this->versionStack);

                if (isset($this->versionStack[$this->step]) && @!empty($this->versionStack[$this->step])) {
                    $version = (object)array_pop($this->versionStack[$this->step]);
                    $this->doneSteps++;
                    Cwmassets::fixAssets($this->step, $version);
                } else {
                    unset($this->versionStack[$this->step]);
                }
            }
        }

        if (empty($this->versionStack)) {
            // Just finished
            $this->resetStack();

            return false;
        }

        // If we have more Versions or SQL files, continue in the next step
        return true;
    }

    /**
     * Check Assets
     *
     * @return array
     *
     * @since 7.0
     */
    public function checkAssets(): array
    {
        $return = [];
        $result = '';
        $db     = Factory::getContainer()->get('DatabaseDriver');

        // First get the new parent_id
        if ($this->parent_id === 0) {
            $this->parentId();
        }

        // Get the names of the Proclaim tables
        $objects = Cwmassets::getassetObjects();

        // Run through each table
        foreach ($objects as $object) {
            // Put the table into the return array
            // Get the total number of rows and collect the table into a query
            $query = $db->getQuery(true);
            $query->select('j.id as jid, j.asset_id as jasset_id, a.id as aid, a.rules as arules, a.parent_id')
                ->from($db->qn($object['name']) . ' as j')
                ->leftJoin('#__assets as a ON (a.id = j.asset_id)');
            $db->setQuery($query);
            $results     = $db->loadObjectList();
            $nullrows    = 0;
            $matchrows   = 0;
            $arulesrows  = 0;
            $nomatchrows = 0;
            $numrows     = count($results);

            // Now go through each record to test it for asset id
            foreach ($results as $result) {
                // If there is no jasset_id it means that this has not been set and should be
                if (!$result->jasset_id) {
                    $nullrows++;
                }

                // If there is a jasset_id but no match to the parent_id then a mismatch has occurred
                if ($this->parent_id !== (int)(int)$result->parent_id && $result->jasset_id) {
                    $nomatchrows++;
                }

                // If $parent_id and $result->parent_id match and the Asset rules are not blank then everything is okay
                if ($this->parent_id === (int)$result->parent_id && $result->arules !== "") {
                    $matchrows++;
                }

                // If $parent_id and $result->parent_id match and the Asset rules is blank we need to fix
                if ($this->parent_id === (int)$result->parent_id && $result->arules === "") {
                    $arulesrows++;
                }
            }

            $return[] = array(
                'realname'         => $object['realname'],
                'numrows'          => $numrows,
                'nullrows'         => $nullrows,
                'matchrows'        => $matchrows,
                'arulesrows'       => $arulesrows,
                'nomatchrows'      => $nomatchrows,
                'parent_id'        => $this->parent_id,
                'result_parent_id' => $result->parent_id,
                'id'               => $result->jid,
                'assetid'          => $result->jasset_id
            );
        }

        return $return;
    }

    /**
     * Set Parent ID
     *
     * @return void
     *
     * @since 7.0
     */
    public function parentId(): void
    {
        $this->parent_id = Cwmassets::parentId();
    }
}
