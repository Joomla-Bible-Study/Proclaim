<?php

/**
 * @author Tom Fuller
 * @copyright 2012
 */

/**
	 * Method to load javascript for squeezebox modal
	 *
	 * $param string $host the site base url
	 *
	 * @return	string
	 */
class JBSUpload{
    
    function uploadjs($host)
    {
    //when we send the files for upload, we have to tell Joomla our session, or we will get logged out 
    $session = & JFactory::getSession();
    
    $val = ini_get('upload_max_filesize');
    $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
    $valk = $val/1024;
    $valm = $valk/1024;
    $maxupload = $valm. ' MB';
    
    $swfUploadHeadJs ='
    var swfu;
     
    window.onload = function()
    {
     
    var settings = 
    {
            //this is the path to the flash file, you need to put your components name into it
            flash_url : "'.$host.'components/com_preachit/assets/swfupload/swfupload.swf",
     
            //we can not put any vars into the url for complicated reasons, but we can put them into the post...
            upload_url: "'.$host.'index.php",
            post_params: {
            		"option" : "com_preachit",
           		"controller" : "studyedit",
            		"task" : "upflash",
            		"'.$session->getName().'" : "'.$session->getId().'",
           		"format" : "raw"
           	},
            //you need to put the session and the "format raw" in there, the other ones are what you would normally put in the url
            file_size_limit : "'.$maxupload.'",
            //client side file checking is for usability only, you need to check server side for security
            file_types : "",
            file_types_description : "All Files",
            file_upload_limit : 100,
            file_queue_limit : 10,
            custom_settings : 
            {
                    progressTarget : "fsUploadProgress",
                    cancelButtonId : "btnCancel"
            },
            debug: false,
     
            // Button settings
            button_image_url: "'.$host.'components/com_preachit/assets/swfupload/images/uploadbutton.png",
            button_width: "86",
            button_height: "33",
            button_placeholder_id: "spanButtonPlaceHolder",
            button_text: \'<span class="upbutton">'.JText::_('COM_PREACHIT_ADMIN_BUTTON_BROWSE').'</span>\',
            button_text_style: ".upbutton { font-size: 14px; margin-left: 15px;}",
            button_text_left_padding: 5,
            button_text_top_padding: 5,
     
            // The event handler functions are defined in handlers.js
            file_queued_handler : fileQueued,
            file_queue_error_handler : fileQueueError,
            file_dialog_complete_handler : fileDialogComplete,
            upload_start_handler : uploadStart,
            upload_progress_handler : uploadProgress,
            upload_error_handler : uploadError,
            upload_success_handler : uploadSuccess,
            upload_complete_handler : uploadComplete,
            queue_complete_handler : queueComplete     // Queue plugin event
    };
    swfu = new SWFUpload(settings);
    };
     
    ';
     
    return $swfUploadHeadJs;
    }

}
?>