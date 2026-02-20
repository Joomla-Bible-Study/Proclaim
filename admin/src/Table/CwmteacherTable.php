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

use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Teacher table class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmteacherTable extends Table
{
    /**
     * Teacher Name
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $teachername = null;

    /** @var string Alias
     *
     * @since 9.0.0
     */
    public string $alias = '';

    /** @var string Ordering
     *
     * @since 9.0.0
     */
    public string $ordering = '';

    /**
     * @var int
     * @since 9.0.0
     */
    public int $id = 0;

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
    public string $created_by_alias = '';

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
     * Teacher thumbnail path
     *
     * @var string|null
     * @since 10.2.0
     */
    public ?string $teacher_thumbnail = null;

    /**
     * Social links JSON
     *
     * @var string|null
     * @since 10.1.0
     */
    public ?string $social_links = null;

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
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since  7.0.0
     */
    public function __construct(&$db)
    {
        parent::__construct('#__bsms_teachers', 'id', $db);
    }

    /**
     * Perform pre-save checks on the table properties.
     *
     * @return  bool  True if checks pass.
     *
     * @throws  \UnexpectedValueException
     *
     * @since   10.1.0
     */
    #[\Override]
    public function check(): bool
    {
        if (trim($this->teachername ?? '') === '') {
            throw new \UnexpectedValueException(Text::_('JBS_CMN_ERROR_TEACHER_NAME_REQUIRED'));
        }

        // Check for duplicate alias (case-insensitive)
        try {
            $db = $this->getDatabase();
        } catch (\Throwable) {
            $db = null;
        }

        if (!empty($this->alias) && $db !== null) {
            $query = $db->getQuery(true);
            $query->select($db->quoteName(['id', 'teachername']))
                ->from($db->quoteName('#__bsms_teachers'))
                ->where('LOWER(' . $db->quoteName('alias') . ') = LOWER(' . $db->quote($this->alias) . ')');

            // Exclude self on edit
            if (!empty($this->id)) {
                $query->where($db->quoteName('id') . ' != ' . (int) $this->id);
            }

            $db->setQuery($query);
            $existing = $db->loadObject();

            if ($existing) {
                $link = 'index.php?option=com_proclaim&task=cwmteacher.edit&id=' . (int) $existing->id;
                throw new \UnexpectedValueException(
                    Text::sprintf('JBS_TCH_DUPLICATE_LINK', $existing->teachername, $link)
                );
            }
        }

        // Auto-prepend https:// to URL fields missing a schema
        foreach (['website', 'facebooklink', 'twitterlink', 'bloglink', 'link1', 'link2', 'link3'] as $field) {
            if (!empty($this->$field) && !preg_match('#^[a-z][a-z0-9+\-.]*://#i', $this->$field)) {
                $this->$field = 'https://' . $this->$field;
            }
        }

        // Auto-prepend https:// to URLs in social_links JSON
        if (!empty($this->social_links) && \is_string($this->social_links)) {
            $links   = json_decode($this->social_links, true);
            $changed = false;

            if (\is_array($links)) {
                foreach ($links as &$link) {
                    if (
                        !empty($link['url'])
                        && !preg_match('#^[a-z][a-z0-9+\-.]*://#i', $link['url'])
                        && ($link['platform'] ?? '') !== 'email'
                        && ($link['platform'] ?? '') !== 'phone'
                    ) {
                        $link['url'] = 'https://' . $link['url'];
                        $changed     = true;
                    }
                }

                unset($link);

                if ($changed) {
                    $this->social_links = json_encode($links);
                }
            }
        }

        return parent::check();
    }

    /**
     * Method to bind an associative array or object to the Table instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   array|object  $src  An associative array or object to bind to the Table instance.
     * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  bool  True on success.
     *
     * @link    http://docs.joomla.org/Table/bind
     * @since   11.1
     */
    #[\Override]
    public function bind($src, $ignore = ''): bool
    {
        if (\array_key_exists('params', $src) && \is_array($src['params'])) {
            $registry = new Registry();
            $registry->loadArray($src['params']);
            $src['params'] = (string)$registry;
        }

        // Bind the rules.
        if (isset($src['rules']) && \is_array($src['rules'])) {
            $rules = new Rules($src['rules']);
            $this->setRules($rules);
        }

        // Cast typed int properties to prevent PHP 8.3 TypeError when form posts strings
        if (isset($src['asset_id'])) {
            $src['asset_id'] = $src['asset_id'] !== '' ? (int) $src['asset_id'] : null;
        }

        return parent::bind($src, $ignore);
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
     * @link    https://docs.joomla.org/Table/store
     * @since   11.1
     */
    #[\Override]
    public function store($updateNulls = false): bool
    {
        if (!$this->getRules()) {
            $this->setRules(
                '{"core.delete":[],"core.edit":[],"core.create":[],"core.edit.state":[],"core.edit.own":[]}'
            );
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to delete a row from the database by primary key value.
     * Also cleans up associated image folder.
     *
     * @param   mixed  $pk  Primary key value to delete (null uses instance property)
     *
     * @return  bool  True on success
     *
     * @since 10.2.0
     */
    #[\Override]
    public function delete($pk = null): bool
    {
        $pk = $pk ?? $this->id;

        // Load record to get image paths before deletion
        if ($pk !== $this->id) {
            $this->load($pk);
        }

        // Delete associated image folder if exists
        if (!empty($this->teacher_thumbnail) && str_contains($this->teacher_thumbnail, 'images/biblestudy/teachers/')) {
            $folderPath = \dirname($this->teacher_thumbnail);
            Cwmthumbnail::deleteFolder($folderPath);
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

        return 'com_proclaim.teacher.' . (int)$this->$k;
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
        return 'JBS Teacher: ' . $this->teachername;
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID 1.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   ?Table  $table  A Table object for the asset parent.
     * @param   ?int    $id     Id to look up
     *
     * @return  int
     *
     * @since   11.1
     */
    #[\Override]
    protected function _getAssetParentId(?Table $table = null, $id = null): int
    {
        // Get to Proclaim Root ID
        return Cwmassets::parentId();
    }
}
