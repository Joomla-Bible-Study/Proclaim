<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Table class for Template
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmtemplateTable extends Table
{
    /**
     * Id
     *
     * @var int
     *
     * @since 9.0.0
     */
    public ?int $id = 0;

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
     * @var int
     *
     * @since 9.0.0
     */
    public ?int $published = 1;

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
     * Asset ID
     *
     * @var int|null
     * @since 9.0.0
     */
    public ?int $asset_id = null;

    /**
     * Access Level
     *
     * @var int|null
     * @since 9.0.0
     */
    public ?int $access = null;

    /**
     * Location ID (multi-campus)
     *
     * @var int|null
     * @since 10.1.0
     */
    public ?int $location_id = null;

    /**
     * Created date
     *
     * @var string|null
     * @since 10.1.0
     */
    public ?string $created = null;

    /**
     * Created by user ID
     *
     * @var int|null
     * @since 10.1.0
     */
    public ?int $created_by = null;

    /**
     * Created by alias
     *
     * @var string
     * @since 10.1.0
     */
    public ?string $created_by_alias = '';

    /**
     * Modified date
     *
     * @var string|null
     * @since 10.1.0
     */
    public ?string $modified = null;

    /**
     * Modified by user ID
     *
     * @var int|null
     * @since 10.1.0
     */
    public ?int $modified_by = null;

    /**
     * Checked out user ID
     *
     * @var int|null
     * @since 10.1.0
     */
    public ?int $checked_out = null;

    /**
     * Checked out time
     *
     * @var string|null
     * @since 10.1.0
     */
    public ?string $checked_out_time = null;

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
     * @param   mixed  $array  An associative array or object to bind to the Table instance.
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  bool  True on success.
     *
     * @link    http://docs.joomla.org/Table/bind
     * @since   11.1
     */
    #[\Override]
    public function bind($array, $ignore = ''): bool
    {
        if (isset($array['params']) && \is_array($array['params'])) {
            $registry = new Registry();

            // For existing records, merge submitted params with existing params
            // This preserves values from lazy-loaded sections that were never expanded
            if (!empty($array['id'])) {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select($db->quoteName('params'))
                    ->from($db->quoteName('#__bsms_templates'))
                    ->where($db->quoteName('id') . ' = ' . (int) $array['id']);
                $db->setQuery($query);
                $existingParams = $db->loadResult();

                if ($existingParams) {
                    $registry->loadString($existingParams);
                }
            }

            // Merge submitted params on top of existing (submitted values take precedence)
            $registry->loadArray($array['params']);
            $array['params'] = (string)$registry;
        }

        // Bind the rules.
        if (isset($array['rules']) && \is_array($array['rules'])) {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }

        // Cast typed int properties to prevent PHP 8.3 TypeError when form posts strings
        if (isset($array['asset_id'])) {
            $array['asset_id'] = $array['asset_id'] !== '' ? (int) $array['asset_id'] : null;
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
     * @param   bool  $updateNulls  True to update fields even if they are null.
     *
     * @return  bool  True on success.
     *
     * @link    http://docs.joomla.org/Table/store
     * @since   11.1
     */
    #[\Override]
    public function store($updateNulls = false): bool
    {
        // Set default rules if not already set
        if (!$this->getRules()) {
            $this->setRules(
                '{"core.delete":[],"core.edit":[],"core.create":[],"core.edit.state":[],"core.edit.own":[]}'
            );
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to delete a row from the database table by primary key value.
     * Prevents deletion of the default template (ID 1).
     *
     * @param   mixed  $pk  An optional primary key value to delete.
     *
     * @return  bool  True on success.
     *
     * @since   10.1.0
     */
    #[\Override]
    public function delete($pk = null): bool
    {
        $k  = $this->_tbl_key;
        $pk = $pk ?? $this->$k;

        if ((int) $pk === 1) {
            throw new \RuntimeException(Text::_('JBS_TPL_DEFAULT_ERROR'));
        }

        return parent::delete($pk);
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
    #[\Override]
    protected function _getAssetName(): string
    {
        $k = $this->_tbl_key;

        return 'com_proclaim.template.' . (int)$this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return      string
     *
     * @since       1.6
     */
    #[\Override]
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
     * @param   ?Table  $table  A Table object for the asset parent.
     * @param   null    $id     Id to look up
     *
     * @return  int
     *
     * @since   11.1
     */
    #[\Override]
    protected function _getAssetParentId(?Table $table = null, $id = null): int
    {
        // Get Proclaim Root ID
        return Cwmassets::parentId();
    }
}
