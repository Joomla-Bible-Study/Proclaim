<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
/**
 * Table class for MediaFile
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class TableMediafile extends JTable
{

	/**
	 * Primary Key
	 *
	 * @var int
	 */
	public $id = null;

	/**
	 * Study ID
	 *
	 * @var string
	 */
	public $study_id = null;

	/**
	 * Media Image
	 *
	 * @var string
	 */
	public $media_image = null;

	/**
	 * Server
	 *
	 * @var string
	 */
	public $server = null;

	/**
	 * Path
	 *
	 * @var string
	 */
	public $path = null;

	/**
	 * Published
	 *
	 * @var string
	 */
	public $published = 1;

	/**
	 * Special
	 *
	 * @var string
	 */
	public $special = null;

	/**
	 * File Name
	 *
	 * @var string
	 */
	public $filename = null;

	/**
	 * File Size
	 *
	 * @var string
	 */
	public $size = null;

	/**
	 * File Mime Type
	 *
	 * @var string
	 */
	public $mime_type = null;

	/**
	 * Podcast ID
	 *
	 * @var string
	 */
	public $podcast_id = null;

	/**
	 * Internal Viewer
	 *
	 * @var string
	 */
	public $internal_viewer = null;

	/**
	 * Ordering
	 *
	 * @var string
	 */
	public $ordering = null;

	/**
	 * Media Code
	 *
	 * @var string
	 */
	public $mediacode = null;

	/**
	 * Create Date
	 *
	 * @var string
	 */
	public $createdate = null;

	/**
	 * Link type
	 *
	 * @var string
	 */
	public $link_type = null;

	/**
	 * Hits
	 *
	 * @var string
	 */
	public $hits = null;

	/**
	 * DocMan ID
	 *
	 * @var string
	 */
	public $docMan_id = null;

	/**
	 * Content Article ID
	 *
	 * @var string
	 */
	public $article_id = null;

	/**
	 * VirtueMart ID
	 *
	 * @var string
	 */
	public $virtueMart_id = null;

	/**
	 * Comment Text
	 *
	 * @var string
	 */
	public $comment = null;

	/**
	 * Params before jSon
	 *
	 * @var string
	 */
	public $params = null;

	/**
	 * Player state
	 *
	 * @var string
	 */
	public $player = null;

	/**
	 * Popup state
	 *
	 * @var string
	 */
	public $popup = null;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver &$db  Database connector object
	 */
	public function Tablemediafile(& $db)
	{
		parent::__construct('#__bsms_mediafiles', 'id', $db);
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed $array   An associative array or object to bind to the JTable instance.
	 * @param   mixed $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		// Bind the podcast_id
		if (isset($array['podcast_id']) && is_array($array['podcast_id']))
		{
			$array['podcast_id'] = implode(',', $array['podcast_id']);
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

		return 'com_biblestudy.mediafile.' . (int) $this->$k;
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
		$title = 'JBS Media File: ' . $this->filename . '-' . $this->id;

		return $title;
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 * By default, all assets are registered to the ROOT node with ID 1.
	 * The extended class can define a table and id to lookup.  If the
	 * asset does not exist it will be created.
	 *
	 * @param   JTable  $table  A JTable object for the asset parent.
	 * @param   integer $id     Id to look up
	 *
	 * @return  integer
	 *
	 * @since       1.6
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_biblestudy');

		return $asset->id;
	}

	/**
	 * Overloaded load function
	 *
	 * @param   int     $pk     primary key
	 * @param   boolean $reset  reset data
	 *
	 * @return      boolean
	 *
	 * @see JTable:load
	 */
	public function load($pk = null, $reset = true)
	{
		if (parent::load($pk, $reset))
		{
			// Convert the params field to a registry.
			$params = new JRegistry;
			$params->loadString($this->params);
			$this->params = $params;

			return true;
		}
		else
		{
			return false;
		}
	}

}
