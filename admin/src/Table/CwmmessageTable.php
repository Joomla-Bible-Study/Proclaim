<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
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
     * @var integer
     *
     * @since 9.0.0
     */
    public $id = null;

    /**
     * Study Date
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $studydate = null;

    /**
     * Teacher id
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $teacher_id = null;

    /**
     * Study Number
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $studynumber = null;

    /**
     * Book Number
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $booknumber = null;

    /**
     * Chapter Begin
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $chapter_begin = null;

    /**
     * Verse Begin
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $verse_begin = null;

    /**
     * Chapter End
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $chapter_end = null;

    /**
     * Verse End
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $verse_end = null;

    /**
     * Secondary Reference
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $secondary_reference = null;

    /**
     * Book Number 2
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $booknumber2 = null;

    /**
     * Chapter Begin2
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $chapter_begin2 = null;

    /**
     * Verse Begin2
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $verse_begin2 = null;

    /**
     * Chapter End2
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $chapter_end2 = null;

    /**
     * Verse End2
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $verse_end2 = null;

    public $prod_dvd;

    public $prod_cd;

    public $server_cd;

    public $server_dvd;

    public $image_cd;

    public $image_dvd;

    public $studytext2;

    /**
     * Comments
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $comments = 1;

    /**
     * Hits
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $hits = 0;

    /**
     * User ID
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $user_id = null;

    /**
     * User Name
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $user_name = null;

    /**
     * Show Level
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $show_level = null;

    /**
     * Location ID
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $location_id = null;

    /**
     * Study Title
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $studytitle = null;

    /**
     * Alias
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $alias = null;

    /**
     * Study Intro
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $studyintro = null;

    /**
     * MessageType
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $messagetype = null;

    /**
     * Series ID
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $series_id = null;

    /**
     * Study Text
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $studytext = null;

    /**
     * ThumbNail Media
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $thumbnailm = null;

    /**
     * ThumbNail Height
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $thumbhm = null;

    /**
     * ThumbNail Width
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $thumbwm = null;

    /**
     * Params
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $params = null;

    public $checked_out;

    public $checked_out_time;

    /**
     * Published
     *
     * @var integer
     *
     * @since 9.0.0
     */
    public $published = 1;

    /** @var string Publish Up
     *
     * @since 9.0.0
     */
    public $publish_up = '0000-00-00 00:00:00';

    /** @var string Publish Down
     *
     * @since 9.0.0
     */
    public $publish_down = '0000-00-00 00:00:00';

    public $modified;

    public $modified_by;

    public $asset_id;

    public $access;

    /**
     * Ordering
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $ordering = null;

    public $language;

    public $download_id;

    /**
     * @var string|null
     * @since version
     */
    public ?string $message_type;

    /**
     * Constructor.
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since 9.0.0
     */
    public function __construct(&$db)
    {
        parent::__construct('#__bsms_studies', 'id', $db);
    }

    /**
     * Method to bind an associative array or object to the JTable instance.This
     * method only binds properties that are publicly accessible and optionally
     * takes an array of properties to ignore when binding.
     *
     * @param   mixed  $array  An associative array or object to bind to the JTable instance.
     * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  boolean  True on success.
     *
     * @link    http://docs.joomla.org/JTable/bind
     * @since   11.1
     */
    public function bind($array, $ignore = ''): bool
    {
        if (array_key_exists('params', $array) && is_array($array['params'])) {
            $registry = new Registry;
            $registry->loadArray($array['params']);
            $array['params'] = $registry->toString();
        }

        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
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
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @link    https://docs.joomla.org/JTable/store
     * @since   11.1
     */
    public function store($updateNulls = false)
    {
        if (!$this->_rules) {
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
    public function ordering()
    {
        // No Data
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
    protected function _getAssetName()
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
    protected function _getAssetTitle()
    {
        return 'JBS Message: ' . $this->studytitle;
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
        // Get Proclaim Root ID
        return Cwmassets::parentId();
    }
}
