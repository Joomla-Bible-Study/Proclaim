<?php


// no direct access
defined('_JEXEC') or die('Restricted access');



class Tablemediaedit extends JTable
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
	var $media_text = null;
	var $media_alttext = null;
	var $media_image_path = null;
	var $media_image_name = null;
	var $published = null;
	var $media_extension = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablemediaedit(& $db) {
		parent::__construct('#__bsms_media', 'id', $db);
	}
}
?>
