<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwminstall;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwminstallModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseInterface;

/**
 * View class for Install
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class HtmlView extends BaseHtmlView
{
    /** @var int Total numbers of Steps
     * @since    7.0.0
     */
    public int $totalSteps = 0;

    /** @var int Numbers of Steps already processed
     * @since    7.0.0
     */
    public int $doneSteps = 0;

    /** @var string Running Now
     * @since    7.0.0
     */
    public string $running = '';

    /** @var array Call stack for the Visioning System.
     * @since    7.0.0
     */
    public array $callstack = [];

    public array $subSteps;

    public mixed $subQuery;

    public mixed $subFiles;

    public string $version = '0.0.0';

    public array $query = [];
    /** @var ?object Start of installation
     * @since    7.0.0
     */
    public ?object $state = null;
    /** @var ?object Status
     * @since    7.0.0
     */
    public ?object $status = null;
    /** @var array Array of Install Task
     * @since    7.0.0
     */
    public array $start = [];
    /** @var bool|string Scan state
     * @since    7.0.0
     */
    public mixed $scanstate = false;
    /** @var array The pre-versions sub sql array to process
     * @since    7.0.0
     */
    public array $allupdates = [];
    /** @var array Array of Install Task
     * @since    7.0.0
     */
    public array $install = [];
    /** @var bool More
     * @since    7.0.0
     */
    protected bool $more;
    /** @var int|float Percentage
     * @since    7.0.0
     */
    protected int|float $percentage = 0;
    /** @var string Type of process
     * @since    7.0.0
     */
    protected string $type;
    /** @var string Install type: 'install', 'migration', or 'upgrade' @since 10.1.0 */
    public string $installType = 'migration';
    /** @var array The pre-versions to process
     * @since    7.0.0
     */
    private array $versionStack;
    /** @var string The pre-versions to process
     * @since    7.0.0
     */
    private string $versionSwitch;
    /** @var array Array of Finish Task
     * @since    7.0.0
     */
    private array $finish = [];
    /** @var int If was imported
     * @since    7.0.0
     */
    private int $isimport = 0;

    /**
     * Display
     *
     * @param   string  $tpl  Template to display
     *
     * @return void
     *
     * @throws  \Exception
     * @since   7.0.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwminstallModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $app             = Factory::getApplication();
        $this->scanstate = $app->getInput()->get('scanstate', false);

        // Get data from the model
        $this->state = $model->getState();
        $layout      = $app->getInput()->get('layout', 'default');
        $task        = $app->getInput()->get('task', 'execute');

        $load    = $this->loadStack();
        $more    = true;
        $percent = 0;

        if ($this->scanstate && $load) {
            if ($this->totalSteps > 0) {
                $percent = min(max(round(100 * $this->doneSteps / $this->totalSteps), 1), 100);
            }
        } elseif ($load) {
            $percent = 100;
            $more    = false;
        }

        $this->more = $more;
        $this->setLayout($layout);

        $this->percentage = $percent;

        // Set install type from callstack (determined in model's getSteps)
        if (!empty($this->callstack['install_type'])) {
            $this->installType = $this->callstack['install_type'];
        }

        // Note: Auto-submit is now handled in the template for better control

        if ($this->more === false) {
            $this->setLayout('install_finished');
        }

        // Install systems setup files
        // @todo need to move to a helper as this is call do many times.
        $this->installSetup();

        $this->addToolbar();

        // Display the template
        parent::display($tpl);
    }

    /**
     * Loads the Versions/SQL/After stack from the session
     *
     * @return bool
     *
     * @throws \Exception
     * @since    7.0.0
     */
    private function loadStack(): bool
    {
        $session = Factory::getApplication()->getSession();
        $stack   = $session->get('migration_stack', '', 'CWM');

        if (empty($stack)) {
            return false;
        }

        if (\function_exists('base64_encode') && \function_exists('base64_decode')) {
            $stack = base64_decode($stack);

            if (\function_exists('gzdeflate') && \function_exists('gzinflate')) {
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
        $this->query         = $stack['query'];

        return true;
    }

    /**
     * Setup Array for installation System
     *
     * @return void
     * @throws \Exception
     * @since 7.0.0
     *
     */
    protected function installSetup(): void
    {
        $language = Factory::getApplication()->getLanguage();

        $installation_queue = [
            // Example: modules => { (folder) => { (module) => { (position), (published) } }* }*
            'modules' => [
                'administrator' => [],
                'site'          => [
                    'proclaim'         => 0,
                    'proclaim_podcast' => 0,
                ],
            ],
            // Example: plugins => { (folder) => { (element) => (published) }* }*
            'plugins' => [
                'finder' => [
                    'proclaim' => 1,
                ],
                'task' => [
                    'proclaim' => 1,
                ],
            ],
        ];

        // -- General settings
        $db                       = Factory::getContainer()->get(DatabaseInterface::class);
        $this->status             = new \stdClass();
        $this->status->cwmmodules = [];
        $this->status->cwmplugins = [];

        // Modules installation
        if (\count($installation_queue['modules'])) {
            foreach ($installation_queue['modules'] as $folder => $modules) {
                if (\count($modules)) {
                    foreach ($modules as $module => $modulePreferences) {
                        // Was the module already installed?
                        $sql = $db->getQuery(true);
                        $sql->select('COUNT(*)')->from($db->quoteName('#__extensions'))->where($db->quoteName('name') . ' = ' . $db->q('mod_' . $module));
                        $db->setQuery($sql);
                        $result                     = $db->loadResult();
                        $this->status->cwmmodules[] = array_merge(
                            $this->status->cwmmodules,
                            [
                                'name'   => 'mod_' . $module,
                                'client' => $folder,
                                'result' => $result,
                            ]
                        );

                        if (is_dir(JPATH_ROOT . '/modules/mod_' . $module . '/')) {
                            $language->load(
                                'mod_' . $module,
                                JPATH_ROOT . '/modules/mod_' . $module,
                                'en-GB',
                                true
                            );
                            $language->load(
                                'mod_' . $module,
                                JPATH_ROOT . '/modules/mod_' . $module,
                                null,
                                true
                            );
                        }
                    }
                }
            }
        }

        // Plugins installation
        if (\count($installation_queue['plugins'])) {
            foreach ($installation_queue['plugins'] as $folder => $plugins) {
                if (\count($plugins)) {
                    foreach ($plugins as $plugin => $published) {
                        $query = $db->getQuery(true);
                        $query->select('COUNT(*)')
                            ->from($db->quoteName('#__extensions'))
                            ->where($db->quoteName('folder') . ' = ' . $db->q($folder))
                            ->where($db->quoteName('name') . ' = ' . $db->q('plg_' . $folder . '_' . $plugin));
                        $db->setQuery($query);
                        $result                     = $db->loadResult();
                        $this->status->cwmplugins[] = array_merge(
                            $this->status->cwmplugins,
                            [
                                'name'   => 'plg_' . $folder . '_' . $plugin,
                                'group'  => $folder,
                                'result' => $result,
                            ]
                        );

                        if (is_dir(JPATH_ROOT . '/plugins/' . $folder . '/' . $plugin . '/')) {
                            $language->load(
                                'plg_' . $folder . '_' . $plugin,
                                JPATH_ROOT . '/plugins/' . $folder . '/' . $plugin,
                                'en-GB',
                                true
                            );
                            $language->load(
                                'plg_' . $folder . '_' . $plugin,
                                JPATH_ROOT . '/plugins/' . $folder . '/' . $plugin,
                                null,
                                true
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Add Toolbar to page
     *
     * @return void
     *
     * @throws \Exception
     * @since  7.0.0
     *
     */
    protected function addToolbar(): void
    {
        if ($this->more) {
            Factory::getApplication()->getInput()->set('hidemainmenu', true);
        }

        ToolbarHelper::help('proclaim', true);
        ToolbarHelper::title(Text::_('JBS_CMN_INSTALL'), 'administration');
    }
}
