<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// No direct access
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/**
 * Description of dir
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class CwmdirModel extends BaseModel
{
    /**
     * Get folder names and their links
     *
     * @return array Filled with object with: name, link properties
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getBreadcrumbs(): array
    {
        $bc         = array();
        $currentDir = $this->getCurrentDir();

        $parts = explode('/', $currentDir);
        $link  = '';
        $i     = 0;

        // Fill bc array with objects
        foreach ($parts as $part) {
            if ($part !== '' && $part !== ' ') {
                $link         .= '/' . $part;
                $bc[$i]       = new \stdClass();
                $bc[$i]->name = $part;
                $bc[$i]->link = $link;
                $i++;
            }
        }

        // Prepend home dir
        $firstBC       = new \stdClass();
        $firstBC->name = BIBLESTUDY_MEDIA_PATH;
        $firstBC->link = '';
        array_unshift($bc, $firstBC);

        return $bc;
    }

    /**
     * Get current directory from request
     *
     * @param bool $fullPath ?
     * @param string $separator ?
     *
     * @return string
     *
     * @throws \Exception
     * @since 7.0
     */
    private function getCurrentDir($fullPath = false, $separator = '/'): string
    {
        $defaultDirVar  = "";
        $defaultDirPath = BIBLESTUDY_ROOT_PATH;

        // Filter GET variable
        $directoryVarFromReq = Factory::getApplication()->input->get('dir', $separator);
        $directoryVarReplSep = str_replace(array("/", "\\"), $separator, $directoryVarFromReq);
        $directoryVarWODots  = preg_replace(array("/\.\./", "/\./"), '', $directoryVarReplSep);
        $directoryVar        = $directoryVarWODots;

        // Make filtered full directory path
        $fullDirPath = BIBLESTUDY_ROOT_PATH . $separator . $directoryVar;
        $dirPath     = Path::check($fullDirPath);

        if (file_exists($dirPath)) {
            // Save current directory in session whenever this function gets called
            $this->setDirectoryState($dirPath);

            if ($fullPath) {
                return $dirPath;
            }

            return $directoryVar;
        }

        if ($fullPath) {
            return $defaultDirPath;
        }

        return $defaultDirVar;
    }

    /**
     * Save current directory in session for file upload
     *
     * @param string $directoryPath ?
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0
     */
    private function setDirectoryState($directoryPath): void
    {
        $app                  = Factory::getApplication();
        $session              = $app->getSession();
        $homeDirBase64        = base64_encode(BIBLESTUDY_ROOT_PATH);
        $directoryPathCleaned = Path::clean($directoryPath);
        $currentDir           = Path::check($directoryPathCleaned);
        $currentDirBase64     = base64_encode($currentDir);

        if (strpos($currentDir, BIBLESTUDY_ROOT_PATH) === false) {
            // Path is invalid, save default directory
            $session->set('current_dir', $homeDirBase64, 'com_proclaim');
        } else {
            $session->set('current_dir', $currentDirBase64, 'com_proclaim');
        }
    }

    /**
     * Folders in current directory
     *
     * @return array
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getFolders(): array
    {
        $currentDir = $this->getCurrentDir(true);

        // Get all folders in current dir
        $folders = Folder::folders($currentDir, '.', false, true);

        // Set the current folder on first place in array
        array_unshift($folders, $currentDir);

        return $this->setFolderInfo($folders);
    }

    /**
     * Sets the info and path for each folder
     *
     * @param   array  $folderPaths  ?
     *
     * @return array
     *
     * @since 7.0
     */
    private function setFolderInfo($folderPaths): array
    {
        $OFolders = array();

        for ($i = 0, $iMax = count($folderPaths); $i < $iMax; $i++) {
            $path                         = Path::clean($folderPaths[$i]);
            $OFolders[$i]                 = new \stdClass();
            $OFolders[$i]->fullPath       = $path;
            $OFolders[$i]->basename       = basename($path);
            $OFolders[$i]->parentFullPath = dirname($path) . '/';
            $OFolders[$i]->parentBasename = basename(dirname($path) . '/');
            $OFolders[$i]->folderCount    = count(Folder::folders($path, '.', false, false));
            $OFolders[$i]->fileCount      = count(Folder::files($path, '.', false, false, array("index.html")));

            // Make parent short path for go-up directory
            if ($path === BIBLESTUDY_ROOT_PATH . '/' . basename($path)) {
                $OFolders[$i]->parentShort = "";
            } else {
                $OFolders[$i]->parentShort = dirname(str_replace(BIBLESTUDY_ROOT_PATH, "", $path . '/'));
            }

            $OFolders[$i]->folderLink = $OFolders[$i]->parentShort . '/' . basename($path);
        }

        return $OFolders;
    }

    /**
     * Files in the current directory
     *
     * @return array
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getFiles(): array
    {
        $currentDir = $this->getCurrentDir(true);

        // Get all files
        $files = Folder::files($currentDir, '.', false, true, array('index.html'));

        return $this->setFileInfo($files);
    }

    /**
     * Set information for each file
     *
     * @param   array  $filePaths  ?
     *
     * @return array
     *
     * @since 7.0
     */
    private function setFileInfo($filePaths): array
    {
        $OFiles = array();

        for ($i = 0, $iMax = count($filePaths); $i < $iMax; $i++) {
            $path                 = Path::clean($filePaths[$i]);
            $OFiles[$i]           = new \stdClass();
            $OFiles[$i]->basename = basename($path);
            $OFiles[$i]->fullPath = dirname($path) . '/' . basename($path);
            $OFiles[$i]->link     = Uri::root() . '/images' . $this->getCurrentDir(false, "/") . '/' . basename($path);
            $OFiles[$i]->ext      = File::getExt($path);

            // Image info, if file is image
            if (@getimagesize($path)) {
                $OFiles[$i]->imgInfo = @getimagesize($path);
            } else {
                $OFiles[$i]->imgInfo = 0;
            }

            // File size
            $size = @filesize($path);

            $unit = ' B';

            if ($size > 1024) {
                $size /= 1024;
                $unit = 'KB';
            }

            if ($size > 1024) {
                $size /= 1024;
                $unit = 'MB';
            }

            if ($size > 1024) {
                $size /= 1024;
                $unit = 'GB';
            }

            $size             = round($size, 2) . " " . $unit;
            $OFiles[$i]->size = $size;

            // Last accessed and last modified time
            $OFiles[$i]->accessTime   = @date("%d/%m/%Y %H:%M:%S", @fileatime($path));
            $OFiles[$i]->modifiedTime = @date("%d/%m/%Y %H:%M:%S", @filemtime($path));
        }

        return $OFiles;
    }
}
