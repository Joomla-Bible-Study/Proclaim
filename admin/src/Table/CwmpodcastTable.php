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
 * Podcast table class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmpodcastTable extends Table
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
     * Published
     *
     * @var int
     *
     * @since 9.0.0
     */
    public ?int $published = 1;

    /**
     * Title
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $title = null;

    /**
     * Website Address
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $website = null;

    /**
     * Description
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $description = null;

    /**
     * Image
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $image = null;

    /**
     * Image Height
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $imageh = null;

    /**
     * Image Width
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $imagew = null;

    /**
     * Author
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $author = null;

    /**
     * Podcast Image
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $podcastimage = null;

    /**
     * Podcast Summary
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $podcastsummary = null;

    /**
     * Podcast Search Words
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $podcastsearch = null;

    /**
     * File Name
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $filename = null;

    /**
     * Language of Podcast
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $language = null;

    /**
     * Podcast name
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $podcastname = null;

    /**
     * Editor Name
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $editor_name = null;

    /**
     * Editor Email Address
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $editor_email = null;

    /**
     * Limit of the episodes in the podcast
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $podcastlimit = null;

    /**
     * Episode Title
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $episodetitle = null;

    /**
     * Custom
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $custom = null;

    /**
     * Details template ID
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $episodesubtitle = null;

    /**
     * Custom
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $customsubtitle = null;

    /**
     * Details template ID
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $detailstemplateid = null;

    /**
     * Type of link to use for podcast.
     *
     * 0 = Default is to episode.
     * 1 = Direct Link.
     * 2 = Popup Player Window with default player as internal.
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $linktype = null;

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
     * Params
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $params = null;

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
        parent::__construct('#__bsms_podcast', 'id', $db);
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
        if (trim($this->title ?? '') === '') {
            throw new \UnexpectedValueException(Text::_('JBS_CMN_ERROR_TITLE_REQUIRED'));
        }

        // Auto-prepend https:// to URL fields missing a schema
        foreach (['website', 'podcastlink', 'alternatelink'] as $field) {
            if (!empty($this->$field) && !preg_match('#^[a-z][a-z0-9+\-.]*://#i', $this->$field)) {
                $this->$field = 'https://' . $this->$field;
            }
        }

        return parent::check();
    }

    /**
     * Method to bind an associative array or object to the Table instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   mixed  $array  An associative array or object to bind to the Table instance.
     * @param   mixed  $ignore  An optional array or space-separated list of properties to ignore while binding.
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

        return 'com_proclaim.podcast.' . (int)$this->$k;
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
        return 'JBS Podcast: ' . $this->title;
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID 1.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   ?Table  $table  A Table object for the asset parent.
     * @param   null                          $id     Id to look up
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
