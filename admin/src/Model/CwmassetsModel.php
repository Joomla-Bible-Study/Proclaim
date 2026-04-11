<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

declare(strict_types=1);

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseInterface;

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
     * @var int Parent ID of asset
     * @since 7.0
     */
    public int $parent_id = 0;

    /** @var int Total numbers of Versions
     * @since 7.0
     */
    public int $totalSteps = 0;

    /** @var int Numbers of Versions already processed
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
    public array $assets = [];

    /** @var float The time the process started
     * @since 7.0
     */
    private float $startTime;

    /** @var array The pre versions to process
     * @since 7.0
     */
    private array $versionStack = [];

    /** @var array The pre versions sub sql array to process
     * @since 7.0
     */
    private array $allupdates = [];

    /** @var string Version of Proclaim
     * @since 7.0
     */
    private string $versionSwitch = '';

    /** @var string Model name
     * @since 7.0
     */
    protected $name = '';

    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @throws \Exception
     * @since 7.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $this->name = 'cwmassets';
    }

    /**
     * Start Looking though the Versions
     *
     * @return bool
     *
     * @throws \Exception
     * @since 7.0
     */
    public function startScanning(): bool
    {
        $this->resetStack();
        $this->resetTimer();
        $this->getSteps();

        if (empty($this->versionStack)) {
            $this->versionStack = [];
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
     * @throws \Exception
     * @since 7.0
     */
    private function resetStack(): void
    {
        $session = Factory::getApplication()->getSession();
        $session->set('asset_stack', '', 'CWM');
        $this->versionStack  = [];
        $this->versionSwitch = '';
        $this->allupdates    = [];
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

        return (float)$usec + (float)$sec;
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
        $results = Cwmassets::build();

        $this->versionStack = $results->query;
        $this->totalSteps   = $results->count;
    }

    /**
     * Saves the Versions/SQL/After stack in the session
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0
     */
    private function saveStack(): void
    {
        $stack = [
            'version'    => $this->versionStack,
            'step'       => $this->step,
            'switch'     => $this->versionSwitch,
            'allupdates' => $this->allupdates,
            'total'      => $this->totalSteps,
            'done'       => $this->doneSteps,
        ];
        $stack = json_encode($stack, JSON_THROW_ON_ERROR);

        if (\function_exists('base64_encode') && \function_exists('base64_decode')) {
            if (\function_exists('gzdeflate') && \function_exists('gzinflate')) {
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
     * @throws \Exception
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
     * @throws \Exception
     * @since 7.0
     */
    private function loadStack(): void
    {
        $session = Factory::getApplication()->getSession();
        $stack   = $session->get('asset_stack', '', 'CWM');

        if (empty($stack)) {
            $this->versionStack  = [];
            $this->versionSwitch = '';
            $this->allupdates    = [];
            $this->step          = '';
            $this->totalSteps    = 0;
            $this->doneSteps     = 0;

            return;
        }

        if (\function_exists('base64_encode') && \function_exists('base64_decode')) {
            $stack = base64_decode($stack);

            if (\function_exists('gzdeflate') && \function_exists('gzinflate')) {
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
     * @throws \Exception
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
     * Collect per-table asset status counts.
     *
     * **Behavior changed in 10.3.0**: Proclaim no longer registers a
     * per-record #__assets row for every item. Records with `asset_id = 0`
     * inherit permission checks from the com_proclaim parent, and that is
     * now the normal, desired state. The returned shape reflects the new
     * model — see `Cwmassets::getAssetStatus()` for key meanings.
     *
     * @return  array
     *
     * @since   7.0
     */
    public function checkAssets(): array
    {
        if ($this->parent_id === 0) {
            $this->parentId();
        }

        return Cwmassets::getAssetStatus();
    }

    /**
     * Set Parent ID (ensures parent asset exists)
     *
     * @return void
     *
     * @since 7.0
     */
    public function parentId(): void
    {
        // Use ensureParentAsset to create if missing
        $this->parent_id = Cwmassets::ensureParentAsset();
    }
}
