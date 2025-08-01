<?php

/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMCpanel;

use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use CWM\Component\Proclaim\Administrator\Model\CwmcpanelModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use SimpleXMLElement;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HtmlView class for Cpanel
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Data from Model
     *
     * @var SimpleXMLElement|false
     * @since    7.0.0
     */
    public $xml;

    /**
     * Total Messages
     *
     * @var string
     * @since    7.0.0
     */
    public string $total_messages;

    /**
     * The model state
     *
     * @var   \Joomla\CMS\Object\CMSObject
     * @since    10.0.0
     */
    protected $state;

    /**
     * Post Installation Messages
     *
     * @var    bool
     * @since  7.0.0
     */
    protected bool $hasPostInstallationMessages;

    /**
     * Extension ID
     *
     * @var    int
     * @since  7.0.0
     */
    protected int $extension_id;

    /**
     * Display
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void  A string if successful, otherwise a Error object.
     *
     * @throws \Exception
     * @since    7.0.0
     */
    public function display($tpl = null): void
    {
        $model       = new CwmcpanelModel();
        $component   = JPATH_ADMINISTRATOR . '/components/com_proclaim/proclaim.xml';
        $this->state = $this->get('State');

        if (file_exists($component)) {
            $this->xml = simplexml_load_string(file_get_contents($component));
        }

        $this->total_messages = Cwmstats::getTotalMessages();

        $this->hasPostInstallationMessages = $model->hasPostInstallMessages();
        $this->extension_id                = (int)$this->state->get('extension_id', 0, 'int');

        // Display the template
        parent::display($tpl);
    }
}
