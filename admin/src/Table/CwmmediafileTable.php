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

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Table class for MediaFile
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmediafileTable extends Table
{
    /**
     * Primary Key
     *
     * @var int|null
     * @since    7.0.0
     */
    public ?int $id = null;

    /**
     * Study id
     *
     * @var int|null
     * @since    7.0.0
     */
    public ?int $study_id = null;

    /**
     * Server id
     *
     * @var int|null
     * @since    7.0.0
     */
    public ?int $server_id = null;

    /**
     * Podcast ID
     *
     * @var string|null
     * @since    7.0.0
     */
    public ?string $podcast_id = null;

    /**
     * Hold transitive data (i.e statistics)
     *
     * @var string|null
     * @since    7.0.0
     */
    public ?string $metadata = '';

    /**
     * Ordering
     *
     * @var string|null
     * @since    7.0.0
     */
    public ?string $ordering = null;

    /**
     * Create Date
     *
     * @var string|null
     * @since    7.0.0
     */
    public ?string $createdate = null;

    /**
     * Hits
     *
     * @var int
     * @since 9.0.0
     */
    public int $hits = 0;

    /**
     * Published
     *
     * @var int
     * @since    7.0.0
     */
    public int $published = 1;

    /**
     * Comment Text
     *
     * @var string|null
     * @since    7.0.0
     */
    public ?string $comment = null;

    /**
     * Downloads
     *
     * @var int
     * @since 9.0.0
     */
    public int $downloads = 0;

    /**
     * Plays
     *
     * @var int
     * @since 9.0.0
     */
    public int $plays = 0;

    /**
     * Media configuration
     *
     * @var string|null
     * @since    7.0.0
     */
    public ?string $params = null;

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
     * Language
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $language = null;

    /**
     * Created date
     *
     * @var string|null
     * @since    10.0.0
     */
    public ?string $created = null;

    /**
     * Created by user ID
     *
     * @var int|null
     * @since 10.0.0
     */
    public ?int $created_by = null;

    /**
     * Created by alias
     *
     * @var string
     * @since 10.0.0
     */
    public string $created_by_alias = '';

    /**
     * Modified date
     *
     * @var string|null
     * @since 10.0.0
     */
    public ?string $modified = null;

    /**
     * Modified by user ID
     *
     * @var int|null
     * @since 10.0.0
     */
    public ?int $modified_by = null;

    /**
     * Checked Out
     *
     * @var int|null
     * @since 9.0.0
     */
    public ?int $checked_out = null;

    /**
     * Checked Out Time
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $checked_out_time = null;

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
     * @return  bool  True on success.
     *
     * @since    7.0.0
     */
    #[\Override]
    public function bind($array, $ignore = ''): bool
    {
        if (isset($array['params']) && \is_array($array['params'])) {
            $registry = new Registry();
            $registry->loadArray($array['params']);
            $array['params'] = (string)$registry;
        }

        // Bind the podcast_id
        if (isset($array['podcast_id']) && \is_array($array['podcast_id'])) {
            $array['podcast_id'] = implode(',', $array['podcast_id']);
        }

        // Bind the rules.
        if (isset($array['rules']) && \is_array($array['rules'])) {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     * If a primary key value is set, the row with that primary key value will be
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
     * Also attempts to delete the physical file from disk if the server supports it.
     *
     * @param   mixed  $pk  Primary key value to delete (null uses instance property)
     *
     * @return  bool  True on success
     *
     * @since   10.1.0
     */
    #[\Override]
    public function delete($pk = null): bool
    {
        $pk = $pk ?? $this->id;

        // Load record to get server_id and params before deletion
        if ($pk !== $this->id) {
            $this->load($pk);
        }

        // Attempt physical file deletion — never block DB deletion
        $this->deletePhysicalFile();

        return parent::delete($pk);
    }

    /**
     * Attempt to delete the physical file associated with this media record
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function deletePhysicalFile(): void
    {
        try {
            // Check user choice from delete confirmation dialog (default 1 = delete files)
            $deletePhysical = (int) Factory::getApplication()->getInput()->get('delete_physical_files', 1, 'int');

            if ($deletePhysical === 0) {
                Log::add(
                    'Media file #' . ($this->id ?? '?') . ': physical file deletion skipped by user choice',
                    Log::INFO,
                    'com_proclaim'
                );

                return;
            }

            if (empty($this->server_id)) {
                return;
            }

            // Load server record
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([$db->quoteName('type'), $db->quoteName('params')])
                ->from($db->quoteName('#__bsms_servers'))
                ->where($db->quoteName('id') . ' = ' . (int) $this->server_id);
            $db->setQuery($query);
            $server = $db->loadObject();

            if (!$server || empty($server->type)) {
                return;
            }

            // Parse media file params to get filename
            $mediaParams = new Registry($this->params ?: '{}');
            $filename    = $mediaParams->get('filename', '');

            if (empty($filename)) {
                return;
            }

            // Parse server params
            $serverParams = new Registry($server->params ?: '{}');

            // Get addon and attempt deletion
            $addon = CWMAddon::getInstance($server->type);
            $addon->deleteFile($filename, $serverParams);
        } catch (\Exception $e) {
            Log::add(
                'Media file #' . ($this->id ?? '?') . ': physical file deletion failed — ' . $e->getMessage(),
                Log::WARNING,
                'com_proclaim'
            );
        }
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

        return 'com_proclaim.mediafile.' . (int)$this->$k;
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
        return 'JBS Media File: ' . $this->id;
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
