<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Legacy\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/**
 * Class JFormFieldPlupload
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class PluploadField extends FormField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 9.0.0
     */
    public $type = 'Plupload';

    /**
     * Get Input
     *
     * @return string
     *
     * @throws \Exception
     * @since 1.5
     */
    protected function getInput(): string
    {
        // Include Plupload libraries
        $document = Factory::getApplication()->getDocument();
        $app      = Factory::getApplication();

        $document->addScript(
            Uri::root(
            ) . 'administrator/components/com_proclaim/src/Addons/Servers/Legacy/includes/js/plupload.full.min.js'
        );
        $document->addScript(
            Uri::root() . 'administrator/components/com_proclaim/src/Addons/Servers/Legacy/includes/js/legacy.js'
        );
        $view  = $app->input->get('view');
        $admin = Cwmparams::getAdmin();

        if (isset($this->form->s_params['uploadpath'])) {
            $upload = $this->form->s_params['uploadpath'];
        } else {
            $upload = $admin->params->get('uploadpath', '/images/biblestudy/media/');
        }

        $document->addScriptDeclaration(
            '
			jQuery(document).ready(function() {
				uploader.setOption("url", "index.php?option=com_proclaim&task=' . $view . '.xhr&' . Session::getFormToken(
            ) . '=1");
				uploader.bind("BeforeUpload", function() {
					uploader.setOption("multipart_params", {
						handler: "' . $this->element["handler"] . '",
						path: "' . $upload . '"
					});
				});
				uploader.init();
			});'
        );

        $class = $this->element['class'] ? ' class="' . (string)$this->element['class'] . ' col-12"' : '';

        $html = ' <input type="text" placeholder="Enter a filename" ' . $class . ' name="' . $this->name . '" id="' .
            $this->id . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/><br />
                          <input id="uploader-file" placeholder="Choose a media file"
                          style="border-left: 0; border-radius: 0;" class="col-7" type="text" disabled>
                          <a id="browse" href="javascript:;" class="btn btn-default">
                             <i class="icon-plus"></i>
                             Add File
                          </a>
                          <a id="start-upload" href="javascript:;" class="btn btn-success" disabled>
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
