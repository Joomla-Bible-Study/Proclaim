<?php

// no direct access
defined('_JEXEC') or die('Restricted access');
// Include dependencies
JLoader::register('UploadScript', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/uploadscript.php');
// import Joomla view library
jimport('joomla.application.component.view');

/**
 * Mediamu View
 */
class BiblestudyViewUpload extends JViewLegacy
{
    /**
     * view display method
     * @return void
     */

    function display($tpl = null)
    {
        //JHtml::_('jquery.framework', 'false');
        //JHtml::_('bootstrap.framework');
        $mediaDir		= JURI::root() . "media/com_biblestudy/plupload/";
        $document 		= JFactory::getDocument();
        $params 		= JComponentHelper::getParams('com_biblestudy');
        $UploadScript   = new UploadScript($params, $mediaDir);
        $runtimeScript  = $UploadScript->runtimeScript;
        $runtime        = $UploadScript->runtime;
//echo $runtime;

        //add plupload styles and scripts
        $document = JFactory::getDocument();
        //JHtml::_('jquery.framework');
        //JHtml::_('behavior.tooltip');
        $document->addScript('https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js');
        $document->addScript('http://bp.yahooapis.com/2.4.21/browserplus-min.js');
        $document->addStyleSheet($mediaDir . 'js/jquery.plupload.queue/css/jquery.plupload.queue.css', 'text/css', 'screen');
        $document->addScript($mediaDir . 'js/plupload.full.min.js');
        //$document->addScript($mediaDir . 'js/jquery.plupload.queue/jquery.plupload.queue.js');
        //$document->addScript($mediaDir . 'js/plupload.js');
        //$document->addScript($mediaDir . 'js/plupload.'.$runtimeScript.'.js');
        //$document->addScript($mediaDir . 'js/jquery.plupload.queue/jquery.plupload.queue.js');

        //$document->addScriptDeclaration( $UploadScript->getScript() );
        //$document->addScriptDeclaration( $UploadScript->newScript() );
        $document->addScriptDeclaration( $UploadScript->UIScript() );

//print_r($UploadScript->newScript());
        //set variables for the template
        $this->enableLog = 1;
        $this->runtime = $runtime;
        $this->currentDir = '/media';


        // Display the template
        parent::display($tpl);
    }


}