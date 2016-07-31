<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No direct access
defined('_JEXEC') or die();

// Import the Joomla modellist library
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Description of dir
 *
 * @package  BibleStudy.Admin
 * @since    9.0.0
 */
class BiblestudyModelDir extends JModelItem
{
	/**
	 * Save current directory in session for file upload
	 *
	 * @param   string  $directoryPath  ?
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	private function _setDirectoryState($directoryPath)
	{
		$session              = JFactory::getSession();
		$homeDirBase64        = base64_encode(BIBLESTUDY_ROOT_PATH);
		$directoryPathCleaned = JPath::clean($directoryPath);
		$currentDir           = JPath::check($directoryPathCleaned);
		$currentDirBase64     = base64_encode($currentDir);

		if (strpos($currentDir, BIBLESTUDY_ROOT_PATH) === false)
		{
			// Path is invalid, save default directory
			$session->set('current_dir', $homeDirBase64, 'com_biblestudy');
		}
		else
		{
			$session->set('current_dir', $currentDirBase64, 'com_biblestudy');
		}
	}

	/**
	 * Get folder names and their links
	 *
	 * @return array Filled with object with: name, link properties
	 *
	 * @since 7.0
	 */
	public function getBreadcrumbs()
	{
		$bc         = array();
		$currentDir = $this->_getCurrentDir();

		$parts = explode(DS, $currentDir);
		$link  = '';
		$i     = 0;

		// Fill bc array with objects
		foreach ($parts as $part)
		{
			if (strlen($part) && $part != ' ')
			{
				$link .= '/' . $part;
				$bc[$i]       = new JObject;
				$bc[$i]->name = $part;
				$bc[$i]->link = $link;
				$i++;
			}
		}

		// Prepend home dir
		$firstBC       = new stdClass;
		$firstBC->name = BIBLESTUDY_MEDIA_PATH;
		$firstBC->link = '';
		array_unshift($bc, $firstBC);

		return $bc;
	}

	/**
	 * Folders in current directory
	 *
	 * @return array
	 *
	 * @since 7.0
	 */
	public function getFolders()
	{
		$currentDir = $this->_getCurrentDir(true);

		// Get all folders in current dir
		$folders = JFolder::folders($currentDir, '.', false, true);

		// Set current folder on first place in array
		array_unshift($folders, $currentDir);

		return $this->_setFolderInfo($folders);
	}

	/**
	 * Files in current directory
	 *
	 * @return array
	 *
	 * @since 7.0
	 */
	public function getFiles()
	{
		$currentDir = $this->_getCurrentDir(true);

		// Get all files
		$files = JFolder::files($currentDir, '.', false, true, array('index.html'));

		return $this->_setFileInfo($files);
	}

	/**
	 * Get current directory from request
	 *
	 * @param   bool    $fullPath   ?
	 * @param   string  $separator  ?
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	private function _getCurrentDir($fullPath = false, $separator = '/')
	{
		$defaultDirVar  = "";
		$defaultDirPath = BIBLESTUDY_ROOT_PATH;

		// Filter GET variable
		$directoryVarFromReq = JFactory::getApplication()->input->get('dir', $separator);
		$directoryVarReplSep = str_replace(array("/", "\\"), $separator, $directoryVarFromReq);
		$directoryVarWODots  = preg_replace(array("/\.\./", "/\./"), '', $directoryVarReplSep);
		$directoryVar        = $directoryVarWODots;

		// Make filtered full directory path
		$fullDirPath = BIBLESTUDY_ROOT_PATH . $separator . $directoryVar;
		$dirPath     = JPath::check($fullDirPath);

		if (file_exists($dirPath))
		{
			// Save current directory in session whenever this function gets called
			$this->_setDirectoryState($dirPath);

			if ($fullPath)
			{
				return $dirPath;
			}
			else
			{
				return $directoryVar;
			}
		}
		else
		{
			if ($fullPath)
			{
				return $defaultDirPath;
			}
			else
			{
				return $defaultDirVar;
			}
		}
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
	private function _setFileInfo($filePaths)
	{
		$OFiles = array();

		for ($i = 0; $i < count($filePaths); $i++)
		{
			$path                 = JPath::clean($filePaths[$i]);
			$OFiles[$i]           = new JObject;
			$OFiles[$i]->basename = basename($path);
			$OFiles[$i]->fullPath = dirname($path) . DS . basename($path);
			$OFiles[$i]->link     = JUri::root() . '/images' . $this->_getCurrentDir(false, "/") . '/' . basename($path);
			$OFiles[$i]->ext      = JFile::getExt($path);

			// Image info, if file is image
			if (@getimagesize($path))
			{
				$OFiles[$i]->imgInfo = @getimagesize($path);
			}
			else
			{
				$OFiles[$i]->imgInfo = 0;
			}

			// File size
			$size = @filesize($path); /* B */
			$unit = ' B';

			if ($size > 1024)
			{
				$size = $size / 1024;
				$unit = 'KB';
			}

			if ($size > 1024)
			{
				$size = $size / 1024;
				$unit = 'MB';
			}

			if ($size > 1024)
			{
				$size = $size / 1024;
				$unit = 'GB';
			}

			$size             = round($size, 2) . " " . $unit;
			$OFiles[$i]->size = $size;

			// Last accessed and last modified time
			$OFiles[$i]->accessTime   = @strftime("%d/%m/%Y %H:%M:%S", @fileatime($path));
			$OFiles[$i]->modifiedTime = @strftime("%d/%m/%Y %H:%M:%S", @filemtime($path));
		}

		return $OFiles;
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
	private function _setFolderInfo($folderPaths)
	{
		$OFolders = array();

		for ($i = 0; $i < count($folderPaths); $i++)
		{
			$path                         = JPath::clean($folderPaths[$i]);
			$OFolders[$i]                 = new JObject;
			$OFolders[$i]->fullPath       = $path;
			$OFolders[$i]->basename       = basename($path);
			$OFolders[$i]->parentFullPath = dirname($path) . '/';
			$OFolders[$i]->parentBasename = basename(dirname($path) . '/');
			$OFolders[$i]->folderCount    = count(JFolder::folders($path, '.', false, false));
			$OFolders[$i]->fileCount      = count(JFolder::files($path, '.', false, false, array("index.html")));

			// Make parent short path for go up directory
			if ($path == BIBLESTUDY_ROOT_PATH . '/' . basename($path))
			{
				$OFolders[$i]->parentShort = "";
			}
			else
			{
				$OFolders[$i]->parentShort = dirname(str_replace(BIBLESTUDY_ROOT_PATH, "", $path . '/'));
			}

			$OFolders[$i]->folderLink = $OFolders[$i]->parentShort . '/' . basename($path);
		}

		return $OFolders;
	}
}
