<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Table;

// No Direct Access
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

defined('_JEXEC') or die;

/**
 * Table class for Comment
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMCommentTable extends Table
{
	/**
	 * Primary Key
	 *
	 * @var integer
	 * @since    7.0.0
	 */
	public $id = null;

	/**
	 * Published
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $published = 1;

	/**
	 * Study ID
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $study_id = null;

	/**
	 * User ID
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $user_id = null;

	/**
	 * Full Name
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $full_name = null;

	/**
	 * User Email
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $user_email = null;

	/**
	 * Comment Date
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $comment_date = null;

	/**
	 * Comment Text
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $comment_text = null;

	public $asset_id;

	public $access;

	public $language;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since    7.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__bsms_comments', 'id', $db);
	}

	/**
	 * Method to store a row in the database from the Table instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    https://docs.joomla.org/Table/store
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		if (!$this->_rules)
		{
			$this->setRules('{"core.delete":[],"core.edit":[],"core.create":[],"core.edit.state":[],"core.edit.own":[]}');
		}

		return parent::store($updateNulls);
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

		return 'com_proclaim.comment.' . (int) $this->$k;
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
	 * @param   \Joomla\CMS\Table\Table|null  $table  A Table object for the asset parent.
	 * @param   null                          $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since       1.6
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		/** @var \Joomla\CMS\Table\Asset $asset */
		$asset = Table::getInstance('Asset');
		$asset->loadByName('com_proclaim');

		return $asset->id;
	}
}
