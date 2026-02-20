<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Local;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Helper\Cwmuploadscript;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

/**
 * Class CWMAddonLocal
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class CWMAddonLocal extends CWMAddon
{
    /**
     * Name of Add-on
     *
     * @var     string
     * @since   9.0.0
     */
    protected $name = 'local';

    /**
     * Description of add-on
     *
     * @var     string
     * @since   9.0.0
     */
    protected $description = 'Used for local server files';

    /**
     * Upload
     *
     * @param ?array $data  Data to upload
     *
     * @return array
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function upload(?array $data): array
    {
        return (new Cwmuploadscript())->upload($data);
    }

    /**
     * Delete a physical file from the local server
     *
     * @param   string    $filename      The filename or relative path to delete
     * @param   Registry  $serverParams  The server configuration parameters
     *
     * @return  bool  True if the file was deleted or already absent
     *
     * @since   10.1.0
     */
    #[\Override]
    public function deleteFile(string $filename, Registry $serverParams): bool
    {
        if (!(int) $serverParams->get('delete_files', 0)) {
            Log::add(
                'Local server: physical file deletion disabled, skipping: ' . $filename,
                Log::INFO,
                'com_proclaim'
            );

            return false;
        }

        if (empty($filename)) {
            return true;
        }

        // Determine absolute path: if filename starts with a known directory prefix, treat as site-relative
        $knownPrefixes  = ['images/', 'media/', 'tmp/'];
        $isSiteRelative = false;

        foreach ($knownPrefixes as $prefix) {
            if (str_starts_with($filename, $prefix)) {
                $isSiteRelative = true;
                break;
            }
        }

        if ($isSiteRelative) {
            $absPath = Path::clean(JPATH_SITE . '/' . $filename);
        } else {
            $basePath = $serverParams->get('path', 'images/biblestudy/media');
            $basePath = trim($basePath, '/');
            $absPath  = Path::clean(JPATH_SITE . '/' . $basePath . '/' . $filename);
        }

        // Safety: resolve symlinks and verify within JPATH_SITE
        $realPath = realpath($absPath);
        $realSite = realpath(JPATH_SITE);

        if ($realPath === false) {
            // File does not exist on disk — nothing to delete
            Log::add(
                'Local server: file not found on disk (already absent): ' . $absPath,
                Log::INFO,
                'com_proclaim'
            );

            return true;
        }

        if ($realSite === false || !str_starts_with($realPath, $realSite)) {
            Log::add(
                'Local server: path traversal blocked for: ' . $absPath . ' (resolved: ' . $realPath . ')',
                Log::WARNING,
                'com_proclaim'
            );

            return false;
        }

        if (!is_file($realPath)) {
            Log::add(
                'Local server: path is not a file: ' . $realPath,
                Log::WARNING,
                'com_proclaim'
            );

            return false;
        }

        try {
            $result = File::delete($realPath);

            if ($result) {
                Log::add(
                    'Local server: deleted physical file: ' . $realPath,
                    Log::INFO,
                    'com_proclaim'
                );
            } else {
                Log::add(
                    'Local server: File::delete() returned false for: ' . $realPath,
                    Log::WARNING,
                    'com_proclaim'
                );
            }

            return $result;
        } catch (\Exception $e) {
            Log::add(
                'Local server: failed to delete file: ' . $realPath . ' — ' . $e->getMessage(),
                Log::ERROR,
                'com_proclaim'
            );

            return false;
        }
    }

    /**
     * Render Fields for general view.
     *
     * @param object  $media_form  Medea files form
     * @param bool    $new         If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    public function renderGeneral($media_form, bool $new): string
    {
        $html   = '';
        $fields = $media_form->getFieldset('general');

        if ($fields) {
            foreach ($fields as $field) {
                if ($new) {
                    $s_name = $field->fieldname;

                    if (isset($media_form->s_params[$s_name])) {
                        $field->setValue($media_form->s_params[$s_name]);
                    }
                }

                $html .= $field->renderField();
            }
        }

        return $html;
    }

    /**
     * Render Layout and fields
     *
     * @param object  $media_form  Media files form
     * @param bool    $new         If media is new
     *
     * @return string
     *
     * @since 9.1.3
     */
    public function render($media_form, bool $new): string
    {
        $html = HTMLHelper::_('uitab.addTab', 'myTab', 'options', Text::_('JBS_ADDON_MEDIA_OPTIONS_LABEL'));
        $html .= $this->renderOptionsFields($media_form, $new);
        $html .= HTMLHelper::_('uitab.endTab');

        return $html;
    }

}
