<?php
/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMCpanel;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Lib\CWMStats;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use SimpleXMLElement;

defined('_JEXEC') or die;

/**
 * JView class for Cpanel
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HTMLView extends BaseHtmlView
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
	 * State
	 *
	 * @var mixed
	 * @since    7.0.0
	 */
	protected $state;

	/**
	 * Post Installation Messages
	 *
	 * @var    boolean
	 * @since  7.0.0
	 */
	protected bool $hasPostInstallationMessages;

	/**
	 * Extension ID
	 *
	 * @var    integer
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
		$this->state = $this->get('State');
		/** @var \CWM\Component\Proclaim\Administrator\Model\CWMCpanelModel $model */
		$model     = $this->getModel();
		$component = JPATH_ADMINISTRATOR . '/components/com_proclaim/proclaim.xml';

		if (file_exists($component))
		{
			$this->xml = simplexml_load_string(file_get_contents($component));
		}

		$this->total_messages = CWMStats::get_total_messages();

		$this->hasPostInstallationMessages = $model->hasPostInstallMessages();
		$this->extension_id                = (int) $this->state->get('extension_id', 0, 'int');

		// Set the document
		$this->setDocument();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 * @throws \Exception
	 * @since    7.1.0
	 *
	 */
	protected function setDocument(): void
	{
		$this->document->setTitle(Text::_('JBS_TITLE_CONTROL_PANEL'));
	}
}
