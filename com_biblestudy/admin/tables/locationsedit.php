<?php
/**
Locations Tables for BibleStudy
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



class Tablelocationsedit extends JTable
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
	var $location_text = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablelocationsedit(& $db) {
		parent::__construct('#__bsms_locations', 'id', $db);
	}
}
?>
