<?php


// no direct access
defined('_JEXEC') or die('Restricted access');



class Tablemediafilesedit extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;

	/**
	 * @var string
	 */
	var $study_id = null;
	var $media_image = null;
	var $server = null;
	var $path = null;
	var $published = 1;
	var $special = null;
	var $filename = null;
	var $size = null;
	var $mime_type = null;
	var $podcast_id = null;
	var $internal_viewer = null;
	var $ordering = null;
	var $mediacode = null;
	var $createdate = null;
	var $link_type = null;
	var $hits = null;
	var $docManCategory = null;
	var $docManItem = null;
	var $articleSection = null;
	var $articleCategory = null;
	var $articleTitle = null;
	

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablemediafilesedit(& $db) {
		parent::__construct('#__bsms_mediafiles', 'id', $db);
	}
}
?>
