<?php

/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmcpanel;

use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use CWM\Component\Proclaim\Administrator\Model\CwmcpanelModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;

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
     * @var      \SimpleXMLElement|false|null
     * @since    7.0.0
     */
    public \SimpleXMLElement|false|null $xml = null;

    /**
     * Total Messages
     *
     * @var      string
     * @since    7.0.0
     */
    public string $total_messages;

    /**
     * The model state
     *
     * @var      ?Registry
     * @since    10.0.0
     */
    protected ?Registry $state = null;

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
     * @return  void  A string if successful, otherwise an Error object.
     *
     * @throws \Exception
     * @since    7.0.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmcpanelModel $model */
        $model       = Factory::getApplication()->bootComponent('com_proclaim')
            ->getMVCFactory()->createModel('Cwmcpanel', 'Administrator');
        $component   = JPATH_ADMINISTRATOR . '/components/com_proclaim/proclaim.xml';
        $model->setUseExceptions(true);
        $this->state = $model->getState();

        if (file_exists($component)) {
            $this->xml = simplexml_load_string(file_get_contents($component));
        }

        $this->total_messages = Cwmstats::getTotalMessages();

        $this->hasPostInstallationMessages = $model->hasPostInstallMessages();
        $this->extension_id                = ComponentHelper::getComponent('com_proclaim')->id;

        // Display the template
        parent::display($tpl);
    }
}
