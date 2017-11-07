<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Seris JTable class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class TableSerie extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $id = null;

	/**
	 * Series Text
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $series_text = null;

	public $alias;

	/**
	 * Teacher
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $teacher = null;

	/**
	 * Description
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $description = null;

	/**
	 * Series Thumbnail
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $series_thumbnail = null;

	/**
	 * Publish state
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $published = 1;

	public $asset_id;

	public $ordering;

	public $access;

	public $language;

	public $landing_show;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since 9.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__bsms_series', 'id', $db);
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    https://docs.joomla.org/JTable/store
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

		return 'com_biblestudy.serie.' . (int) $this->$k;
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
		$title = 'JBS Series: ' . $this->series_text;

		return $title;
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID 1.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   JTable   $table  A JTable object for the asset parent.
	 * @param   integer  $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since       1.6
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		/** @type JTableAsset $asset */
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_biblestudy');

		return $asset->id;
	}
}
