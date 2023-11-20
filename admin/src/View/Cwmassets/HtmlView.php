<?php
/**
 * Assets html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMAssets;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for Admin
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /** @var integer Total numbers of Steps
     *
     * @since 9.0.0
     */
    public $totalSteps = 0;

    /** @var integer Numbers of Steps already processed
     *
     * @since 9.0.0
     */
    public $doneSteps = 0;

    /** @var array Call stack for the Visioning System.
     *
     * @since 9.0.0
     */
    public $callstack = array();

    public $version;

    public $step;

    public $assets;
    /** @var object Start of install
     *
     * @since 9.0.0
     */
    public $state;
    /** @var object Status
     *
     * @since 9.0.0
     */
    public $status;
    /** @var boolean More
     *
     * @since 9.0.0
     */
    protected bool $more = false;
    /** @var  string Percentage
     *
     * @since 9.0.0
     */
    protected $percentage;
    /** @var array The pre versions to process
     *
     * @since 9.0.0
     */
    private $versionStack = array();

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void  A string if successful, otherwise a JError object.
     *
     * @throws  \Exception
     * @since   11.1
     * @see     fetch()
     */
    public function display($tpl = null)
    {
        $app             = Factory::getApplication();
        $this->scanstate = $app->input->get('scanstate', false);
        // Get data from the model
        $this->state = $this->get("State");
        $layout      = $app->input->get('layout', 'edit');
        $task        = $app->input->get('task', 'checkassets');

        $session      = $app->getSession();
        $this->assets = $session->get('checkassets', null, 'CWM');
        $stack        = $session->get('asset_stack', '', 'CWM');

        if (empty($stack)) {
            $this->versionStack = array();
            $this->step         = null;
            $this->totalSteps   = 0;
            $this->doneSteps    = 0;
        } else {
            if (function_exists('base64_encode') && function_exists('base64_decode')) {
                $stack = base64_decode($stack);

                if (function_exists('gzdeflate') && function_exists('gzinflate')) {
                    $stack = gzinflate($stack);
                }
            }

            $stack = json_decode($stack, true, 512, JSON_THROW_ON_ERROR);

            $this->versionStack = $stack['version'];
            $this->step         = $stack['step'];
            $this->totalSteps   = $stack['total'];
            $this->doneSteps    = $stack['done'];
        }

        $percent = 0;

        if ($this->scanstate) {
            if ($this->totalSteps > 0) {
                $percent = min(max(round(100 * $this->doneSteps / $this->totalSteps), 1), 100);
            }

            $more = true;
        } else {
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

        if ($task === 'browse' || $task === 'run') {
            $this->setLayout('fix');
        } else {
            $this->setLayout('edit');
        }

        // Set the toolbar
        $this->addToolbar();

        $this->setDocumentTitle(Text::_('JBS_TITLE_ADMINISTRATION'));

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
    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        ToolbarHelper::title(Text::_('JBS_CMN_ADMINISTRATION'), 'administration');
        ToolbarHelper::custom('cwmadmin.back', 'home', 'home', 'JTOOLBAR_BACK', false);

        ToolbarHelper::custom('cwmassets.checkassets', 'refresh', 'refresh', 'JBS_ADM_CHECK_ASSETS', false);

        ToolbarHelper::custom('cwmassets.browse', 'fix', 'fix', 'JBS_ADM_FIX', false);

        ToolbarHelper::help('proclaim', true);
    }
}
