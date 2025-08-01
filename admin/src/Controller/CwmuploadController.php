<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

use JetBrains\PhpStorm\NoReturn;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;
use JsonException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class CwmuploadController
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class CwmuploadController extends AdminController
{
    /**
     * File upload handler
     * Controller adapted from COM_MEDIAMU
     *
     * @return void JSON response
     *
     * @throws JsonException
     * @since 9.0
     */
    public function upload(): void
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $contentType = null;

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Enable valid json response when debugging is disabled
        if (!JBSMDEBUG) {
            error_reporting(0);
        }

        $input   = new Input();
        $params  = ComponentHelper::getParams('com_proclaim');
        $app     = Factory::getApplication();
        $session = $app->getSession();
        $user    = $app->getIdentity();

        // Remove old files
        $cleanupTargetDir = true;

        // Temp file age in seconds
        $maxFileAge = 5 * 3600;

        // Directory for file upload
        $targetDirBase64  = $session->get('current_dir', null, 'com_proclaim');
        $targetDirDecoded = base64_decode($targetDirBase64);
        $targetDirWithSep = $targetDirDecoded . DIRECTORY_SEPARATOR;

        // Check for snooping
        $targetDirCleaned = Path::check($targetDirWithSep);

        // Finally
        $targetDir = $targetDirCleaned;

        // Get parameters
        $chunk  = $input->getInt('chunk', 0);
        $chunks = $input->getInt('chunks', 0);

        // Current file name
        $fileNameFromReq = $input->getString('name', '');

        // Clean the fileName for security reasons
        $fileName = File::makeSafe($fileNameFromReq);

        // Check file extension
        $ext_images = $params->get('image_file_extensions', null);
        $ext_other  = $params->get('other_files_extension', null);

        // Prepare extensions for validation
        $exts     = $ext_images . ',' . $ext_other;
        $exts_lc  = strtolower($exts);
        $exts_arr = explode(',', $exts_lc);

        // Check token
        if (!$session->checkToken('request')) {
            $this->setResponse(400, Text::_('JINVALID_TOKEN'));
        }

        // Check user perms
        if (!$user->authorise('core.create', 'com_proclaim')) {
            $this->setResponse(400, Text::_('JBS_ERROR_PERM_DENIDED'));
        }

        // Directory check
        if (!file_exists($targetDir) && !is_dir($targetDir) && strpos(Uri::base(), $targetDir) !== false) {
            $this->setResponse(100, Text::_('JBS_ERROR_UPLOAD_INVALID_PATH'));
        }

        // File type check
        if (!in_array(File::getExt($fileName), $exts_arr, true)) {
            $this->setResponse(100, Text::_('JBS_ERROR_UPLOAD_INVALID_FILE_EXTENSION'));
        }

        // Make sure the fileName is unique but only if chunk is disabled
        if ($chunks < 2 && file_exists($targetDir . '/' . $fileName)) {
            $ext        = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;

            while (file_exists($targetDir . '/' . $fileName_a . '_' . $count . $fileName_b)) {
                $count++;
            }

            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        $filePath = $targetDir . '/' . $fileName;

        // Remove old temp files
        if ($cleanupTargetDir && ($dir = opendir($targetDir))) {
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . '/' . $file;

                // Remove temp file if it is older than the max age and is not the current file
                if (
                    preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time(
                    ) - $maxFileAge) && ($tmpfilePath !== "{$filePath}.part")
                ) {
                    File::delete($tmpfilePath);
                }
            }

            closedir($dir);
        } else {
            $this->setResponse(100, 'Failed to open temp directory.');
        }

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"])) {
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
        }

        if (isset($_SERVER["CONTENT_TYPE"])) {
            $contentType = $_SERVER["CONTENT_TYPE"];
        }

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (str_contains($contentType, "multipart")) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");

                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096)) {
                            fwrite($out, $buff);
                        }
                    } else {
                        $this->setResponse(101, "Failed to open input stream.");
                    }

                    fclose($in);
                    fclose($out);
                    File::delete($_FILES['file']['tmp_name']);
                } else {
                    $this->setResponse(102, "Failed to open output stream.");
                }
            } else {
                $this->setResponse(103, "Failed to move uploaded file");
            }
        } else {
            // Open temp file
            $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");

            if ($out) {
                // Read binary input stream and append it to temp file
                $in = fopen("php://input", "rb");

                if ($in) {
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                } else {
                    $this->setResponse(101, "Failed to open input stream.");
                }

                fclose($in);
                fclose($out);
            } else {
                $this->setResponse(102, "Failed to open output stream.");
            }
        }

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            @rename("{$filePath}.part", $filePath);
        }

        $this->setResponse(0, null, false);
    }

    /**
     * Set the JSON response and exists script
     *
     * @param   int      $code   Error Code
     * @param   ?string  $msg    Error Message
     * @param   ?bool    $error  ?
     *
     * @return void
     *
     * @throws JsonException
     * @since 9.0
     */
    #[NoReturn] private function setResponse(int $code, ?string $msg = null, ?bool $error = true): void
    {
        if ($error) {
            $jsonrpc = array(
                "error" => 1,
                "code"  => $code,
                "msg"   => $msg
            );
        } else {
            $jsonrpc = array(
                "error" => 0,
                "code"  => $code,
                "msg"   => "File uploaded!"
            );
        }

        die(json_encode($jsonrpc, JSON_THROW_ON_ERROR));
    }
}
