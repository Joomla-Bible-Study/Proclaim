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

use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmstudyteacherHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Table class for Message
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmessageTable extends Table
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
     * Study Date
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $studydate = null;

    /**
     * Teacher id
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $teacher_id = null;

    /**
     * Study Number
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $studynumber = null;

    /**
     * Book Number
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $booknumber = null;

    /**
     * Chapter Begin
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $chapter_begin = null;

    /**
     * Verse Begin
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $verse_begin = null;

    /**
     * Chapter End
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $chapter_end = null;

    /**
     * Verse End
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $verse_end = null;

    /**
     * Secondary Reference
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $secondary_reference = null;

    /**
     * Book Number 2
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $booknumber2 = null;

    /**
     * Chapter Begin2
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $chapter_begin2 = null;

    /**
     * Verse Begin2
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $verse_begin2 = null;

    /**
     * Chapter End2
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $chapter_end2 = null;

    /**
     * Verse End2
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $verse_end2 = null;

    /**
     * Prod DVD
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $prod_dvd = null;

    /**
     * Prod CD
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $prod_cd = null;

    /**
     * Server CD
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $server_cd = null;

    /**
     * Server DVD
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $server_dvd = null;

    /**
     * Image CD
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $image_cd = null;

    /**
     * Image DVD
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $image_dvd = null;

    /**
     * Study Text 2
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $studytext2 = null;

    /**
     * Comments
     *
     * @var ?int
     *
     * @since 9.0.0
     */
    public ?int $comments = 1;

    /**
     * Hits
     *
     * @var ?int
     *
     * @since 9.0.0
     */
    public ?int $hits = 0;

    /**
     * User ID
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $user_id = null;

    /**
     * User Name
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $user_name = null;

    /**
     * Show Level
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $show_level = null;

    /**
     * Location ID
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $location_id = null;

    /**
     * Study Title
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $studytitle = null;

    /**
     * Alias
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $alias = null;

    /**
     * Study Intro
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $studyintro = null;

    /**
     * MessageType
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $messagetype = null;

    /**
     * Series ID
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $series_id = null;

    /**
     * Study Text
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $studytext = null;

    /**
     * ThumbNail Media
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $thumbnailm = null;

    /**
     * Full-size original image path
     *
     * @var string|null
     *
     * @since 10.1.0
     */
    public ?string $image = null;

    /**
     * ThumbNail Height
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $thumbhm = null;

    /**
     * ThumbNail Width
     *
     * @var int|null
     *
     * @since 9.0.0
     */
    public ?int $thumbwm = null;

    /**
     * Params
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $params = null;

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
     * Published
     *
     * @var ?int
     *
     * @since 9.0.0
     */
    public ?int $published = 1;

    /** @var ?string Publish Up
     *
     * @since 9.0.0
     */
    public ?string $publish_up = '0000-00-00 00:00:00';

    /** @var string|null Publish Down
     *
     * @since 9.0.0
     */
    public ?string $publish_down = '0000-00-00 00:00:00';

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
     * @var ?string
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
     * Ordering
     *
     * @var string|null
     *
     * @since 9.0.0
     */
    public ?string $ordering = null;

    /**
     * Language
     *
     * @var string|null
     * @since 9.0.0
     */
    public ?string $language = null;

    /**
     * Download ID
     *
     * @var int|null
     * @since 9.0.0
     */
    public ?int $download_id = null;

    /**
     * Message type name
     *
     * @var ?string
     * @since 10.1.0
     */
    public ?string $message_type = null;

    /**
     * Constructor.
     *
     * @param   DatabaseInterface  $db  Database connector object
     *
     * @since 9.0.0
     */
    public function __construct(&$db)
    {
        parent::__construct('#__bsms_studies', 'id', $db);
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
        if (trim($this->studytitle ?? '') === '') {
            throw new \UnexpectedValueException(Text::_('JBS_CMN_ERROR_TITLE_REQUIRED'));
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
     * Method to bind an associative array or object to the JTable instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   mixed  $array  An associative array or object to bind to the JTable instance.
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  bool  True on success.
     *
     * @link    http://docs.joomla.org/JTable/bind
     * @since   11.1
     */
    #[\Override]
    public function bind($array, $ignore = ''): bool
    {
        if (\array_key_exists('params', $array) && \is_array($array['params'])) {
            $registry = new Registry();
            $registry->loadArray($array['params']);
            $array['params'] = $registry->toString();
        }

        // Strip scriptures subform data — handled by CwmmessageModel, not a DB column
        unset($array['scriptures']);

        // Bind the rules.
        if (isset($array['rules']) && \is_array($array['rules'])) {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }

        // Cast typed int properties to prevent PHP 8.3 TypeError when form posts strings
        foreach ([
            'asset_id', 'created_by', 'modified_by', 'checked_out', 'access',
            'teacher_id', 'booknumber', 'chapter_begin', 'verse_begin',
            'chapter_end', 'verse_end', 'comments', 'hits', 'user_id',
            'location_id', 'series_id', 'thumbhm', 'thumbwm', 'published',
            'download_id',
        ] as $field) {
            if (isset($array[$field])) {
                $array[$field] = $array[$field] !== '' ? (int) $array[$field] : null;
            }
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Method to store a row in the database from the JTable instance properties.
     * If a primary key value is set the row with that primary key value will be
     * updated with the instance property values.  If no primary key value is set
     * a new row will be inserted into the database with the properties from the
     * Table instance.
     *
     * @param   bool  $updateNulls  True to update fields even if they are null.
     *
     * @return  bool  True on success.
     *
     * @link    https://docs.joomla.org/JTable/store
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
     * Ordering.
     *
     * @return void
     *
     * @since 9.0.0
     */
    public function ordering(): void
    {
        // No Data
    }

    /**
     * Method to delete a row from the database by primary key value.
     * Also cleans up associated image folder.
     *
     * @param   mixed  $pk  Primary key value to delete (null uses instance property)
     *
     * @return  bool  True on success
     *
     * @since 10.1.0
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
        if (!empty($this->thumbnailm) && str_contains($this->thumbnailm, 'images/biblestudy/studies/')) {
            $folderPath = \dirname($this->thumbnailm);
            Cwmthumbnail::deleteFolder($folderPath);
        }

        // Delete associated scripture references from junction table
        CwmscriptureHelper::deleteScriptures((int) $pk);

        // Delete associated study_teachers junction records
        CwmstudyteacherHelper::deleteTeachers((int) $pk);

        // Cascade-delete associated media files (triggers physical file cleanup)
        $this->deleteMediaFiles((int) $pk);

        return parent::delete($pk);
    }

    /**
     * Cascade-delete all media files associated with a message
     *
     * Each media file is deleted individually through CwmmediafileTable::delete()
     * so that physical file cleanup is triggered via the addon pattern.
     *
     * @param   int  $studyId  The message/study primary key
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function deleteMediaFiles(int $studyId): void
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->where($db->quoteName('study_id') . ' = ' . $studyId);
            $db->setQuery($query);
            $mediaIds = $db->loadColumn();

            if (empty($mediaIds)) {
                return;
            }

            // Get a media file table instance via MVCFactory
            $app        = Factory::getApplication();
            $mediaTable = $app->bootComponent('com_proclaim')
                ->getMVCFactory()
                ->createTable('Cwmmediafile', 'Administrator');

            $deleted = 0;

            foreach ($mediaIds as $mediaId) {
                try {
                    $mediaTable->reset();

                    if ($mediaTable->load((int) $mediaId)) {
                        $mediaTable->delete((int) $mediaId);
                        $deleted++;
                    }
                } catch (\Exception $e) {
                    Log::add(
                        'Message #' . $studyId . ': failed to cascade-delete media file #' . $mediaId
                        . ' — ' . $e->getMessage(),
                        Log::WARNING,
                        'com_proclaim'
                    );
                }
            }

            if ($deleted > 0) {
                Log::add(
                    'Message #' . $studyId . ': cascade-deleted ' . $deleted . ' media file(s)',
                    Log::INFO,
                    'com_proclaim'
                );
            }
        } catch (\Exception $e) {
            Log::add(
                'Message #' . $studyId . ': media file cascade query failed — ' . $e->getMessage(),
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
     * @return  string
     *
     * @since  1.6
     */
    #[\Override]
    protected function _getAssetName(): string
    {
        $k = $this->_tbl_key;

        return 'com_proclaim.message.' . (int)$this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string
     *
     * @since  1.6
     */
    #[\Override]
    protected function _getAssetTitle(): string
    {
        return 'JBS Message: ' . $this->studytitle;
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID 1.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   ?Table $table  A Table object for the asset parent.
     * @param   null   $id     Id to look up
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
