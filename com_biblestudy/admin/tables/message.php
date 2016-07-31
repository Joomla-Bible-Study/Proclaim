<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Table class for Message
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class TableMessage extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
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
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $teacher_id = null;

	/**
	 * Study Number
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $studynumber = null;

	/**
	 * Book Number
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $booknumber = null;

	/**
	 * Chapter Begin
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $chapter_begin = null;

	/**
	 * Verse Begin
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $verse_begin = null;

	/**
	 * Chapter End
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $chapter_end = null;

	/**
	 * Verse End
	 *
	 * @var int
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
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $chapter_begin2 = null;

	/**
	 * Verse Begin2
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $verse_begin2 = null;

	/**
	 * Chapter End2
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $chapter_end2 = null;

	/**
	 * Verse End2
	 *
	 * @var int
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
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $comments = 1;

	/**
	 * Hits
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $hits = 0;

	/**
	 * User ID
	 *
	 * @var int
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
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $show_level = null;

	/**
	 * Location ID
	 *
	 * @var int
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
	 * Media Hours
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $media_hours = null;

	/**
	 * Media Minutes
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $media_minutes = null;

	/**
	 * Media seconds
	 *
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $media_seconds = null;

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
	 * @var int
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
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $thumbhm = null;

	/**
	 * ThumbNail Width
	 *
	 * @var int
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
	 * @var int
	 *
	 * @since 9.0.0
	 */
	public $published = 1;

	/** @var string Publish Up
	 *
	 * @since 9.0.0 */
	public $publish_up = '0000-00-00 00:00:00';

	/** @var string Publish Down
	 *
	 * @since 9.0.0 */
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
	 * Constructor.
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
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
	 * @param   mixed  $array   An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/bind
	 * @since   11.1
	 */
	public function bind($array, $ignore = '')
	{
		if (array_key_exists('params', $array) && is_array($array['params']))
		{
			$registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
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
		if (!$this->_rules)
		{
			$this->setRules('{"core.delete":[],"core.edit":[],"core.create":[],"core.edit.state":[],"core.edit.own":[]}');
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

		return 'com_biblestudy.message.' . (int) $this->$k;
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
		$title = 'JBS Message: ' . $this->studytitle;

		return $title;
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID 1.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   JTable   $table  A JTable object for the asset parent.
	 * @param   integer  $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		/** @type JTableAsset $asset */
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_biblestudy');

		return $asset->id;
	}
}
