<?php

/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// Check to ensure this file is included in Joomla!

namespace CWM\Component\Proclaim\Administrator\View\Cwmadmin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmanalyticsHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmupgradeHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Filesystem\Folder;

/**
 * View class for Admin
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Cached component version
     *
     * @var string|null
     * @since 9.0.0
     */
    private static ?string $cachedVersion = null;

    /**
     * Version
     *
     * @var string
     * @since    7.0.0
     */
    public $version;

    /**
     * Can Do
     *
     * @var string
     * @since    7.0.0
     */
    public $canDo;

    /**
     * Change Set
     *
     * @var string
     * @since    7.0.0
     */
    public $changeSet;

    /**
     * Errors
     *
     * @var string
     * @since    7.0.0
     */
    public $errors;

    /**
     * Results
     *
     * @var string
     * @since    7.0.0
     */
    public $results;

    /**
     * Schema Version
     *
     * @var string
     * @since    7.0.0
     */
    public string $schemaVersion;

    /**
     * Update Version
     *
     * @var string
     * @since    7.0.0
     */
    public string $updateVersion;

    /**
     * Pagination
     *
     * @var string
     * @since    7.0.0
     */
    public string $pagination;

    /**
     * Error Count
     *
     * @var string
     * @since    7.0.0
     */
    public string $errorCount;

    /**
     * Joomla Version
     *
     * @var string
     * @since    7.0.0
     */
    public string $jversion;

    /**
     * Temp Destination
     *
     * @var string
     * @since    7.0.0
     */
    public string $tmp_dest;

    /**
     * Player Stats
     *
     * @var string
     * @since    7.0.0
     */
    public $playerstats;

    /**
     * Assets
     *
     * @var string
     * @since    7.0.0
     */
    public $assets;

    /**
     * Popups
     *
     * @var string
     * @since    7.0.0
     */
    public $popups;

    /**
     * SS
     *
     * @var string
     * @since    7.0.0
     */
    public $ss;

    /**
     * Lists
     *
     * @var string
     * @since    7.0.0
     */
    public $lists;

    /**
     * PI
     *
     * @var string
     * @since    7.0.0
     */
    public $pi;

    /**
     * Whether a 9.x schema was detected in the database
     *
     * @var bool
     * @since 10.1.0
     */
    public bool $has9xSchema = false;

    /** @var array{views: int, plays: int, downloads: int, sessions: int} Last-30-day analytics KPIs for the analytics tab @since 10.1.0 */
    public array $anaKpi = ['views' => 0, 'plays' => 0, 'downloads' => 0, 'sessions' => 0];

    /**
     * Form
     *
     * @var Form
     * @since    7.0.0
     */
    protected $form;

    /**
     * Item
     *
     * @var object
     * @since    7.0.0
     */
    protected $item;

    /**
     * State
     *
     * @var Registry
     * @since    7.0.0
     */
    protected $state;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void  A string if successful, otherwise a Error object.
     *
     * @throws  \Exception
     * @since   11.1
     * @see     fetch()
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $app      = Factory::getApplication();
        $language = $app->getLanguage();
        $language->load('com_installer');

        // Get data from the model
        $this->form  = $this->get("Form");
        $this->item  = $this->get("Item");
        $this->state = $this->get("State");
        $this->canDo = ContentHelper::getActions('com_proclaim', 'cwmadmin', (int)$this->item->id);

        // End for a database
        $this->tmp_dest = $app->get('tmp_path');

        // Stats are now loaded via AJAX for better page load performance
        $this->playerstats = '';
        $this->assets      = $app->getInput()->get('checkassets', null, 'get');
        $this->popups      = '';

        // Analytics KPI (last 30 days) — loaded server-side to avoid spinner
        $today          = date('Y-m-d');
        $thirtyDaysAgo  = date('Y-m-d', strtotime('-29 days'));
        $this->anaKpi   = CwmanalyticsHelper::getKpiTotals($thirtyDaysAgo, $today);

        // Get the list of backup files
        $path = JPATH_SITE . '/media/com_proclaim/backup';

        if (file_exists($path)) {
            if (!$files = Folder::files($path, '.sql')) {
                $this->lists['backedupfiles'] = Text::_('JBS_CMN_NO_FILES_TO_DISPLAY');
            } else {
                asort($files, SORT_STRING);
                $filelist = [];

                foreach ($files as $value) {
                    $filelisttemp = ['value' => $value, 'text' => $value];
                    $filelist[]   = $filelisttemp;
                }

                $types[]                      = HTMLHelper::_('select.option', '0', Text::_('JBS_IBM_SELECT_DB'));
                $types                        = array_merge($types, $filelist);
                $this->lists['backedupfiles'] = HTMLHelper::_(
                    'select.genericlist',
                    $types,
                    'backuprestore',
                    'class="inputbox" size="1" ',
                    'value',
                    'text',
                    ''
                );
            }
        } else {
            $this->lists['backedupfiles'] = Text::_('JBS_CMN_NO_FILES_TO_DISPLAY');
        }

        // Check for SermonSpeaker and PreachIt - defaults first
        $this->ss = Text::_('JBS_IBM_NO_SERMON_SPEAKER_FOUND');
        $this->pi = Text::_('JBS_IBM_NO_PREACHIT_FOUND');

        $extensions = $this->get('SSorPI');

        foreach ($extensions as $extension) {
            if ($extension->element === 'com_sermonspeaker') {
                $this->ss = '<a href="index.php?option=com_proclaim&view=cwmadmin&task=cwmadmin.convertSermonSpeaker">'
                    . Text::_('JBS_IBM_CONVERT_SERMON_SPEAKER') . '</a>';
            }

            if ($extension->element === 'com_preachit') {
                $this->pi = '<a href="index.php?option=com_proclaim&view=cwmadmin&task=cwmadmin.convertPreachIt">'
                    . Text::_('JBS_IBM_CONVERT_PREACH_IT') . '</a>';
            }
        }

        // Detect 9.x schema for upgrade wizard tab
        $this->has9xSchema = CwmupgradeHelper::detect9xSchema()['detected'];

        // Get cached version to avoid parsing XML on every request
        $this->version = self::getComponentVersion();

        $this->setLayout('edit');

        // Set the toolbar
        $this->addToolbar();

        // Display the template
        parent::display($tpl);
    }

    /**
     * Add Toolbar
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $toolbar    = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('JBS_CMN_ADMINISTRATION'), 'options');
        $toolbar->preferences('com_proclaim', 'JBS_ADM_PERMISSIONS');
        ToolbarHelper::divider();
        $toolbar->apply('cwmadmin.apply');
        $toolbar->save('cwmadmin.save');
        $toolbar->cancel('cwmadmin.cancel');
        ToolbarHelper::divider();
        ToolbarHelper::custom('cwmadmin.resetHits', 'hits', 'Reset All Hits', 'JBS_ADM_RESET_ALL_HITS', false);
        ToolbarHelper::custom(
            'cwmadmin.resetDownloads',
            'download.png',
            'Reset All Download Hits',
            'JBS_ADM_RESET_ALL_DOWNLOAD_HITS',
            false
        );
        ToolbarHelper::custom('cwmadmin.resetPlays', 'play.png', 'Reset All Plays', 'JBS_ADM_RESET_ALL_PLAYS', false);

        $toolbar->divider();

        ToolbarHelper::inlinehelp();
        ToolbarHelper::help('admin', true);
    }

    /**
     * Get the component version with caching to avoid repeated XML parsing
     *
     * @return string The component version
     *
     * @since 9.0.0
     */
    private static function getComponentVersion(): string
    {
        if (self::$cachedVersion !== null) {
            return self::$cachedVersion;
        }

        $xmlFile = JPATH_ADMINISTRATOR . '/components/com_proclaim/proclaim.xml';

        if (!is_file($xmlFile)) {
            self::$cachedVersion = '';
            return self::$cachedVersion;
        }

        $jbsversion          = Installer::parseXMLInstallFile($xmlFile);
        self::$cachedVersion = $jbsversion['version'] ?? '';

        return self::$cachedVersion;
    }
}
