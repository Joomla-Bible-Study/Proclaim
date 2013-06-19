<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Table class for Comment
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class TableComment extends JTable
{

	/**
	 * Primary Key
	 *
	 * @var int
	 */
	public $id = null;

	/**
	 * Study ID
	 *
	 * @var string
	 */
	public $study_id = null;

	/**
	 * User ID
	 *
	 * @var string
	 */
	public $user_id = null;

	/**
	 * Comment Date
	 *
	 * @var string
	 */
	public $comment_date = null;

	/**
	 * Full Name
	 *
	 * @var string
	 */
	public $full_name = null;

	/**
	 * Published
	 *
	 * @var string
	 */
	public $published = 1;

	/**
	 * Comment Text
	 *
	 * @var string
	 */
	public $comment_text = null;

	/**
	 * User Email
	 *
	 * @var string
	 */
	public $user_email = null;

	/**
	 * Title
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver &$db  Database connector object
	 */
	public function TableComment(& $db)
	{
		parent::__construct('#__bsms_comments', 'id', $db);
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form `table_name.id`
	 * where id is the value of the primary key of the table.
	 *
	 * @return      string
	 *
	 * @since       1.6
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_biblestudy.comment.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return      string
	 *
	 * @since       1.6
	 */
	protected function _getAssetTitle()
	{
		$title = 'JBS Comment - id/date: ' . $this->user_id . ' - ' . $this->comment_date;

		return $title;
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID 1.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   JTable  $table  A JTable object for the asset parent.
	 * @param   integer $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since       1.6
	 */
	protected function _getAssetParentId($table = null, $id = null)
	{
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_biblestudy');

		return $asset->id;
	}


}
