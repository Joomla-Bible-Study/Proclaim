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
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Table class for Template
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMTemplateTable extends Table
{
	/**
	 * Id
	 *
	 * @var integer
	 *
	 * @since 9.0.0
	 */
	public int $id = 0;

	/**
	 * Type
	 *
	 * @var ?string
	 *
	 * @since 9.0.0
	 */
	public ?string $type = null;

	/**
	 * Template
	 *
	 * @var ?string
	 *
	 * @since 9.0.0
	 */
	public ?string $tmpl = null;

	/**
	 * Published
	 *
	 * @var integer
	 *
	 * @since 9.0.0
	 */
	public int $published = 1;

	/**
	 * Params
	 *
	 * @var ?string
	 *
	 * @since 9.0.0
	 */
	public ?string $params = null;

	/**
	 * Title
	 *
	 * @var ?string
	 *
	 * @since 9.0.0
	 */
	public ?string $title = null;

	/**
	 * Text
	 *
	 * @var ?string
	 *
	 * @since 9.0.0
	 */
	public ?string $text = null;

	/**
	 * PDF file
	 *
	 * @var ?string
	 *
	 * @since 9.0.0
	 */
	public ?string $pdf = null;

	/**
	 * Contractor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since 9.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__bsms_templates', 'id', $db);
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
	 * @link    http://docs.joomla.org/Table/bind
	 * @since   11.1
	 */
	public function bind($array, $ignore = ''): bool
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
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
	 * @link    http://docs.joomla.org/Table/store
	 * @since   11.1
	 */
	public function store($updateNulls = false): bool
	{
		$db = Factory::getContainer()->get('DatabaseDriver');

		// Attempt to store the user data.
		$oldrow = new CWMTemplateTable($db);

		if (!$oldrow->load($this->id) && $oldrow->getError())
		{
			$this->setError($oldrow->getError());
		}

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
	protected function _getAssetName(): string
	{
		$k = $this->_tbl_key;

		return 'com_proclaim.template.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return      string
	 *
	 * @since       1.6
	 */
	protected function _getAssetTitle(): string
	{
		return 'JBS Template: ' . $this->title;
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
	 * @since   11.1
	 */
	protected function _getAssetParentId(Table $table = null, $id = null): int
	{
		/** @var \Joomla\CMS\Table\Asset $asset */
		//$asset = Table::getInstance('Asset');
		//$asset->loadByName('com_proclaim');
        $asset->id = 1;
		return $asset->id;
	}
}
