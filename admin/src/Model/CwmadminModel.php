<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmDebug;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeFileCache;
use CWM\Component\Proclaim\Administrator\Table\CwmadminTable;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use CWM\Library\Scripture\Helper\ScriptureParamsHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Schema\ChangeSet;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Extension as ExtensionTable;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\Database\DatabaseInterface;
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
    protected string $formName = 'cwmcpanel';

    /**
     * @var null
     * @since 7.0
     */
    protected mixed $changeSet = null;

    /**
     * Gets the form from the XML file.
     *
     * @param   array  $data      Data for the form.
     * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getForm($data = [], $loadData = true): mixed
    {
        // No eager getItem() — loadFormData() handles it and AdminModel caches the result.
        // Get the form.
        $form = $this->loadForm('com_proclaim.admin', 'admin', ['control' => 'jform', 'load_data' => $loadData]);

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
     * @throws  \Exception
     * @since   3.0
     */
    public function getTable($name = 'Cwmadmin', $prefix = '', $options = []): Table
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
    /**
     * Scripture param keys that belong to the plugin, not the component.
     * These are stripped from component params on save and written to plugin params.
     *
     * Note: gdpr_mode is NOT in this list — it's shared. Proclaim keeps its own
     * copy for analytics/privacy, and also syncs it to the plugin for scripture.
     *
     * @var  string[]
     * @since  10.3.0
     */
    private const SCRIPTURE_KEYS = [
        'provider_getbible',
        'provider_api_bible',
        'api_bible_api_key',
        'scripture_cache_days',
        'default_bible_version',
    ];

    /**
     * Keys that are synced to the plugin but also kept in component params.
     *
     * @var  string[]
     * @since  10.3.0
     */
    private const SHARED_KEYS = [
        'gdpr_mode',
    ];

    public function save($data): bool
    {
        $params = new Registry();
        $params->loadArray($data['params']);

        // Intercept scripture settings and redirect them to the plugin params
        $this->saveScriptureParams($params);

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
     * Extract scripture-related params and save them to the plugin's row.
     *
     * Also strips them from the component Registry so they don't get
     * persisted in `#__bsms_admin.params`.
     *
     * @param   Registry  $params  Component params (modified in place)
     *
     * @return  void
     *
     * @since  10.3.0
     */
    private function saveScriptureParams(Registry $params): void
    {
        try {
            $pluginParams = ScriptureParamsHelper::getParams();
            $changed      = false;

            // Scripture-only keys: move to plugin, remove from component
            foreach (self::SCRIPTURE_KEYS as $key) {
                $value = $params->get($key);

                if ($value === null) {
                    continue;
                }

                $pluginKey = match ($key) {
                    'scripture_cache_days'  => 'cache_days',
                    'default_bible_version' => 'default_version',
                    default                 => $key,
                };

                $pluginParams->set($pluginKey, $value);
                $params->remove($key);
                $changed = true;
            }

            // Shared keys: sync to plugin but keep in component params
            foreach (self::SHARED_KEYS as $key) {
                $value = $params->get($key);

                if ($value === null) {
                    continue;
                }

                $pluginParams->set($key, $value);
                // Do NOT remove — Proclaim needs its own copy
                $changed = true;
            }

            if ($changed) {
                ScriptureParamsHelper::save($pluginParams);
            }
        } catch (\Exception $e) {
            // Plugin may not be installed — silently skip
        }
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
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__bsms_mediafiles'));
        $db->setQuery($query);
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
     * @throws \Exception
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
        /** @var CwminstallModel $installer */
        $installer = Factory::getApplication()->bootComponent('com_proclaim')
            ->getMVCFactory()->createModel('Cwminstall', 'Administrator');
        $installer->fixMenus();
        $installer->fixemptyaccess();
        $installer->fixemptylanguage();
        $this->fixDefaultTextFilters();

        return true;
    }

    /**
     * Gets the ChangeSet object
     *
     * @return ChangeSet|bool|null JSchema  ChangeSet
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getItems(): ChangeSet|bool|null
    {
        $folder = JPATH_ADMINISTRATOR . '/components/com_proclaim/sql/updates/';

        if ($this->changeSet !== null) {
            return $this->changeSet;
        }

        try {
            $this->changeSet = ChangeSet::getInstance(Factory::getContainer()->get(DatabaseInterface::class), $folder);
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
     * @throws \Exception
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
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__schemas'))
            ->where($db->quoteName('extension_id') . ' = ' . $db->q($extensionresult));
        $db->setQuery($query);
        $db->execute();

        // Add new row
        $query->clear()
            ->insert($db->quoteName('#__schemas'))
            ->columns($db->quoteName('extension_id') . ',' . $db->quoteName('version_id'))
            ->values($db->quote($extensionresult) . ', ' . $db->quote($schema));
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\Exception $e) {
            return false;
        }

        return $schema;
    }

    /**
     * To retrieve component extension_id
     *
     * @return string extension_id
     *
     * @throws \Exception
     * @since 7.1.0
     */
    public function getExtentionId(): string
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select($db->quoteName('extension_id'))->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->q('com_proclaim'));
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
     * @throws \Exception
     * @since 7.0
     */
    public function getSchemaVersion(): mixed
    {
        $db              = Factory::getContainer()->get(DatabaseInterface::class);
        $query           = $db->getQuery(true);
        $extensionresult = $this->getExtentionId();
        $query->select($db->quoteName('version_id'))->from($db->quoteName('#__schemas'))
            ->where($db->quoteName('extension_id') . ' = ' . $db->q($extensionresult));
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Fix a Joomla version in #__extensions table if wrong (doesn't equal JVersion short version)
     *
     * @return   mixed  string update version if success, false if fail
     *
     * @throws \Exception
     * @since 7.0
     */
    public function fixUpdateVersion(): mixed
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $table = new ExtensionTable($db);
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
        $version  = '';
        $file     = JPATH_ADMINISTRATOR . '/components/com_proclaim/proclaim.xml';
        $xml      = simplexml_load_string(file_get_contents($file));

        if ($xml) {
            $version = (string)$xml->version;
        }

        return $version;
    }

    /**
     * Check if com_proclaim parameters are blank. If so, populate with com_content text filters.
     *
     * @return  mixed  bool true if params are updated, null otherwise
     *
     * @since 7.0
     */
    public function fixDefaultTextFilters(): mixed
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $table = new ExtensionTable($db);
        $table->load($table->find(['name' => 'com_proclaim']));

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
     * @throws \Exception
     * @since 7.0
     */
    public function getUpdateVersion(): mixed
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $table = new ExtensionTable($db);
        $table->load($this->getExtentionId());

        return (new Registry($table->manifest_cache))->get('version');
    }

    /**
     * Check if com_proclaim parameters are blank.
     *
     * @return  Registry  default text filters (if any)
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getDefaultTextFilters(): Registry
    {
        return Cwmparams::getAdmin()->params;
    }

    /**
     * Check for SermonSpeaker and PreachIt
     *
     * @return mixed The return value or null if the query failed.
     *
     * @since 7.0
     */
    public function getSSorPI(): mixed
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName(['extension_id', 'name', 'element']))
            ->from($db->quoteName('#__extensions'))
            ->whereIn($db->quoteName('element'), ['com_sermonspeaker', 'com_preachit']);
        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Change Player based off MimeType or Extension of File Name
     *
     * @return string
     *
     * @throws \Exception
     * @since 9.0.12
     */
    public function playerByMediaType(): string
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            throw new \Exception(Text::_('JINVALID_TOKEN'));
        }

        $db   = Factory::getContainer()->get(DatabaseInterface::class);
        $msg  = Text::_('JBS_CMN_OPERATION_SUCCESSFUL');
        $post = Factory::getApplication()->getInput()->post->get('jform', [], 'array');
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
        $query->select($db->quoteName(['id', 'params']))
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('published') . ' = ' . $db->q('1'));
        $db->setQuery($query);

        foreach ($db->loadObjectList() as $media) {
            $count++;
            $search = false;
            $from   = '';
            $reg    = new Registry();
            $reg->loadString($media->params);
            $filename  = $reg->get('filename', '');
            $mediaCode = $reg->get('mediacode');

            $extension = substr($filename, strrpos($filename, '.') + 1);

            if ($from === 'http' && str_contains($filename, 'http')) {
                $reg->set('mime_type', ' ');
                $from   = 'http';
                $search = true;
            }

            if (!empty($mediaCode) && $from === 'mediacode') {
                $reg->set('mime_type', ' ');
                $from   = 'mediacode';
                $search = true;
            }

            if (str_contains($key, $extension) || $reg->get('mime_type', 0) == $from) {
                $reg->set('mime_type', $from);
                $from   = 'Extenstion';
                $search = true;
            }

            if ($search && !empty($from)) {
                $account++;

                CwmDebug::log(
                    'MIME from=' . $from . ' mime=' . $reg->get('mime_type', '') . ' file=' . $filename,
                    'mime'
                );

                $reg->set('player', $to);

                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__bsms_mediafiles'))
                    ->set($db->quoteName('params') . ' = ' . $db->q($reg->toString()))
                    ->where($db->quoteName('id') . ' = ' . (int)$media->id);
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
     * @throws \Exception
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
     * @return  bool  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @throws \Exception
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
     * @param   ?string  $ordering   ?
     * @param   ?string  $direction  ?
     *
     * @return  void
     *
     * @throws \Exception
     * @since    1.7.2
     */
    protected function populateState(?string $ordering = null, ?string $direction = null): void
    {
        $app = Factory::getApplication();
        $this->setState('message', $app->getUserState('com_proclaim.message'));
        $this->setState('extension_message', $app->getUserState('com_proclaim.extension_message'));
        $app->setUserState('com_proclaim.message', '');
        $app->setUserState('com_proclaim.extension_message', '');
        parent::populateState();

        // Singleton settings page — always use id=1.
        // Use $this->state directly to avoid getState() re-triggering populateState().
        if (!$this->state->get($this->getName() . '.id')) {
            $this->setState($this->getName() . '.id', 1);
        }
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
     * @throws \Exception
     * @since 7.0
     */
    protected function loadFormData(): object
    {
        $data = Factory::getApplication()->getUserState('com_proclaim.edit.cwmadmin.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        // Inject scripture plugin params into the form so the Scripture tab
        // fields display the current plugin values (not stale component params).
        try {
            $pluginParams = ScriptureParamsHelper::getParams();
            // gdpr_mode is NOT injected — Proclaim keeps its own copy in component params
            $keyMap       = [
                'provider_getbible'  => 'provider_getbible',
                'provider_api_bible' => 'provider_api_bible',
                'api_bible_api_key'  => 'api_bible_api_key',
                'cache_days'         => 'scripture_cache_days',
                'default_version'    => 'default_bible_version',
            ];

            if (\is_object($data) && isset($data->params)) {
                $params = $data->params instanceof Registry
                    ? $data->params
                    : new Registry($data->params);

                foreach ($keyMap as $pluginKey => $formKey) {
                    $params->set($formKey, $pluginParams->get($pluginKey));
                }

                $data->params = $params;
            }
        } catch (\Exception $e) {
            // Plugin not installed — fields will show defaults
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
        parent::cleanCache('mod_proclaim_youtube');
        parent::cleanCache('mod_proclaim_podcast');

        // Also clear the YouTube module's file-based cache
        if (class_exists(CwmyoutubeFileCache::class)) {
            CwmyoutubeFileCache::clearVideoCache();
        }
    }
}
