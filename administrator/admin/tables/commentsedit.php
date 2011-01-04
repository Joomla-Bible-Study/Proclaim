<?php


// no direct access
defined('_JEXEC') or die('Restricted access');



class Tablecommentsedit extends JTable
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
	var $user_id = null;
	var $comment_date = null;
	var $full_name = null;
	var $published = 1;
	var $comment_text = null;
	var $user_email = null;
	

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablecommentsedit(& $db) {
		parent::__construct('#__bsms_comments', 'id', $db);
	}
}
?>
