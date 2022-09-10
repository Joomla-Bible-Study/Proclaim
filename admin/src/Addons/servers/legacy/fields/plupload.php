<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormField;
defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Class JFormFieldPlupload
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class JFormFieldPlupload extends FormField
{
	public $type = 'Plupload';

	/**
	 * Get Input
	 *
	 * @return string
	 *
	 * @since 1.5
	 * @throws \Exception
	 */
	protected function getInput()
	{
		// Include Plupload libraries
		$document = Factory::getApplication()->getDocument();
		$app = Factory::getApplication();
		$document->addScript(JUri::root() . 'administrator/components/com_proclaim/src/addons/servers/legacy/includes/js/plupload.full.min.js');
		$document->addScript(JUri::root() . 'administrator/components/com_proclaim/src/addons/servers/legacy/includes/js/legacy.js');
		$view = $app->input->get('view');
		$admin = CWMParams::getAdmin();

		if (isset($this->form->s_params['uploadpath']))
		{
			$upload = $this->form->s_params['uploadpath'];
		}
		else
		{
			$upload = $admin->params->get('uploadpath', '/images/biblestudy/media/');
		}

		$document->addScriptDeclaration('
			jQuery(document).ready(function() {
				uploader.setOption("url", "index.php?option=com_proclaim&task=' . $view . '.xhr&' . Session::getFormToken() . '=1");
				uploader.bind("BeforeUpload", function() {
					uploader.setOption("multipart_params", {
						handler: "' . $this->element["handler"] . '",
						path: "' . $upload . '"
					});
				});
				uploader.init();
			});
		');

		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . ' span12"' : '';

		$html = ' <input type="text" placeholder="Enter a filename" ' . $class . ' name="' . $this->name . '" id="' .
			$this->id . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/><br />
                          <input id="uploader-file" placeholder="Choose a media file"
                          style="border-left: 0; border-radius: 0;" class="span7" type="text" disabled>
                          <a id="btn-add-file" href="javascript:;" class="btn btn-default">
                             <i class="icon-plus"></i>
                             Add File
                          </a>
                          <a id="btn-upload" href="javascript:;" class="btn btn-success" disabled>
                             <i class="icon-upload"></i>
                             Upload
                          </a>
                        <div id="upload-progress" style="display: none; margin-top: 5px; margin-bottom: 0;" 
                        class="progress progress-striped active">
                        <div class="bar" style="width: 0;"></div></div><br />
						<pre id="console" class="hidden"></pre>';
		$html .= '
            ';

		return $html;
	}
}
