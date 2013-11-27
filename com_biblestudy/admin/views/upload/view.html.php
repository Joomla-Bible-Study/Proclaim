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
        $mediaDir		= JURI::root() . "media/com_biblestudy/plupload/";
        $document 		= JFactory::getDocument();
        $params 		= JComponentHelper::getParams('com_biblestudy');
        $UploadScript   = new UploadScript($params, $mediaDir);
        $runtimeScript  = $UploadScript->runtimeScript;
        $runtime        = $UploadScript->runtime;
//echo $runtime;

        //add plupload styles and scripts
        $document->addStyleSheet($mediaDir . 'js/jquery.plupload.queue/css/jquery.plupload.queue.css', 'text/css', 'screen');
        $document->addScript($mediaDir . 'js/jquery.min.js');
        $document->addScript($mediaDir . 'js/browserplus-min.js');
        $document->addScript($mediaDir . 'js/plupload.js');
        $document->addScript($mediaDir . 'js/plupload.'.$runtimeScript.'.js');
        $document->addScript($mediaDir . 'js/jquery.plupload.queue/jquery.plupload.queue.js');

        $document->addScriptDeclaration( $UploadScript->getScript() );
       // $document->addScriptDeclaration( $UploadScript->newScript() );

//print_r($UploadScript->newScript());
        //set variables for the template
        $this->enableLog = 1;
        $this->runtime = $runtime;
        $this->currentDir = '/media';


        // Display the template
        parent::display($tpl);
    }


}