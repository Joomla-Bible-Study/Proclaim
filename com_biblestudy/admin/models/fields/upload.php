<?php

defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldUpload extends JFormField
{

    public $type = 'upload';

    protected function getInput()
    {
        // Include Plupload libraries
        $document = JFactory::getDocument();
        $document->addScript(JURI::root() . 'administrator/components/com_biblestudy/addons/servers/legacy/includes/js/plupload.full.min.js');

        $document->addScript(JURI::root() . 'administrator/components/com_biblestudy/addons/servers/legacy/includes/js/legacy.js');

        $document->addScriptDeclaration('
            jQuery(document).ready(function() {
                uploader.setOption("url", "index.php?option=com_biblestudy&task=mediafile.xhr&'.JSession::getFormToken().'=1");
                uploader.bind("BeforeUpload", function() {
                    var path = jQuery("#jform_params_localFolder").val();
                    var type = jQuery("#jform_serverType").val();
                    uploader.setOption("multipart_params", {
                        handler: "'.$this->element["handler"].'",
                        path: path,
                        type: type
                    });
                });
                uploader.init();
            });
        ');

        $html = '<div class="control-group">
                        <div class="input-append">
                        <input type="text" placeholder="Enter the upload path" class="span5" name="' . $this->name . '" id="' . $this->id . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />
                          <input id="uploader-file" placeholder="Choose a media file" style="border-left: 0; border-radius: 0; class="span7" type="text" disabled>
                          <a id="btn-add-file"class="btn btn-default">
                             <i class="icon-plus"></i>
                             Add File
                          </a>
                          <a id="btn-upload" class="btn btn-success" disabled>
                             <i class="icon-upload"></i>
                             Upload
                          </a>
                        </div>
                        <div id="upload-progress" style="display: none; margin-top: 5px;" class="progress progress-striped active"><div class="bar" style="width: 0;"></div></div>
                    </div>';
        $html .= '
            ';

        return $html;
    }
}
