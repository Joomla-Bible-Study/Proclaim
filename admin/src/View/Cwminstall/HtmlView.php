<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMInstall;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for Install
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class HtmlView extends BaseHtmlView
{
    /** @var integer Total numbers of Steps
     * @since    7.0.0
     */
    public int $totalSteps = 0;

    /** @var integer Numbers of Steps already processed
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
    /** @var string Starte of install
     * @since    7.0.0
     */
    public $state;
    /** @var object Status
     * @since    7.0.0
     */
    public $status;
    /** @var array The pre-versions sub sql array to process
     * @since    7.0.0
     */
    public array $allupdates = [];
    /** @var array Array of Install Task
     * @since    7.0.0
     */
    public array $install = [];
    /** @var boolean More
     * @since    7.0.0
     */
    protected bool $more;
    /** @var  string Percentage
     * @since    7.0.0
     */
    protected $percentage;
    /** @var string Type of process
     * @since    7.0.0
     */
    protected string $type;
    /** @var array The pre versions to process
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
    /** @var integer If was imported
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
    public function display($tpl = null): void
    {
        $app             = Factory::getApplication();
        $this->scanstate = $app->input->get('scanstate', false);

        // Get data from the model
        $this->state = $this->get("State");
        $layout      = $app->input->get('layout', 'default');
        $task        = $app->input->get('task', 'execute');

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

        if ($this->more) {
            $doc = $this->getDocument();
            $wa  = $doc->getWebAssetManager();
            $wa->useScript('form.validate')
                ->addInlineScript(
                    "setTimeout(function(){
                                    jQuery('#adminForm').submit()
								}, 3000);"
                );
        }

        if ($this->more === false) {
            $this->setLayout('install_finished');
        }

        // Install systems setup files
        // @todo need to move to a helper as this is call do many times.
        $this->installsetup();

        $this->addToolbar();

        $this->setDocumentTitle(Text::sprintf('JBS_TITLE_INSTALL', $this->percentage . '%', $this->running));

        // Display the template
        parent::display($tpl);
    }

    /**
     * Loads the Versions/SQL/After stack from the session
     *
     * @return boolean
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

        if (function_exists('base64_encode') && function_exists('base64_decode')) {
            $stack = base64_decode($stack);

            if (function_exists('gzdeflate') && function_exists('gzinflate')) {
                $stack = gzinflate($stack);
            }
        }

        $stack = json_decode($stack, true);

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
     * @since 7.0.0
     *
     */
    protected function installSetup(): void
    {
        $language = Factory::getApplication()->getLanguage();

        $installation_queue = array(
            // Example: modules => { (folder) => { (module) => { (position), (published) } }* }*
            'modules' => array(
                'administrator' => array(),
                'site'          => array(
                    'proclaim'         => 0,
                    'proclaim_podcast' => 0,
                )
            ),
            // Example: plugins => { (folder) => { (element) => (published) }* }*
            'plugins' => array(
                'finder' => array(
                    'proclaim' => 1,
                ),
                'system' => array(
                    'proclaim'  => 0,
                    'proclaimpodcast' => 0,
                )
            )
        );

        // -- General settings
        jimport('joomla.installer.installer');
        $db                       = Factory::getContainer()->get('DatabaseDriver');
        $this->status             = new \stdClass();
        $this->status->cwmmodules = array();
        $this->status->cwmplugins = array();

        // Modules installation
        if (count($installation_queue['modules'])) {
            foreach ($installation_queue['modules'] as $folder => $modules) {
                if (count($modules)) {
                    foreach ($modules as $module => $modulePreferences) {
                        // Was the module already installed?
                        $sql = $db->getQuery(true);
                        $sql->select('COUNT(*)')->from('#__extensions')->where('name=' . $db->q('mod_' . $module));
                        $db->setQuery($sql);
                        $result                     = $db->loadResult();
                        $this->status->cwmmodules[] = array_merge(
                            $this->status->cwmmodules,
                            array(
                                'name'   => 'mod_' . $module,
                                'client' => $folder,
                                'result' => $result
                            )
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
        if (count($installation_queue['plugins'])) {
            foreach ($installation_queue['plugins'] as $folder => $plugins) {
                if (count($plugins)) {
                    foreach ($plugins as $plugin => $published) {
                        $query = $db->getQuery(true);
                        $query->select('COUNT(*)')
                            ->from('#__extensions')
                            ->where('folder=' . $db->q($folder))
                            ->where('name=' . $db->q('plg_' . $folder . '_' . $plugin));
                        $db->setQuery($query);
                        $result                     = $db->loadResult();
                        $this->status->cwmplugins[] = array_merge(
                            $this->status->cwmplugins,
                            array(
                                'name'   => 'plg_' . $folder . '_' . $plugin,
                                'group'  => $folder,
                                'result' => $result
                            )
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
            Factory::getApplication()->input->set('hidemainmenu', true);
        }

        ToolbarHelper::help('proclaim', true);
        ToolbarHelper::title(Text::_('JBS_CMN_INSTALL'), 'administration');
    }
}
