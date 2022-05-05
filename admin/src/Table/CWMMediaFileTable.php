<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Table;

// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Table class for MediaFile
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMMediaFileTable extends Table
{
	/**
	 * Primary Key
	 *
	 * @var integer
	 * @since    7.0.0
	 */
	public $id = null;

	/**
	 * Study id
	 *
	 * @var integer
	 * @since    7.0.0
	 */
	public $study_id = null;

	/**
	 * Server id
	 *
	 * @var integer
	 * @since    7.0.0
	 */
	public $server_id = null;

	/**
	 * Podcast ID
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $podcast_id = null;

	/**
	 * Hold transitive data (i.e statistics)
	 *
	 * @var null
	 * @since    7.0.0
	 */
	public $metadata = null;

	/**
	 * Ordering
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $ordering = null;

	/**
	 * Create Date
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $createdate = null;

	public $hits = 0;

	/**
	 * Published
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $published = 1;

	/**
	 * Comment Text
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $comment = null;

	public $downloads = 0;

	public $plays = 0;

	/**
	 * Media configuration
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $params = null;

	public $asset_id;

	public $access;

	public $language;

	public $created_by;

	public $created_by_alias;

	public $modified;

	public $modified_by;

	public $checked_out;

	public $checked_out_time;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since    7.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__bsms_mediafiles', 'id', $db);
	}

	/**
	 * Method to bind an associative array or object to the Table instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $array   An associative array or object to bind to the Table instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since    7.0.0
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		// Bind the podcast_id
		if (isset($array['podcast_id']) && is_array($array['podcast_id']))
		{
			$array['podcast_id'] = implode(',', $array['podcast_id']);
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new Rules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
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

		return 'com_proclaim.mediafile.' . (int) $this->$k;
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
		return 'JBS Media File: ' . $this->id;
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

	/**
	 * Method to check a row in if the necessary properties/fields exist.  Checking
	 * a row in will allow other users the ability to edit the row.
	 *
	 * @param   mixed  $pk  An optional primary key value to check out.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/Table/checkIn
	 * @since   11.1
	 * @throws  \UnexpectedValueException
	 */
	public function checkIn($pk = null)
	{
		// If there is no checked_out or checked_out_time field, just return true.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
		{
			return true;
		}

		if (is_null($pk))
		{
			$pk = array();

			foreach ($this->_tbl_keys AS $key)
			{
				$pk[$this->$key] = $this->$key;
			}
		}
		elseif (!is_array($pk))
		{
			$pk = array($this->_tbl_key => $pk);
		}

		foreach ($this->_tbl_keys AS $key)
		{
			$pk[$key] = empty($pk[$key]) ? $this->$key : $pk[$key];

			if ($pk[$key] === null)
			{
				throw new \UnexpectedValueException('Null primary key not allowed.');
			}
		}

		// Check the row in by primary key.
		$query = $this->_db->getQuery(true)
			->update($this->_tbl)
			->set($this->_db->quoteName($this->getColumnAlias('checked_out')) . ' = 0')
			->set($this->_db->quoteName($this->getColumnAlias('checked_out_time')) . ' = ' . $this->_db->quote($this->_db->getNullDate()));
		$this->appendPrimaryKeys($query, $pk);
		$this->_db->setQuery($query);

		// Check for a database error.
		$this->_db->execute();

		// Set table values in the object.
		$this->checked_out      = 0;
		$this->checked_out_time = '';

		return true;
	}
}
