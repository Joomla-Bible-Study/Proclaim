<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Table\CwmadminTable;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Schema\ChangeSet;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\Registry\Registry;

/**
 * Admin administrator model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmadminModel extends AdminModel
{
    use VersionableModelTrait;

    /**
     * The type alias for this content type (for example, 'com_content.article').
     *
     * @var    string
     * @since  3.2
     */
    public $typeAlias = 'com_proclaim.cwmadmin';
    /**
     * @var        string    The prefix to use with controller messages.
     * @since    1.6
     */
    protected $text_prefix = 'com_proclaim';
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
    protected $formName = 'cwmcpanel';

    /**
     * @var null
     * @since 7.0
     */
    protected $changeSet = null;

    /**
     * Gets the form from the XML file.
     *
     * @param   array  $data      Data for the form.
     * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @throws Exception
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true): mixed
    {
        if (empty($data)) {
            $this->getItem();
        }

        // Get the form.
        $form = $this->loadForm('com_proclaim.admin', 'admin', array('control' => 'jform', 'load_data' => $loadData));

        if ($form === null) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @throws  Exception
     * @since   3.0
     */
    public function getTable($name = 'Cwmadmin', $prefix = '', $options = array()): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return    bool    True on success.
     *
     * @since    1.6
     */
    public function save($data): bool
    {
        $params = new Registry();
        $params->loadArray($data['params']);

        // Load the image, then turn it into an array because Joomla's mediafield
        // attaches metadata to the end. Then grab the URL from the array and save it.
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
     * @param   null  $pk  The numeric id of the primary key.
     *
     * @return bool|int|null False on failure or error, true otherwise.
     *
     * @since   11.1
     */
    public function checkout($pk = null): bool|int|null
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
    public function getMediaFiles(): mixed
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__bsms_mediafiles');
        $db->setQuery($query->__toString());
        $mediafiles = $db->loadObjectList();

        foreach ($mediafiles as $i => $mediafile) {
            $reg = new Registry();
            $reg->loadString($mediafile->params);
            $mediafiles[$i]->params = $reg;
        }

        return $mediafiles;
    }

    /**
     * Fixes database problems
     *
     * @return bool
     *
     * @throws Exception
     * @since 7.0
     */
    public function fix(): bool
    {
        if (!$changeSet = $this->getItems()) {
            return false;
        }

        $changeSet->fix();
        $this->fixSchemaVersion($changeSet);
        $this->fixUpdateVersion();
        $installer = new CwminstallModel();
        $installer->fixMenus();
        $installer->fixemptyaccess();
        $installer->fixemptylanguage();
        $this->fixDefaultTextFilters();

        return true;
    }

    /**
     * Gets the ChangeSet object
     *
     * @return ChangeSet|boolean|null JSchema  ChangeSet
     *
     * @throws Exception
     * @since 7.0
     */
    public function getItems(): ChangeSet|bool|null
    {
        $folder = JPATH_ADMINISTRATOR . '/components/com_proclaim/sql/updates/';

        if ($this->changeSet !== null) {
            return $this->changeSet;
        }

        try {
            $this->changeSet = ChangeSet::getInstance(Factory::getDbo(), $folder);
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

            return false;
        }

        return $this->changeSet;
    }

    /**
     * Fix a schema version if wrong.
     *
     * @param   ChangeSet  $changeSet  Schema change set.
     *
     * @return   mixed  string schema version of success, false if fail
     *
     * @throws Exception
     * @since 7.0
     */
    public function fixSchemaVersion(ChangeSet $changeSet): mixed
    {
        // Get a correct schema version -- last file in array.
        $schema          = $changeSet->getSchema();
        $extensionresult = $this->getExtentionId();

        if ($schema === $this->getSchemaVersion()) {
            return $schema;
        }

        // Delete old row
        $db    = Factory::getContainer()->get('DatabaseDriver');
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

        try {
            $db->execute();
        } catch (Exception $e) {
            return false;
        }

        return $schema;
    }

    /**
     * To retrieve component extension_id
     *
     * @return string extension_id
     *
     * @throws Exception
     * @since 7.1.0
     */
    public function getExtentionId(): string
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('extension_id')->from($db->qn('#__extensions'))
            ->where('element = ' . $db->q('com_proclaim'));
        $db->setQuery($query);
        $result = $db->loadResult();

        if (!$result) {
            throw new \RuntimeException('Database error - getExtentionId');
        }

        return $result;
    }

    /**
     * Get a version from #__schemas table
     *
     * @return  mixed  the return value from the query, or null if the query fails
     *
     * @throws Exception
     * @since 7.0
     */
    public function getSchemaVersion(): mixed
    {
        $db              = Factory::getContainer()->get('DatabaseDriver');
        $query           = $db->getQuery(true);
        $extensionresult = $this->getExtentionId();
        $query->select('version_id')->from($db->qn('#__schemas'))
            ->where('extension_id = ' . $db->q($extensionresult));
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Fix a Joomla version in #__extensions table if wrong (doesn't equal JVersion short version)
     *
     * @return   mixed  string update version if success, false if fail
     *
     * @throws Exception
     * @since 7.0
     */
    public function fixUpdateVersion(): mixed
    {
        $table = Table::getInstance('Extension');
        $table->load($this->getExtentionId());
        $cache         = new Registry($table->manifest_cache);
        $updateVersion = $cache->get('version');

        if ($updateVersion === $this->getCompVersion()) {
            return $updateVersion;
        }

        $cache->set('version', $this->getCompVersion());
        $table->manifest_cache = $cache->toString();

        if ($table->store()) {
            return $this->getCompVersion();
        }

        return false;
    }

    /**
     * To retrieve component version
     *
     * @return string Version of component
     *
     * @since 1.7.3
     */
    public function getCompVersion(): string
    {
        $jversion = '';
        $file     = JPATH_ADMINISTRATOR . '/components/com_proclaim/proclaim.xml';
        $xml      = simplexml_load_string(file_get_contents($file));

        if ($xml) {
            $jversion = (string)$xml->version;
        }

        return $jversion;
    }

    /**
     * Check if com_proclaim parameters are blank. If so, populate with com_content text filters.
     *
     * @return  mixed  boolean true if params are updated, null otherwise
     *
     * @since 7.0
     */
    public function fixDefaultTextFilters(): mixed
    {
        $table = Table::getInstance('Extension');
        $table->load($table->find(array('name' => 'com_proclaim')));

        // Check for empty $config and non-empty content filters
        if (!$table->params) {
            // Get filters from com_content and store if you find them
            $contentParams = ComponentHelper::getParams('com_proclaim');

            if ($contentParams->get('filters')) {
                $newParams = new Registry();
                $newParams->set('filters', $contentParams->get('filters'));
                $table->params = (string)$newParams;
                $table->store();

                return true;
            }
        }

        return false;
    }

    /**
     * Get Pagination state but is hard coded to be true right now.
     *
     * @return bool
     *
     * @since 7.0
     */
    public function getPagination(): bool
    {
        return true;
    }

    /**
     * Get current version from #__extensions table
     *
     * @return  mixed   version if successful, false if fail
     *
     * @throws Exception
     * @since 7.0
     */
    public function getUpdateVersion(): mixed
    {
        $table = Table::getInstance('Extension');
        $table->load($this->getExtentionId());

        return (new Registry($table->manifest_cache))->get('version');
    }

    /**
     * Check if com_proclaim parameters are blank.
     *
     * @return  Registry  default text filters (if any)
     *
     * @since 7.0
     */
    public function getDefaultTextFilters(): Registry
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
    public function getSSorPI(): object
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
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
    public function playerByMediaType(): string
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $db   = Factory::getContainer()->get('DatabaseDriver');
        $msg  = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
        $post = $_POST['jform'];
        $reg  = new Registry();
        $reg->loadArray($post['params']);
        $from    = $reg->get('mtFrom', 'x');
        $to      = $reg->get('mtTo', 'x');
        $account = 0;
        $count   = 0;

        $MediaHelper = new Cwmmedia();
        $mimetypes   = $MediaHelper->getMimetypes();

        if ($from !== 'x') {
            $key = array_search($from, $mimetypes);
        } else {
            return 'No Selection Made';
        }

        $query = $db->getQuery(true);
        $query->select('id, params')
            ->from('#__bsms_mediafiles')
            ->where('published = ' . $db->q('1'));
        $db->setQuery($query);

        foreach ($db->loadObjectList() as $media) {
            $count++;
            $search = false;
            $isfrom = '';
            $reg    = new Registry();
            $reg->loadString($media->params);
            $filename  = $reg->get('filename', '');
            $mediacode = $reg->get('mediacode');

            $extension = substr($filename, strrpos($filename, '.') + 1);

            if ($from === 'http' && strpos($filename, 'http') !== false) {
                $reg->set('mime_type', ' ');
                $isfrom = 'http';
                $search = true;
            }

            if (!empty($mediacode) && $from === 'mediacode') {
                $reg->set('mime_type', ' ');
                $isfrom = 'mediacode';
                $search = true;
            }

            if (strpos($key, $extension) !== false || $reg->get('mime_type', 0) == $from) {
                $reg->set('mime_type', $from);
                $isfrom = 'Extenstion';
                $search = true;
            }

            if ($search && !empty($isfrom)) {
                $account++;

                if (JBSMDEBUG) {
                    $msg .= ' From: ' . $isfrom . '<br />';

                    if ($reg->get('mime_type', 0) == $from) {
                        $msg .= ' MimeType: ' . $reg->get('mime_type') . '<br />';
                    }

                    $msg .= ' Search found FileName: ' . $filename . '<br />';
                }

                $reg->set('player', $to);

                $query = $db->getQuery(true);
                $query->update('#__bsms_mediafiles')
                    ->set('params = ' . $db->q($reg->toString()))
                    ->where('id = ' . (int)$media->id);
                $db->setQuery($query);

                if (!$db->execute()) {
                    return Text::_('JBS_ADM_ERROR_OCCURED');
                }
            }
        }

        return $msg . ' ' . $account;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  bool  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @throws Exception
     * @since   1.6
     */
    protected function canDelete($record): bool
    {
        if (empty($record->id) || $record->published !== -2) {
            return false;
        }

        return Factory::getApplication()->getIdentity()->authorise('core.delete');
    }

    /**
     * Method to test whether a record can have its state edited.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @throws Exception
     * @since   1.6
     */
    protected function canEditState($record): bool
    {
        // Check against the category.
        if (!empty($record->catid)) {
            return Factory::getApplication()->getIdentity()->authorise('core.edit.state');
        }

        // Default to component settings if category not known.
        return parent::canEditState($record);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string|null  $ordering   ?
     * @param   string|null  $direction  ?
     *
     * @return  void
     *
     * @throws Exception
     * @since    1.7.2
     */
    protected function populateState(string $ordering = null, string $direction = null): void
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
     * @param   CwmadminTable  $table  A Table object.
     *
     * @return   void
     *
     * @since    1.6
     */
    protected function prepareTable($table): void
    {
        // Reorder the articles within the category so the new article is first
        if (empty($table->id)) {
            $table->id = 1;
        }
    }

    /**
     * Load Form Date
     *
     * @return object
     *
     * @throws Exception
     * @since 7.0
     */
    protected function loadFormData(): object
    {
        $data = Factory::getApplication()->getUserState('com_proclaim.edit.cwmadmin.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Custom clean the cache of com_proclaim and proclaim modules
     *
     * @param   string  $group      The cache group
     *
     * @return  void
     *
     * @since    1.6
     */
    protected function cleanCache($group = null): void
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }
}
