<?php
/**
 Servers Tables for BibleStudy
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



class Tableserversedit extends JTable
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
	var $server_name = null;
	var $server_path = null;
	var $server_type = null;
	var $ftp_username = null;
	var $ftp_password = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tableserversedit(& $db) {
		parent::__construct('#__bsms_servers', 'id', $db);
	}
}
?>
