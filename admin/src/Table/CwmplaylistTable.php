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
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Playlist table class
 *
 * Represents a synchronised playlist (e.g. a YouTube playlist) as a first-class
 * Proclaim entity, distinct from a Series. A playlist may optionally back a
 * Series but also exists for cross-cutting groupings (e.g. "All Church Services").
 *
 * @package  Proclaim.Admin
 * @since    10.3.3
 */
class CwmplaylistTable extends Table
{
    /**
     * Primary Key
     *
     * @var int|null
     * @since 10.3.3
     */
    public ?int $id = null;

    /**
     * Published state
     *
     * @var int|null
     * @since 10.3.3
     */
    public ?int $published = 1;

    /**
     * Playlist title (display name)
     *
     * @var string|null
     * @since 10.3.3
     */
    public ?string $title = null;

    /**
     * URL alias
     *
     * @var string|null
     * @since 10.3.3
     */
    public ?string $alias = null;

    /**
     * Description
     *
     * @var string|null
     * @since 10.3.3
     */
    public ?string $description = null;

    /**
     * Remote playlist ID (e.g. YouTube playlist ID)
     *
     * @var string|null
     * @since 10.3.3
     */
    public ?string $remote_playlist_id = null;

    /**
     * Server ID (FK to #__bsms_servers) that hosts the playlist
     *
     * @var int|null
     * @since 10.3.3
     */
    public ?int $server_id = null;

    /**
     * Optional Series ID (FK to #__bsms_series) this playlist backs
     *
     * @var int|null
     * @since 10.3.3
     */
    public ?int $series_id = null;

    /**
     * Default settings applied to synced videos (JSON)
     *
     * @var string|null
     * @since 10.3.3
     */
    public ?string $default_settings = null;

    /**
     * Whether scheduled sync is enabled for this playlist
     *
     * @var int|null
     * @since 10.3.3
     */
    public ?int $sync_enabled = 0;

    /**
     * Whether local title changes are pushed back to the remote platform
     *
     * @var int|null
     * @since 10.3.3
     */
    public ?int $writeback_enabled = 0;

    /**
     * Timestamp of the last successful sync
     *
     * @var string|null
     * @since 10.3.3
     */
    public ?string $last_sync = null;

    /**
     * Ordering
     *
     * @var int|null
     * @since 10.3.3
     */
    public ?int $ordering = null;

    /**
     * Asset ID
     *
     * @var int|null
     * @since 10.3.3
     */
    public ?int $asset_id = null;

    /**
     * Access Level
     *
     * @var int|null
     * @since 10.3.3
     */
    public ?int $access = null;

    /**
     * Language
     *
     * @var string|null
     * @since 10.3.3
     */
    public ?string $language = null;

    /**
     * Params
     *
     * @var string|null
     * @since 10.3.3
     */
    public ?string $params = null;

    /**
     * Created date
     *
     * @var string|null
     * @since 10.3.3
     */
    public ?string $created = null;

    /**
     * Created by user ID
     *
     * @var int|null
     * @since 10.3.3
     */
    public ?int $created_by = null;

    /**
     * Created by alias
     *
     * @var string
     * @since 10.3.3
     */
    public ?string $created_by_alias = '';

    /**
     * Modified date
     *
     * @var string|null
     * @since 10.3.3
     */
    public ?string $modified = null;

    /**
     * Modified by user ID
     *
     * @var int|null
     * @since 10.3.3
     */
    public ?int $modified_by = null;

    /**
     * Checked out user ID
     *
     * @var int|null
     * @since 10.3.3
     */
    public ?int $checked_out = null;

    /**
     * Checked out time
     *
     * @var string|null
     * @since 10.3.3
     */
    public ?string $checked_out_time = null;

    /**
     * Constructor
     *
     * @param   DatabaseInterface  $db  Database connector object
     *
     * @since 10.3.3
     */
    public function __construct(&$db)
    {
        parent::__construct('#__bsms_playlists', 'id', $db);
    }

