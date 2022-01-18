<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMMessageTypes;

// No direct access
use CWM\Component\Proclaim\Administrator\Helper\CWMUploadScript;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die();

/**
 * Mediamu View
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class HTMLView extends BaseHtmlView
{
	public $enableLog;

	public $runtime;

	public $currentDir;

	/**
	 * Form
	 *
	 * @var mixed
	 * @since 7.0
	 */
	protected mixed $form;

	/**
	 * View display method
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get("Form");
		HTMLHelper::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html/');
		HTMLHelper::_('jquery.framework', 'false');

		$mediaDir      = Uri::root() . "media/com_proclaim/plupload/";
		$document      = Factory::getApplication()->getDocument();
		$params        = ComponentHelper::getParams('com_proclaim');
		$UploadScript  = new CWMUploadScript($params, $mediaDir);
		$runtimeScript = $UploadScript->runtimeScript;
		$runtime       = $UploadScript->runtime;

		// Add plupload styles and scripts
		$document->addScript($mediaDir . 'js/plupload.js');
		$document->addScript($mediaDir . 'js/plupload.browserplus.js');
		$document->addScript($mediaDir . 'js/plupload.full.js');
		$document->addScript($mediaDir . 'js/jquery.plupload.queue/jquery.plupload.queue.js');
		$document->addScript('https://bp.yahooapis.com/2.4.21/browserplus-min.js');
		$document->addStyleSheet($mediaDir . 'js/jquery.plupload.queue/css/jquery.plupload.queue.css', 'text/css', 'screen');
		$document->addScriptDeclaration($UploadScript->UIScript());

		// Set variables for the template
		$this->enableLog  = 1;
		$this->runtime    = $runtime;
		$this->currentDir = '/media';

		// Set the document
		$this->setDocument();

		// Set the toolbar
		$this->addToolbar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(Text::_('JBS_TITLE_UPLOAD_FORM'));
	}

	/**
	 * Add Toolbar
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('JBS_TITLE_UPLOAD_FORM'), 'mp3.png');
	}
}
