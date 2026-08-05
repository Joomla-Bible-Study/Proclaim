<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * Class UploadScript
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class Cwmuploadscript
{
    public mixed $runtimeScript;
    public mixed $runtime;

    /**
     * @var string
     * @since 9.0.0
     */
    protected string $folder = '';

    /**
     * Upload media files
     *
     * Reached through CwmmediafileController::xhr(), which passes the request
     * Input object straight through to the addon's upload(). The declared type
     * was previously an untyped `$data` documented as `Input|array`, while the
     * body only ever used the Input API -- and the addon overrides declared
     * `?array`, so every call raised a TypeError before this method was even
     * entered. Typed to what is actually passed and actually used. See #1564.
     *
     * @param   Input  $data  Request input carrying the upload target path
     *
     * @return array
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function upload(Input $data): array
    {
        Session::checkToken('get') or die('Invalid Token');
        $params = ComponentHelper::getParams('com_proclaim');

        // Get some data from the request
        $file         = $_FILES['file'];
        $this->folder = $data->get('path', '', 'path');

        // Total length of post back data in bytes.
        $contentLength = (int)$_SERVER['CONTENT_LENGTH'];

        // Instantiate the media helper
        $mediaHelper = new MediaHelper();

        // Maximum allowed size of post back data in MB.
        $postMaxSize = $mediaHelper->toBytes(\ini_get('post_max_size'));

        // Maximum allowed size of script execution in MB.
        $memoryLimit = $mediaHelper->toBytes(\ini_get('memory_limit'));

        // Check for the total size of post-back data.
        if (
            ($postMaxSize > 0 && $contentLength > $postMaxSize)
            || ($memoryLimit != -1 && $contentLength > $memoryLimit)
        ) {
            return ['data' => '', 'error' => ''];
        }

        $uploadMaxSize     = $params->get('upload_maxsize', 0) * 1024 * 1024;
        $uploadMaxFileSize = $mediaHelper->toBytes(\ini_get('upload_max_filesize'));

        $file['name']     = File::makeSafe($file['name']);
        $file['name']     = str_replace(' ', '-', $file['name']);
        $file['filepath'] = Path::clean(implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, $this->folder, $file['name']]));

        if (
            ($file['error'] == 1)
            || ($uploadMaxSize > 0 && $file['size'] > $uploadMaxSize)
            || ($uploadMaxFileSize > 0 && $file['size'] > $uploadMaxFileSize)
        ) {
            // File size exceed either 'upload_max_filesize' or 'upload_maxsize'.
            return ['data' => '', 'error' => 'File size exceed either \'upload_max_filesize\' or \'upload_maxsize\''];
        }

        if (!isset($file['name'])) {
            // No filename (after the name was cleaned by JFile::makeSafe)
            return ['data' => '', 'error' => 'No filename'];
        }

        // Set FTP credentials, if given
        ClientHelper::setCredentialsFromRequest('ftp');
        PluginHelper::importPlugin('content');
        $app = Factory::getApplication();

        self::applyConfiguredExtensions($params);

        if (!$mediaHelper->canUpload($file, 'com_proclaim')) {
            // The file can't be uploaded
            return ['data' => '', 'error' => 'The file can\'t be uploaded by types allowed'];
        }

        // Trigger the onContentBeforeSave event.
        $object_file = (object)$file;
        $result      = $app->triggerEvent('onContentBeforeSave', ['com_proclaim.file', &$object_file, true]);

        if (\in_array(false, $result, true)) {
            // There are some errors in the plugins
            return ['data' => '', 'error' => 'Plugin errors on upload'];
        }

        if (!File::upload($object_file->tmp_name, $object_file->filepath)) {
            return ['data' => '', 'error' => 'Could not upload'];
        }

        // Trigger the onContentAfterSave event.
        $app->triggerEvent('onContentAfterSave', ['com_proclaim.file', &$object_file, true]);

        // Return Success
        return [
            'data' => [
                'filename' => $object_file->filepath,
                'size'     => $_FILES['file']['size'],
            ],
        ];
    }

    /**
     * Make the admin's configured "Legal Extensions" the list actually enforced.
     *
     * MediaHelper::canUpload() reads its extension allow-list from
     * `restrict_uploads_extensions` (com_media's key name), which
     * com_proclaim's config.xml never defines -- it defines `upload_extensions`
     * instead. Registry::get() therefore fell back to Joomla's hardcoded
     * default list every time, so the admin's setting was silently ignored:
     * adding e.g. `vtt` to "Legal Extensions" still got the upload rejected,
     * with no way to fix it from the UI.
     *
     * canUpload() re-reads the params internally via
     * ComponentHelper::getParams('com_proclaim'), which returns this same
     * cached Registry instance, so setting the key here is observed by it.
     *
     * Copied only when non-empty -- an empty list would make canUpload()
     * reject every file, which is worse than falling back to Joomla's
     * defaults (the pre-fix behaviour).
     *
     * @param   Registry  $params  com_proclaim's params (the shared instance)
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    protected static function applyConfiguredExtensions(Registry $params): void
    {
        $legalExtensions = trim((string) $params->get('upload_extensions', ''));

        if ($legalExtensions !== '') {
            $params->set('restrict_uploads_extensions', $legalExtensions);
        }
    }

    /**
     * Upload media files
     *
     * @return string
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function UIScript(): string
    {
        return '';
    }
}
