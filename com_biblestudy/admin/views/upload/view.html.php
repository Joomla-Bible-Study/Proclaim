<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No direct access
defined('_JEXEC') or die('Restricted access');

// Include dependencies
JLoader::register('UploadScript', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/uploadscript.php');

// Import Joomla view library
jimport('joomla.application.component.view');

/**
 * Mediamu View
 *
 * @package  BibleStudy.Admin
 * @since    8.1.0
 */
class BiblestudyViewUpload extends JViewLegacy
{
	/**
	 * View display method
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		JHtml::_('jquery.framework', 'false');
		JHtml::_('bootstrap.framework');
		JHtml::_('behavior.tooltip');
		$mediaDir      = JURI::root() . "media/com_biblestudy/plupload/";
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

		//$document->addScriptDeclaration( $UploadScript->getScript() );
		//$document->addScriptDeclaration( $UploadScript->newScript() );
		$document->addScriptDeclaration($UploadScript->UIScript());

		//print_r($UploadScript->newScript());
		// Set variables for the template
		$this->enableLog  = 1;
		$this->runtime    = $runtime;
		$this->currentDir = '/media';


		// Display the template
		parent::display($tpl);
	}

}
