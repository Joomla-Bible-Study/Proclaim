<?php
/**
 Podcast Tables for BibleStudy
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



class Tablepodcastedit extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	var $published = null;

	/**
	 * @var string
	 */
	
			var $title = null;
			var $website = null;
			var $description = null;
			var $image = null;
			var $imageh = null;
			var $imagew = null;
			var $author = null;
			var $podcastimage = null;
			var $podcastsummary = null;
			var $podcastsubtitle = null;
			var $podcastsearch = null;
			var $filename = null;
			var $language = null;
			var $podcastname = null;
			var $editor_name = null;
			var $editor_email = null;
			var $podcastlimit = null;
			var $episodetitle = null;
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablepodcastedit(& $db) {
		parent::__construct('#__bsms_podcast', 'id', $db);
	}
}
?>
