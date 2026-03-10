<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Session\Session;

/**
 * Upload Field class
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class UploadField extends FormField
{
    /**
     * @var string
     * @since 9.0.0
     */
    public $type = 'Uploads';

    /**
     * Get Input
     *
     * @return string
     *
     * @throws \Exception
     * @since 9.0.0
     */
    #[\Override]
    protected function getInput(): string
    {
        $document = Factory::getApplication()->getDocument();
        $wa       = $document->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_proclaim');

        $handler = $this->getAttribute('handler');
        $token   = Session::getFormToken();

        $wa->addInlineScript(
            'document.addEventListener("DOMContentLoaded", function() {
                if (typeof uploader === "undefined") { return; }
                uploader.setOption("url", "index.php?option=com_proclaim&task=cwmmediafile.xhr&' . $token . '=1");
                uploader.bind("BeforeUpload", function() {
                    var pathEl = document.getElementById("jform_params_localFolder");
                    var typeEl = document.getElementById("jform_serverType");
                    uploader.setOption("multipart_params", {
                        handler: "' . $handler . '",
                        path: pathEl ? pathEl.value : "",
                        type: typeEl ? typeEl.value : ""
                    });
                });
                uploader.init();
            });'
        );

        $class    = $this->getAttribute('class') ? (string) $this->getAttribute('class') : '';
        $required = 'requires="' . $this->getAttribute('required') . '"';

        return '<div class="mb-3">
                    <div class="input-group">
                        <input type="text" placeholder="Enter the upload path" class="' . $class . '" name="' . $this->name .
            '" id="' . $this->id . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $required . '/>
                        <input id="uploader-file" placeholder="Choose a media file" style="border-left: 0; border-radius: 0;" class="col-7" type="text" disabled>
                        <a id="btn-upload" class="btn btn-success" disabled>
                            <i class="icon-upload"></i>
                            Upload
                        </a>
                        <a id="btn-add-file" class="btn btn-default">
                            <i class="icon-plus"></i>
                            Add File
                        </a>
                    </div>
                    <div id="upload-progress" style="display: none; margin-top: 5px;" class="progress progress-striped active">
                        <div class="bar" style="width: 0;"></div>
                    </div>
                </div>';
    }
}
