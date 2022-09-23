<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// No Direct Access
\defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Table\CWMAdminTable;
use CWM\Component\Proclaim\Site\Helper\CWMMedia;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\WorkflowBehaviorTrait;
use Joomla\CMS\MVC\Model\WorkflowModelInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Schema\ChangeSet;
use Joomla\CMS\Session\Session;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\UCM\UCMType;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Admin administrator model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMAdminModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'com_proclaim';

	/**
	 * The type alias for this content type (for example, 'com_content.article').
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = 'com_proclaim.admin';

	/**
	 * The context used for the associations table
	 *
	 * @var    string
	 * @since  3.4.4
	 */
	protected $associationsContext = 'com_proclaim.item';

	/**
	 * Name of the form
	 *
	 * @var string
	 * @since  4.0.0
	 */
	protected $formName = 'cpanel';

	/**
	 * @var null
	 * @since 7.0
	 */
	protected $changeSet = null;

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		if (empty($record->id) || $record->published != -2)
		{
			return false;
		}

		return Factory::getUser()->authorise('core.delete');
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		// Check against the category.
		if (!empty($record->catid))
		{
			return Factory::getUser()->authorise('core.edit.state');
		}

		// Default to component settings if category not known.
		return parent::canEditState($record);
	}

	/**
	 * Gets the form from the XML file.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		if (empty($data))
		{
			$this->getItem();
		}

		// Get the form.
		$form = $this->loadForm('com_proclaim.admin', 'admin', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return    boolean    True on success.
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
        $params = new Registry;
        $params->loadArray($data['params']);
        //load the image, then turn it into an array because Joomla's mediafield attaches metadata to the end. Then grab the URL from the array and save it.
        $image = HTMLHelper::cleanImageURL($params->get('media_image'));
        $params->set('media_image', $image->url);
        $image = HTMLHelper::cleanImageURL($params->get('jwplayer_logo'));
        $params->set('jwplayer_logo', $image->url);
        $image = HTMLHelper::cleanImageURL($params->get('jwplayer_image'));
        $params->set('jwplayer_image', $image->url);
        $image = HTMLHelper::cleanImageURL($params->get('default_study_image'));
        $params->set('default_study_image', $image->url);
        $image = HTMLHelper::cleanImageURL($params->get('default_showHide_image'));
        $params->set('default_showHide_image', $image->url);
        $image = HTMLHelper::cleanImageURL($params->get('default_download_image'));
        $params->set('default_download_image', $image->url);
        $image = HTMLHelper::cleanImageURL($params->get('default_teacher_image'));
        $params->set('default_teacher_image', $image->url);
        $image = HTMLHelper::cleanImageURL($params->get('default_series_image'));
        $params->set('default_series_image', $image->url);
        $image = HTMLHelper::cleanImageURL($params->get('default_main_image'));
        $params->set('default_main_image', $image->url);

        $data['params'] = $params->toArray();

		return parent::save($data);
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer  $pk  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   11.1
	 */
	public function checkout($pk = null)
	{
		return $pk;
	}

	/**
	 * Get Media Files
	 *
	 * @return mixed
	 *
	 * @since 7.0
	 *
	 * @todo  not sure if this should be here.
	 */
	public function getMediaFiles()
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__bsms_mediafiles');
		$db->setQuery($query->__toString());
		$mediafiles = $db->loadObjectList();

		foreach ($mediafiles as $i => $mediafile)
		{
			$reg = new Registry;
			$reg->loadString($mediafile->params);
			$mediafiles[$i]->params = $reg;
		}

		return $mediafiles;
	}

	/**
	 * Fixes database problems
	 *
	 * @return boolean
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function fix()
	{
		if (!$changeSet = $this->getItems())
		{
			return false;
		}

		$changeSet->fix();
		$this->fixSchemaVersion($changeSet);
		$this->fixUpdateVersion();
		$this->fixUpdateJBSMVersion();
		$installer = new CWMInstallModel;
		$installer->fixMenus();
		$installer->fixemptyaccess();
		$installer->fixemptylanguage();
		$this->fixDefaultTextFilters();

		/*
		 * Finally, if the schema updates succeeded, make sure the database is
		 * converted to utf8mb4 or, if not suported by the server, compatible to it.
		 */
		$installerJoomla = new ScriptJoomlaInstaller;
		$statusArray     = $changeSet->getStatus();

		if (count($statusArray['error']) === 0)
		{
			$installerJoomla->convertTablesToUtf8mb4(false);
		}

		return true;
	}

	/**
	 * Gets the ChangeSet object
	 *
	 * @return \Joomla\CMS\Schema\ChangeSet JSchema  ChangeSet
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getItems()
	{
		$folder = JPATH_ADMINISTRATOR . '/components/com_proclaim/install/sql/updates/';

		if ($this->changeSet !== null)
		{
			return $this->changeSet;
		}

		try
		{
			$this->changeSet = ChangeSet::getInstance(Factory::getDbo(), $folder);
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

			return false;
		}

		return $this->changeSet;
	}

	/**
	 * Fix schema version if wrong.
	 *
	 * @param   JSchemaChangeSet  $changeSet  Schema change set.
	 *
	 * @return   mixed  string schema version if success, false if fail
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function fixSchemaVersion($changeSet)
	{
		// Get correct schema version -- last file in array.
		$schema          = $changeSet->getSchema();
		$extensionresult = $this->getExtentionId();

		if ($schema == $this->getSchemaVersion())
		{
			return $schema;
		}

		// Delete old row
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->delete($db->qn('#__schemas'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extensionresult));
		$db->setQuery($query);
		$db->execute();

		// Add new row
		$query->clear()
			->insert($db->qn('#__schemas'))
			->columns($db->quoteName('extension_id') . ',' . $db->quoteName('version_id'))
			->values($db->quote($extensionresult) . ', ' . $db->quote($schema));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return $schema;
	}

	/**
	 * To retrieve component version
	 *
	 * @return string Version of component
	 *
	 * @since 1.7.3
	 */
	public function getCompVersion()
	{
		$jversion = null;
		$file     = JPATH_ADMINISTRATOR . '/components/com_proclaim/proclaim.xml';
		$xml      = simplexml_load_string(file_get_contents($file));

		if ($xml)
		{
			$jversion = (string) $xml->version;
		}

		return $jversion;
	}

	/**
	 * To retrieve component extension_id
	 *
	 * @return string extension_id
	 *
	 * @throws \Exception
	 * @since 7.1.0
	 */
	public function getExtentionId()
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('extension_id')->from($db->qn('#__extensions'))
			->where('element = ' . $db->q('com_proclaim'));
		$db->setQuery($query);
		$result = $db->loadResult();

		if (!$result)
		{
			throw new \Exception('Database error - getExtentionId');
		}

		return $result;
	}

	/**
	 * Get version from #__schemas table
	 *
	 * @return  mixed  the return value from the query, or null if the query fails
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getSchemaVersion()
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query           = $db->getQuery(true);
		$extensionresult = $this->getExtentionId();
		$query->select('version_id')->from($db->qn('#__schemas'))
			->where('extension_id = ' . $db->q($extensionresult));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Fix Joomla version in #__extensions table if wrong (doesn't equal JVersion short version)
	 *
	 * @return   mixed  string update version if success, false if fail
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function fixUpdateVersion()
	{
		$table = Table::getInstance('Extension');
		$table->load($this->getExtentionId());
		$cache         = new Registry($table->manifest_cache);
		$updateVersion = $cache->get('version');

		if ($updateVersion === $this->getCompVersion())
		{
			return $updateVersion;
		}

		$cache->set('version', $this->getCompVersion());
		$table->manifest_cache = $cache->toString();

		if ($table->store())
		{
			return $this->getCompVersion();
		}

		return false;
	}

	/**
	 * Get current version from #__bsms_update table.
	 *
	 * @return  mixed   version if successful, false if fail.
	 *
	 * @since 9.0.14
	 */
	public function getUpdateJBSMVersion()
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('version')
			->from('#__bsms_update')
			->order('id DESC');
		$db->setQuery($query, 0, 1);

		return $db->loadResult();
	}

	/**
	 * Fix Joomla version in #__bsms_updae table if wrong (doesn't equal JVersion short version).
	 *
	 * @return   mixed  string update version if success, false if fail.
	 *
	 * @since 9.0.14
	 */
	public function fixUpdateJBSMVersion()
	{
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('id, version')
			->from('#__bsms_update')
			->order('id DESC');
		$db->setQuery($query, 0, 1);

		$results = $db->loadObject();

		if ($results->version === $this->getCompVersion())
		{
			return $results->version;
		}

		$newid = $results->id + 1;

		$query->clear()
			->insert($db->qn('#__bsms_update'))
			->columns($db->qn('id') . ',' . $db->qn('version'))
			->values($db->q($newid) . ', ' . $db->q($this->getCompVersion()));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return $results->version;
	}

	/**
	 * Check if com_proclaim parameters are blank. If so, populate with com_content text filters.
	 *
	 * @return  mixed  boolean true if params are updated, null otherwise
	 *
	 * @since 7.0
	 */
	public function fixDefaultTextFilters()
	{
		$table = Table::getInstance('Extension');
		$table->load($table->find(array('name' => 'com_proclaim')));

		// Check for empty $config and non-empty content filters
		if (!$table->params)
		{
			// Get filters from com_content and store if you find them
			$contentParams = ComponentHelper::getParams('com_proclaim');

			if ($contentParams->get('filters'))
			{
				$newParams = new Registry;
				$newParams->set('filters', $contentParams->get('filters'));
				$table->params = (string) $newParams;
				$table->store();

				return true;
			}
		}

		return false;
	}

	/**
	 * Get Pagination state but is hard coded to be true right now.
	 *
	 * @return boolean
	 *
	 * @since 7.0
	 */
	public function getPagination()
	{
		return true;
	}

	/**
	 * Get current version from #__extensions table
	 *
	 * @return  mixed   version if successful, false if fail
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getUpdateVersion()
	{
		$table = Table::getInstance('Extension');
		$table->load($this->getExtentionId());
		$cache = new Registry($table->manifest_cache);

		return $cache->get('version');
	}

	/**
	 * Check if com_proclaim parameters are blank.
	 *
	 * @return  string  default text filters (if any)
	 *
	 * @since 7.0
	 */
	public function getDefaultTextFilters()
	{
		$table = Table::getInstance('Extension');
		$table->load($table->find(array('name' => 'com_proclaim')));

		return $table->params;
	}

	/**
	 * Check for SermonSpeaker and PreachIt
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public function getSSorPI()
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('extension_id, name, element')->from('#__extensions');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Change Player based off MimeType or Extension of File Name
	 *
	 * @return string
	 *
	 * @since 9.0.12
	 */
	public function playerByMediaType()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$db   = Factory::getContainer()->get('DatabaseDriver');
		$msg  = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
		$post = $_POST['jform'];
		$reg  = new Registry;
		$reg->loadArray($post['params']);
		$from    = $reg->get('mtFrom', 'x');
		$to      = $reg->get('mtTo', 'x');
		$account = 0;
		$count   = 0;

		$MediaHelper = new CWMMedia;
		$mimetypes   = $MediaHelper->getMimetypes();

		if ($from !== 'x')
		{
			$key = array_search($from, $mimetypes);
		}
		else
		{
			return 'No Selection Made';
		}

		$query = $db->getQuery(true);
		$query->select('id, params')
			->from('#__bsms_mediafiles')
			->where('published = ' . $db->q('1'));
		$db->setQuery($query);

		foreach ($db->loadObjectList() as $media)
		{
			$count++;
			$search = false;
			$isfrom = '';
			$reg    = new Registry;
			$reg->loadString($media->params);
			$filename  = $reg->get('filename', '');
			$mediacode = $reg->get('mediacode');

			$extension = substr($filename, strrpos($filename, '.') + 1);

			if ($from === 'http' && strpos($filename, 'http') !== false)
			{
				$reg->set('mime_type', ' ');
				$isfrom = 'http';
				$search = true;
			}

			if (!empty($mediacode) && $from === 'mediacode')
			{
				$reg->set('mime_type', ' ');
				$isfrom = 'mediacode';
				$search = true;
			}

			if (strpos($key, $extension) !== false || $reg->get('mime_type', 0) == $from)
			{
				$reg->set('mime_type', $from);
				$isfrom = 'Extenstion';
				$search = true;
			}

			if ($search && !empty($isfrom))
			{
				$account++;

				if (JBSMDEBUG)
				{
					$msg .= ' From: ' . $isfrom . '<br />';

					if ($reg->get('mime_type', 0) == $from)
					{
						$msg .= ' MimeType: ' . $reg->get('mime_type') . '<br />';
					}

					$msg .= ' Search found FileName: ' . $filename . '<br />';
				}

				$reg->set('player', $to);

				$query = $db->getQuery(true);
				$query->update('#__bsms_mediafiles')
					->set('params = ' . $db->q($reg->toString()))
					->where('id = ' . (int) $media->id);
				$db->setQuery($query);

				if (!$db->execute())
				{
					return Text::_('JBS_ADM_ERROR_OCCURED');
				}
			}
		}

		return $msg . ' ' . $account;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   ?
	 * @param   string  $direction  ?
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since    1.7.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication();
		$this->setState('message', $app->getUserState('com_proclaim.message'));
		$this->setState('extension_message', $app->getUserState('com_proclaim.extension_message'));
		$app->setUserState('com_proclaim.message', '');
		$app->setUserState('com_proclaim.extension_message', '');
		parent::populateState();
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   CWMAdminTable  $table  A JTable object.
	 *
	 * @return   void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		// Reorder the articles within the category so the new article is first
		if (empty($table->id))
		{
			$table->id = 1;
		}
	}

	/**
	 * Load Form Date
	 *
	 * @return object
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_proclaim.edit.administration.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Custom clean the cache of com_biblestudy and biblestudy modules
	 *
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_biblestudy');
		parent::cleanCache('mod_biblestudy');
	}
}
