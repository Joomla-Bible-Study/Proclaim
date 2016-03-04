<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Upload Field class
 *
 * @package  BibleStudy.Admin
 * @since    9.0.0
 */
class JFormFieldUpload extends JFormField
{

	public $type = 'upload';

	/**
	 * Get Input
	 *
	 * @return string
	 */
	protected function getInput()
	{
		// Include Plupload libraries
		$document = JFactory::getDocument();
		$document->addScript(JURI::root() . 'administrator/components/com_biblestudy/addons/servers/legacy/includes/js/plupload.full.min.js');

		$document->addScript(JURI::root() . 'administrator/components/com_biblestudy/addons/servers/legacy/includes/js/legacy.js');

		$document->addScriptDeclaration('
            jQuery(document).ready(function() {
                uploader.setOption("url", "index.php?option=com_biblestudy&task=mediafile.xhr&' . JSession::getFormToken() . '=1");
                uploader.bind("BeforeUpload", function() {
                    var path = jQuery("#jform_params_localFolder").val();
                    var type = jQuery("#jform_serverType").val();
                    uploader.setOption("multipart_params", {
                        handler: "' . $this->getAttribute("handler") . '",
                        path: path,
                        type: type
                    });
                });
                uploader.init();
            });
        ');

		$class = $this->getAttribute('class') ? (string) $this->getAttribute('class') : '';

		$required = 'requires="' . $this->getAttribute('required') . '"';

		$html = '<div class="control-group">
                        <div class="input-append">
                        <input type="text" placeholder="Enter the upload path" class="' . $class . '" name="' . $this->name .
			'" id="' . $this->id . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $required . '/>
			<input id="uploader-file" placeholder="Choose a media file" style="border-left: 0; border-radius: 0;" class="span7" type="text" disabled>
                          <a id="btn-add-file"class="btn btn-default">
                             <i class="icon-plus"></i>
                             Add File
                          </a>
                          <a id="btn-upload" class="btn btn-success" disabled>
                             <i class="icon-upload"></i>
                             Upload
                          </a>
                        </div>
                        <div id="upload-progress" style="display: none; margin-top: 5px;" class="progress progress-striped active">
                        <div class="bar" style="width: 0;">
                        </div>
                        </div>
                    </div>';
		$html .= '
            ';

		return $html;
	}
}
