<?php

defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldPlupload extends JFormField
{

    public $type = 'Plupload';

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
                    uploader.setOption("multipart_params", {
                        handler: "'.$this->element["handler"].'",
                        path: "'.$this->value.'"
                    });
                });
                uploader.init();
            });
        ');

        $class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

        $html = '<div class="control-group">
                        <div class="input-append">
                        <input type="text" placeholder="Enter a filename" '.$class.' name="' . $this->name . '" id="' . $this->id . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />
                          <input id="uploader-file" placeholder="Choose a media file" style="border-left: 0px; border-radius: 0; class="span7" type="text" disabled>
                          <a id="btn-add-file"class="btn btn-default">
                             <i class="icon-plus"></i>
                             Add File
                          </a>
                          <a id="btn-upload" class="btn btn-success" disabled>
                             <i class="icon-upload"></i>
                             Upload
                          </a>
                        </div>
                        <div id="upload-progress" style="display: none; margin-top: 5px;" class="progress progress-striped active"><div class="bar" style="width: 0%;"></div></div>
                    </div>';
        $html .= '
            ';

        return $html;
    }
}