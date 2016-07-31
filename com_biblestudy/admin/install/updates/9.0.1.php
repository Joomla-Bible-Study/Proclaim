<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 */
defined('_JEXEC') or die;

/**
 * Update for 9.0.1 class
 *
 * @package  BibleStudy.Admin
 * @since    9.0.1
 */
class Migration901
{
	/**
	 * Call Script for Updates of 9.0.1
	 *
	 * @param   JDatabaseDriver  $db  Joomla Data bass driver
	 *
	 * @return bool
	 *
	 * @since 9.0.1
	 */
	public function up($db)
	{
		$this->deleteUnexistingFiles();

		return true;
	}

	/**
	 * Remove Old Files and Folders
	 *
	 * @since      9.0.1
	 *
	 * @return   void
	 */
	protected function deleteUnexistingFiles()
	{
		// Import filesystem libraries. Perhaps not necessary, but does not hurt
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$path = array(
			BIBLESTUDY_PATH_ADMIN . '/models/style.php',
			BIBLESTUDY_PATH_ADMIN . '/models/styles.php',
			BIBLESTUDY_PATH_ADMIN . '/tables/style.php',
			BIBLESTUDY_PATH_ADMIN . '/controllers/style.php',
			BIBLESTUDY_PATH_ADMIN . '/controllers/styles.php',
			BIBLESTUDY_PATH_ADMIN . '/controllers/folder.php',
			BIBLESTUDY_PATH_ADMIN . '/controllers/folders.php',
			BIBLESTUDY_PATH_ADMIN . '/controllers/mediaimage.php',
			BIBLESTUDY_PATH_ADMIN . '/controllers/mediaimages.php',
			BIBLESTUDY_PATH_ADMIN . '/controllers/mimetype.php',
			BIBLESTUDY_PATH_ADMIN . '/controllers/mimetypes.php',
			BIBLESTUDY_PATH_ADMIN . '/views/style/index.html',
			BIBLESTUDY_PATH_ADMIN . '/views/style/view.html.php',
			BIBLESTUDY_PATH_ADMIN . '/views/style/tmpl/index.html',
			BIBLESTUDY_PATH_ADMIN . '/views/style/view.html.php',
			BIBLESTUDY_PATH_ADMIN . '/views/style/tmpl/edit.php',
			BIBLESTUDY_PATH_ADMIN . '/views/styles/index.html',
			BIBLESTUDY_PATH_ADMIN . '/views/styles/view.html.php',
			BIBLESTUDY_PATH_ADMIN . '/views/styles/tmpl/index.html',
			BIBLESTUDY_PATH_ADMIN . '/views/styles/view.html.php',
			BIBLESTUDY_PATH_ADMIN . '/views/styles/tmpl/default.php',
			BIBLESTUDY_PATH_ADMIN . '/models/share.php',
			BIBLESTUDY_PATH_ADMIN . '/models/shares.php',
			BIBLESTUDY_PATH_ADMIN . '/models/forms/share.xml',
			BIBLESTUDY_PATH_ADMIN . '/tables/share.php',
			BIBLESTUDY_PATH_ADMIN . '/controllers/share.php',
			BIBLESTUDY_PATH_ADMIN . '/controllers/shares.php',
			BIBLESTUDY_PATH_ADMIN . '/views/share/index.html',
			BIBLESTUDY_PATH_ADMIN . '/views/share/view.html.php',
			BIBLESTUDY_PATH_ADMIN . '/views/share/tmpl/index.html',
			BIBLESTUDY_PATH_ADMIN . '/views/share/view.html.php',
			BIBLESTUDY_PATH_ADMIN . '/views/share/tmpl/edit.php',
			BIBLESTUDY_PATH_ADMIN . '/views/shares/index.html',
			BIBLESTUDY_PATH_ADMIN . '/views/shares/view.html.php',
			BIBLESTUDY_PATH_ADMIN . '/views/shares/tmpl/index.html',
			BIBLESTUDY_PATH_ADMIN . '/views/shares/view.html.php',
			BIBLESTUDY_PATH_ADMIN . '/views/shares/tmpl/default.php',
			BIBLESTUDY_PATH_ADMIN . '/moduels/migration.php',
			BIBLESTUDY_PATH_ADMIN . '/controllers/migration.php',
			BIBLESTUDY_PATH_ADMIN . '/helpers/templates.helper.php',
			BIBLESTUDY_PATH_ADMIN . '/helpers/html/sortablelist.php',
			BIBLESTUDY_PATH_ADMIN . '/helpers/html/jquery.php',
			BIBLESTUDY_PATH_ADMIN . '/install/updates/update701.php',
			BIBLESTUDY_PATH_ADMIN . '/install/updates/update702.php',
			BIBLESTUDY_PATH_ADMIN . '/install/updates/update710.php',
			BIBLESTUDY_PATH_ADMIN . '/install/updates/updateAll.php',
			BIBLESTUDY_PATH_ADMIN . '/install/biblestudy.install.special.php',
			BIBLESTUDY_MEDIA_PATH . '/player/jwplayer.html5.js',
			BIBLESTUDY_MEDIA_PATH . '/player/key.js',
			BIBLESTUDY_MEDIA_PATH . '/js/tooltip.js',
			BIBLESTUDY_MEDIA_PATH . '/css/biblestudy-j2.5.css',
			BIBLESTUDY_MEDIA_PATH . '/css/cpanel.css',
			BIBLESTUDY_MEDIA_PATH . '/css/mediafilesedit.css',
			BIBLESTUDY_MEDIA_PATH . '/css/studieslist.css',
			BIBLESTUDY_PATH_HELPERS . '/biblegateway.php',
			BIBLESTUDY_PATH_HELPERS . '/elements.php',
			BIBLESTUDY_PATH_HELPERS . '/icon.php',
			BIBLESTUDY_PATH_HELPERS . '/related.php',
			BIBLESTUDY_PATH_HELPERS . '/studiesedit.php',
			BIBLESTUDY_PATH_HELPERS . '/writexml.php',
			BIBLESTUDY_PATH_LIB . '/biblestudy.debug.php',
			BIBLESTUDY_PATH_LIB . '/biblestudy.download.class.php',
			BIBLESTUDY_PATH_LIB . '/biblestudy.images.class.php',
			BIBLESTUDY_PATH_LIB . '/biblestudy.listing.class.php',
			BIBLESTUDY_PATH_LIB . '/biblestudy.media.class.php',
			BIBLESTUDY_PATH_LIB . '/biblestudy.pagebuilder.class.php',
			BIBLESTUDY_PATH_LIB . '/biblestudy.podcast.class.php',
			BIBLESTUDY_PATH_MOD . '/tmpl/default_custom.php'
		);

		foreach ($path as $file)
		{
			if (JFile::exists($file))
			{
				JFile::delete($file);
			}
		}

		$folders = array(
			BIBLESTUDY_PATH_ADMIN . '/views/styles',
			BIBLESTUDY_PATH_ADMIN . '/views/style',
			BIBLESTUDY_PATH_ADMIN . '/views/shares',
			BIBLESTUDY_PATH_ADMIN . '/views/share',
			BIBLESTUDY_MEDIA_PATH . '/jui',
			BIBLESTUDY_MEDIA_PATH . '/Legacyplayer',
			BIBLESTUDY_MEDIA_PATH . '/captcha',
			BIBLESTUDY_PATH_ADMIN . '/views/migration',
			BIBLESTUDY_PATH . '/language',
			BIBLESTUDY_PATH_MODELS . '/fields');

		foreach ($folders as $folder)
		{
			if (JFolder::exists($folder))
			{
				JFolder::delete($folder);
			}
		}
	}
}
