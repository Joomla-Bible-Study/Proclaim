<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * TemplateCode table class
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class TableTemplatecode extends JTable
{
	/**
	 * File Name
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $filename;

	/**
	 * Type
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $type;

	/**
	 * Template Code
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $templatecode;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since 9.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__bsms_templatecode', 'id', $db);
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
		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overriden JTable::store to set modified data and user id.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return    boolean    True on success.
	 *
	 * @since    1.6
	 */
	public function store($updateNulls = false)
	{
		if ($this->filename == 'main' || $this->filename == 'simple' || $this->filename == 'custom' || $this->filename == 'formheader' || $this->filename == 'formfooter')
		{
			$this->setError(JText::_('JBS_STYLE_RESTRICED_FILE_NAME'));

			return false;
		}

		// Write the file
		jimport('joomla.client.helper');
		jimport('joomla.filesystem.file');
		$templatetype = $this->type;
		$filename     = 'default_' . $this->filename . '.php';
		$file         = null;

		switch ($templatetype)
		{
			case 1:
				// Sermons
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/sermons/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
			case 2:
				// Sermon
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/sermon/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
			case 3:
				// Teachers
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/teachers/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
			case 4:
				// Teacher
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/teacher/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
			case 5:
				// Seriesdisplays
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/seriesdisplays/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
			case 6:
				// Seriesdisplay
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/seriesdisplay/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
			case 7:
				// Model Display
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'modules/mod_biblestudy/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
		}

		$filecontent = $this->templatecode;

		// Check to see if there is the required code in the file
		$requiredtext = "defined('_JEXEC') or die;";
		$required     = substr_count($filecontent, $requiredtext);

		if (!$required)
		{
			$filecontent = $requiredtext . $filecontent;
		}

		if (!$return = JFile::write($file, $filecontent))
		{
			$this->setError(JText::_('JBS_STYLE_FILENAME_NOT_UNIQUE'));

			return false;
		}

		if (!$this->_rules)
		{
			$this->setRules('{"core.delete":[],"core.edit":[],"core.create":[],"core.edit.state":[],"core.edit.own":[]}');
		}

		return parent::store($updateNulls);
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTable/delete
	 * @since   11.1
	 */
	public function delete($pk = null)
	{
		jimport('joomla.client.helper');
		jimport('joomla.filesystem.file');
		$filename     = 'default_' . $this->filename . '.php';
		$templatetype = $this->type;
		$file         = null;

		switch ($templatetype)
		{
			case 1:
				// Sermons
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/sermons/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
			case 2:
				// Sermon
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/sermon/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
			case 3:
				// Teachers
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/teachers/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
			case 4:
				// Teacher
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/teacher/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
			case 5:
				// Seriesdisplays
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/seriesdisplays/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
			case 6:
				// Seriesdisplay
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components/com_biblestudy/views/seriesdisplay/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
			case 7:
				// Module's Display
				$file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'modules/mod_biblestudy/tmpl' . DIRECTORY_SEPARATOR . $filename;
				break;
		}

		if (JFile::exists($file))
		{
			if (!JFile::delete($file))
			{
				$this->setError(JText::_('JBS_STYLE_FILENAME_NOT_DELETED'));

				return false;
			}
		}

		return parent::delete($pk);
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form `table_name.id`
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since       1.6
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_biblestudy.templatecode.' . (int) $this->$k;
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
		$title = 'JBS Templatecode ' . $this->filename;

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
