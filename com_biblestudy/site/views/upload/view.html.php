<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

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
        $UploadScript          = new UploadScript($params, $mediaDir);
        $runtimeScript		= $UploadScript->runtimeScript;
        $runtime                = $UploadScript->runtime;

        //add default mediamu css
        $document->addStyleSheet($mediaDir . 'css/com_mediamu.css');

        //add plupload styles and scripts
        $document->addStyleSheet($mediaDir . 'js/jquery.plupload.queue/css/jquery.plupload.queue.css', 'text/css', 'screen');
        $document->addScript($mediaDir . 'js/jquery.min.js');
        $document->addScript($mediaDir . 'js/browserplus-min.js');
        $document->addScript($mediaDir . 'js/plupload.js');
        $document->addScript($mediaDir . 'js/plupload.' . $runtimeScript . '.js');
        $document->addScript($mediaDir . 'js/jquery.plupload.queue/jquery.plupload.queue.js');
        $document->addScriptDeclaration( $UploadScript->getScript() );

        //set variables for the template
        $this->enableLog = $params->get('enable_uploader_log', 0);
        $this->runtime = $runtime;
        $this->currentDir = $this->get('CurrentDir');


        // Display the template
        parent::display($tpl);
    }


}