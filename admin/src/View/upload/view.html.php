<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No direct access
defined('_JEXEC') or die();

// Include dependencies
JLoader::register('UploadScript', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/CWMUploadScript.php');

/**
 * Mediamu View
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class BiblestudyViewUpload extends JViewLegacy
{
	public $enableLog;

	public $runtime;

	public $currentDir;

	/**
	 * Form
	 *
	 * @var object
	 * @since 7.0
	 */
	protected $form;

	/**
	 * View display method
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get("Form");
		JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html/');
		JHtml::_('jquery.framework', 'false');

		$mediaDir      = JUri::root() . "media/com_biblestudy/plupload/";
		$document      = JFactory::getDocument();
		$params        = JComponentHelper::getParams('com_biblestudy');
		$UploadScript  = new UploadScript($params, $mediaDir);
		$runtimeScript = $UploadScript->runtimeScript;
		$runtime       = $UploadScript->runtime;

		// Add plupload styles and scripts
		$document->addScript($mediaDir . 'js/plupload.js');
		$document->addScript($mediaDir . 'js/plupload.browserplus.js');
		$document->addScript($mediaDir . 'js/plupload.full.js');
		$document->addScript($mediaDir . 'js/jquery.plupload.queue/jquery.plupload.queue.js');
		$document->addScript('http://bp.yahooapis.com/2.4.21/browserplus-min.js');
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
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_UPLOAD_FORM'));
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
		JToolbarHelper::title(JText::_('JBS_TITLE_UPLOAD_FORM'), 'mp3.png');
	}
}
