<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;


use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Table class for Server
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class TableServer extends JTable
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
	 * Server Name
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $server_name = null;

	/**
	 * Published
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $published = 1;

	/**
	 * Asset ID
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $assset_id = null;

	public $access;

	/**
	 * Server Type
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $type = null;

	public $params = null;

	public $media = null;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  &$db  Database connector object
	 *
	 * @since 9.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__bsms_servers', 'id', $db);
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $array   An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/bind
	 * @since   11.1
	 */
	public function bind($array, $ignore = '')
	{
		// Bind the server params
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		// Bind the media defaults
		if (isset($array['media']) && is_array($array['media']))
		{
			$registry = new Registry;
			$registry->loadArray($array['media']);
			$array['media'] = (string) $registry;
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
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

		return 'com_biblestudy.server.' . (int) $this->$k;
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
		return 'JBS Server: ' . $this->server_name;
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID 1.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   \JTable|null  $table  A JTable object for the asset parent.
	 * @param   null          $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		/** @type JTableAsset $asset */
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_biblestudy');

		return $asset->id;
	}
}
