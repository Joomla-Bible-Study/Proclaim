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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Seris Table class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmserieTable extends Table
{
    /**
     * Primary Key
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $id = null;

    /**
     * Series Text
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $series_text = null;

    /**
     * Alias
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $alias = null;

    /**
     * Teacher
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $teacher = null;

    /**
     * Description
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $description = null;

    /**
     * Series Thumbnail
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $series_thumbnail = null;

    /**
     * Original full-size image path
     *
     * @var string|null
     *
     * @since 10.1.0
     */
    public ?string $image = null;

    /**
     * Publish state
     *
     * @var int
     *
     * @since 9.0.0
     */
    public ?int $published = 1;

    /**
     * Location ID (multi-campus)
     *
     * @var int|null
     * @since 10.1.0
     */
    public ?int $location_id = null;

    /**
     * Asset ID
     *
     * @var int|null
     * @since 9.0.0
     */
    public ?int $asset_id = null;

    /**
     * Ordering
     *
     * @var int|null
     * @since 9.0.0
     */
    public ?int $ordering = null;

    /**
     * Access Level
     *
     * @var int|null
     * @since 9.0.0
     */
    public ?int $access = null;

    /**
     * Language
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $language = null;

    /**
     * Landing Show
     *
     * @var int|null
     * @since 9.0.0
     */
    public ?int $landing_show = null;

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
     * Publish up date
     *
     * @var string
     * @since 10.1.0
     */
    public ?string $publish_up = '0000-00-00 00:00:00';

    /**
     * Publish down date
     *
     * @var string
     * @since 10.1.0
     */
    public ?string $publish_down = '0000-00-00 00:00:00';

    /**
     * Podcast Show
     *
     * @var int
     * @since 10.1.0
     */
    public ?int $podcast_show = 0;

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
     * @since 9.0.0
     */
    public function __construct(&$db)
    {
        parent::__construct('#__bsms_series', 'id', $db);
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
    /**
     * Bind form data to the table, casting typed properties to prevent PHP 8.3 TypeError.
     *
     * @param   array|object  $array   Data to bind
     * @param   array|string  $ignore  Fields to ignore
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    #[\Override]
    public function bind($array, $ignore = ''): bool
    {
        if (\is_array($array)) {
            // Cast typed int properties to prevent PHP 8.3 TypeError when form posts strings
            if (isset($array['asset_id'])) {
                $array['asset_id'] = $array['asset_id'] !== '' ? (int) $array['asset_id'] : null;
            }
        }

        return parent::bind($array, $ignore);
    }

    #[\Override]
    public function check(): bool
    {
        if (trim($this->series_text ?? '') === '') {
            throw new \UnexpectedValueException(Text::_('JBS_CMN_ERROR_SERIES_NAME_REQUIRED'));
        }

        // Normalise "Select Location" sentinel (-1) to NULL for DB storage
        if ($this->location_id !== null && $this->location_id <= 0) {
            $this->location_id = null;
        }

        // Sanitise publish dates — empty strings are invalid for NOT NULL DATETIME columns
        if (empty($this->publish_up)) {
            $this->publish_up = $this->getDatabase()->getNullDate();
        }

        if (empty($this->publish_down)) {
            $this->publish_down = $this->getDatabase()->getNullDate();
        }

        return parent::check();
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
        $imagePath = $this->image ?: $this->series_thumbnail;
        if (!empty($imagePath) && str_contains($imagePath, 'images/biblestudy/series/')) {
            $folderPath = \dirname($imagePath);
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

        return 'com_proclaim.serie.' . (int)$this->$k;
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
        return 'JBS Series: ' . $this->series_text;
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
     * @since       1.6
     */
    #[\Override]
    protected function _getAssetParentId(?Table $table = null, $id = null): int
    {
        // Get Proclaim Root ID
        return Cwmassets::parentId();
    }
}
