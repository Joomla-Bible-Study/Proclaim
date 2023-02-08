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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\CWMAssets;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Podcast table class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMPodcastTable extends Table
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
	 * Published
	 *
	 * @var integer
	 *
	 * @since 9.0.0
	 */
	public $published = 1;

	/**
	 * Title
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $title = null;

	/**
	 * Website Address
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $website = null;

	/**
	 * Description
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $description = null;

	/**
	 * Image
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $image = null;

	/**
	 * Image Height
	 *
	 * @var integer
	 *
	 * @since 9.0.0
	 */
	public $imageh = null;

	/**
	 * Image Width
	 *
	 * @var integer
	 *
	 * @since 9.0.0
	 */
	public $imagew = null;

	/**
	 * Author
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $author = null;

	/**
	 * Podcast Image
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $podcastimage = null;

	/**
	 * Podcast Summary
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $podcastsummary = null;

	/**
	 * Podcast Search Words
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $podcastsearch = null;

	/**
	 * File Name
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $filename = null;

	/**
	 * Language of Podcast
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $language = null;

	/**
	 * Podcast name
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $podcastname = null;

	/**
	 * Editor Name
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $editor_name = null;

	/**
	 * Editor Email Address
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $editor_email = null;

	/**
	 * Limit of the episodes in the podcast
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $podcastlimit = null;

	/**
	 * Episode Title
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $episodetitle = null;

	/**
	 * Custom
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $custom = null;

	/**
	 * Details template ID
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $episodesubtitle = null;

	/**
	 * Custom
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $customsubtitle = null;

	/**
	 * Details template ID
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $detailstemplateid = null;

	/**
	 * Type of link to use for podcast.
	 *
	 * 0 = Default is to episode.
	 * 1 = Direct Link.
	 * 2 = Popup Player Window with default player as internal.
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $linktype = null;

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
	public function bind($array, $ignore = '')
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
	 * @link    https://docs.joomla.org/Table/store
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
	 * Method to compute the default name of the asset.
	 * The default name is in the form `table_name.id`
	 * where id is the value of the primary key of the table.
	 *
	 * @return      string
	 *
	 * @since       1.6
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_proclaim.podcast.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return      string
	 *
	 * @since       1.6
	 */
	protected function _getAssetTitle()
	{
		return 'JBS Podcast: ' . $this->title;
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
		return CWMAssets::parentId();
	}
}
