<?php
/**
 * Bible Study Topics table class
 * 
 * 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



class Tabletopicsedit extends JTable
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
	var $topic_text = null;
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tabletopicsedit(& $db) {
		parent::__construct('#__bsms_topics', 'id', $db);
	}
}
?>