    /**
     * Perform pre-save checks on the table properties.
     *
     * @return  bool  True if checks pass.
     *
     * @throws  \UnexpectedValueException
     *
     * @since   10.3.3
     */
    #[\Override]
    public function check(): bool
    {
        if (trim($this->title ?? '') === '') {
            throw new \UnexpectedValueException(Text::_('JBS_CMN_ERROR_PLAYLIST_TITLE_REQUIRED'));
        }

        // Auto-generate an alias from the title when none was supplied.
        if (trim($this->alias ?? '') === '') {
            $this->alias = $this->title;
        }

        $this->alias = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($this->alias);

        if (trim(str_replace('-', '', $this->alias)) === '') {
            $this->alias = \Joomla\CMS\Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return parent::check();
    }

    /**
     * Method to bind an associative array or object to the Table instance.
     *
     * @param   mixed  $array   An associative array or object to bind to the Table instance.
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  bool  True on success.
     *
     * @since   10.3.3
     */
    #[\Override]
    public function bind($array, $ignore = ''): bool
    {
        foreach (['params', 'default_settings'] as $jsonField) {
            if (isset($array[$jsonField]) && \is_array($array[$jsonField])) {
                $registry = new Registry();
                $registry->loadArray($array[$jsonField]);
                $array[$jsonField] = (string) $registry;
            }
        }

        // Bind the rules.
        if (isset($array['rules']) && \is_array($array['rules'])) {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }

        // Cast typed int properties to prevent PHP 8.3 TypeError when form posts strings.
        foreach ([
            'id', 'published', 'asset_id', 'access', 'server_id', 'series_id',
            'sync_enabled', 'writeback_enabled', 'ordering', 'created_by', 'modified_by', 'checked_out',
        ] as $field) {
            if (isset($array[$field])) {
                $array[$field] = $array[$field] !== '' ? (int) $array[$field] : null;
            }
        }

        // Nullify empty nullable datetime columns so MySQL strict mode does not
        // reject empty strings (e.g. last_sync on a never-synced playlist).
        foreach (['last_sync', 'created', 'modified', 'checked_out_time'] as $dateField) {
            if (isset($array[$dateField]) && trim((string) $array[$dateField]) === '') {
                $array[$dateField] = null;
            }
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     *
     * @param   bool  $updateNulls  True to update fields even if they are null.
     *
     * @return  bool  True on success.
     *
     * @since   10.3.3
     */
    #[\Override]
    public function store($updateNulls = false): bool
    {
        // remote_playlist_uk exists only to carry uq_server_remote_playlist; the
        // database computes it from remote_playlist_id and rejects any statement
        // that writes it. Table picks it up as an ordinary column, so load() sets
        // it and the follow-up update would carry it into the SET clause.
        unset($this->remote_playlist_uk);

        $result = parent::store($updateNulls);

        if ($result) {
            Cwmassets::stripEmptyAssetRow($this);
        }

        return $result;
    }

    /**
     * Method to compute the default name of the asset.
     *
     * @return  string
     *
     * @since   10.3.3
     */
    #[\Override]
    protected function _getAssetName(): string
    {
        $k = $this->_tbl_key;

        return 'com_proclaim.playlist.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string
     *
     * @since   10.3.3
     */
    #[\Override]
    protected function _getAssetTitle(): string
    {
        return 'JBS Playlist: ' . $this->title;
    }

    /**
     * Method to get the parent asset under which to register this one.
     *
     * @param   ?Table  $table  A Table object for the asset parent.
     * @param   null    $id     Id to look up
     *
     * @return  int
     *
     * @since   10.3.3
     */
    #[\Override]
    protected function _getAssetParentId(?Table $table = null, $id = null): int
    {
        // Parent to the section, so a rule on com_proclaim.playlist reaches
        // this record's own asset instead of being bypassed by it.
        return Cwmassets::sectionParentId('playlist');
    }
}
