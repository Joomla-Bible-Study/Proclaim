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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
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

    /**
     * Get available AJAX actions for this addon
     *
     * @return  array  List of available action names
     *
     * @since   10.1.0
     */
    #[\Override]
    public function getAjaxActions(): array
    {
        return ['browseFiles', 'copyFile'];
    }

    /**
     * Browse files in the server's media directory (XHR handler)
     *
     * Called by the xhr() controller method via `$addon->browseFiles($input)`.
     *
     * @param   \Joomla\Input\Input  $input  Request input
     *
     * @return  array  Response with files and folders
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function browseFiles($input): array
    {
        return $this->handleBrowseFilesAction();
    }

    /**
     * Handle browseFiles AJAX action — directory listing for local media browser
     *
     * @return  array  Response with files and folders
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    protected function handleBrowseFilesAction(): array
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();
        $serverId = $input->getInt('server_id', 0);
        $subPath  = $input->getString('path', '');
        $filter   = $input->getString('filter', 'all');

        if (!$serverId) {
            return ['success' => false, 'error' => 'No server ID provided'];
        }

        // Load server configuration to get upload path
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('id') . ' = ' . $serverId);
        $db->setQuery($query);
        $serverParams = $db->loadResult();

        $reg      = new Registry($serverParams ?: '{}');
        $basePath = $reg->get('path', 'images/biblestudy/media');
        $basePath = trim($basePath, '/');

        // Build absolute base path
        $absBase = Path::clean(JPATH_SITE . '/' . $basePath);

        if (!is_dir($absBase)) {
            return [
                'success'     => true,
                'files'       => [],
                'folders'     => [],
                'currentPath' => $basePath,
                'parentPath'  => null,
                'basePath'    => $basePath,
            ];
        }

        // Build current directory path
        $currentRel = $basePath;

        if (!empty($subPath)) {
            $subPath    = str_replace(['..', "\0"], '', $subPath);
            $currentRel = $basePath . '/' . trim($subPath, '/');
        }

        $absDir = Path::clean(JPATH_SITE . '/' . $currentRel);

        // Security: ensure path is within base
        $realBase = realpath($absBase);
        $realDir  = realpath($absDir);

        if ($realBase === false || $realDir === false || !str_starts_with($realDir, $realBase)) {
            return ['success' => false, 'error' => 'Invalid path'];
        }

        if (!is_dir($realDir)) {
            return ['success' => false, 'error' => 'Directory not found'];
        }

        // Extension filters
        $audioExts = ['mp3', 'm4a', 'ogg', 'oga', 'wav', 'flac', 'aac', 'wma'];
        $videoExts = ['mp4', 'm4v', 'webm', 'ogv', 'mov', 'avi', 'mkv', 'wmv'];
        $docExts   = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
        $allExts   = array_merge($audioExts, $videoExts, $docExts);

        // Determine which extensions to show
        $allowedExts = match ($filter) {
            'audio'    => $audioExts,
            'video'    => $videoExts,
            'document' => $docExts,
            default    => $allExts,
        };

        // Get folders
        $folderNames = Folder::folders($realDir, '.', false, false);
        $folders     = [];

        if (\is_array($folderNames)) {
            sort($folderNames);

            foreach ($folderNames as $folderName) {
                // Skip hidden directories
                if (str_starts_with($folderName, '.')) {
                    continue;
                }

                $relPath   = ($subPath ? trim($subPath, '/') . '/' : '') . $folderName;
                $folders[] = [
                    'name' => $folderName,
                    'path' => $relPath,
                ];
            }
        }

        // Get files
        $fileNames = Folder::files($realDir, '.', false, false);
        $files     = [];

        if (\is_array($fileNames)) {
            sort($fileNames);

            foreach ($fileNames as $fileName) {
                if (str_starts_with($fileName, '.')) {
                    continue;
                }

                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (!\in_array($ext, $allowedExts, true)) {
                    continue;
                }

                $filePath  = $realDir . '/' . $fileName;
                $fileSize  = is_file($filePath) ? filesize($filePath) : 0;
                $relPath   = $currentRel . '/' . $fileName;

                // Determine file category
                $category = 'document';

                if (\in_array($ext, $audioExts, true)) {
                    $category = 'audio';
                } elseif (\in_array($ext, $videoExts, true)) {
                    $category = 'video';
                }

                $files[] = [
                    'name'      => $fileName,
                    'path'      => $relPath,
                    'size'      => $fileSize,
                    'extension' => $ext,
                    'category'  => $category,
                ];
            }
        }

        // Determine parent path
        $parentPath = null;

        if (!empty($subPath)) {
            $parentPath = \dirname(trim($subPath, '/'));

            if ($parentPath === '.') {
                $parentPath = '';
            }
        }

        return [
            'success'     => true,
            'files'       => $files,
            'folders'     => $folders,
            'currentPath' => $currentRel,
            'parentPath'  => $parentPath,
            'basePath'    => $basePath,
        ];
    }

    /**
     * Copy a file to create a unique copy for a media record (XHR handler)
     *
     * Called by the xhr() controller method via `$addon->copyFile($input)`.
     *
     * @param   \Joomla\Input\Input  $input  Request input
     *
     * @return  array  Response with new file path and size
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function copyFile($input): array
    {
        return $this->handleCopyFileAction();
    }

    /**
     * Handle copyFile AJAX action — create a unique copy of a file
     *
     * @return  array  Response with new file path and size
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    protected function handleCopyFileAction(): array
    {
        $app      = Factory::getApplication();
        $input    = $app->getInput();
        $serverId = $input->getInt('server_id', 0);
        $filePath = $input->getString('file', '');

        if (!$serverId || empty($filePath)) {
            return ['success' => false, 'error' => 'Missing server_id or file parameter'];
        }

        // Load server configuration to get base path
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_servers'))
            ->where($db->quoteName('id') . ' = ' . $serverId);
        $db->setQuery($query);
        $serverParams = $db->loadResult();

        $reg      = new Registry($serverParams ?: '{}');
        $basePath = $reg->get('path', 'images/biblestudy/media');
        $basePath = trim($basePath, '/');

        // Build absolute path for the source file
        $absBase = Path::clean(JPATH_SITE . '/' . $basePath);
        $absFile = Path::clean(JPATH_SITE . '/' . $filePath);

        // Security: ensure both paths resolve and file is within the base directory
        $realBase = realpath($absBase);
        $realFile = realpath($absFile);
        $realSite = realpath(JPATH_SITE);

        if ($realBase === false || $realFile === false || $realSite === false) {
            return ['success' => false, 'error' => 'File or base path not found'];
        }

        if (!str_starts_with($realFile, $realSite)) {
            return ['success' => false, 'error' => 'Path traversal blocked'];
        }

        if (!is_file($realFile)) {
            return ['success' => false, 'error' => 'Source is not a file'];
        }

        // Build new filename with timestamp suffix
        $pathInfo  = pathinfo($realFile);
        $baseName  = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';
        $directory = $pathInfo['dirname'];
        $timestamp = time();

        $newName = $baseName . '_' . $timestamp . ($extension ? '.' . $extension : '');
        $newPath = $directory . '/' . $newName;

        // Collision guard: if timestamp name exists, append random suffix
        if (file_exists($newPath)) {
            $newName = $baseName . '_' . $timestamp . '_' . random_int(100, 999) . ($extension ? '.' . $extension : '');
            $newPath = $directory . '/' . $newName;
        }

        try {
            $result = File::copy($realFile, $newPath);

            if (!$result) {
                return ['success' => false, 'error' => 'File::copy() returned false'];
            }

            // Build the relative path matching the original format
            $relDir  = \dirname($filePath);
            $newRelPath = $relDir . '/' . $newName;
            $newSize = filesize($newPath) ?: 0;

            Log::add(
                'Local server: copied file ' . $realFile . ' → ' . $newPath,
                Log::INFO,
                'com_proclaim'
            );

            return [
                'success' => true,
                'newPath' => $newRelPath,
                'newSize' => $newSize,
            ];
        } catch (\Exception $e) {
            Log::add(
                'Local server: failed to copy file: ' . $realFile . ' — ' . $e->getMessage(),
                Log::ERROR,
                'com_proclaim'
            );

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
