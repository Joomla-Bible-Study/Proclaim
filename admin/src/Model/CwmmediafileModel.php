<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Table\CwmmediafileTable;
use CWM\Component\Proclaim\Administrator\Table\CwmserverTable;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

/**
 * MediaFile model class
 *
 * @property mixed _id
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmediafileModel extends AdminModel
{
    /**
     * The type alias for this content type (for example, 'com_content.article').
     *
     * @var    string
     * @since  3.2
     */
    public $typeAlias = 'com_proclaim.mediafile';
    /**
     * @var    string  The prefix to use with controller messages.
     * @since  1.6
     */
    protected $text_prefix = 'com_proclaim';
    /**
     * Data
     *
     * @var object
     * @since   9.0.0
     */
    private object $data;


    /**
     * Allowed batch commands
     *
     * @var array
     * @since 10.0.0
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'player'        => 'batchPlayer',
        'linkType'      => 'batchLinkType',
        'mimetype'      => 'batchMimetype',
        'mediaType'     => 'batchMediatype',
        'popup'         => 'popup',
    ];

    /**
     * Method to move a mediafile listing
     *
     * @param   string  $direction  ?
     *
     * @access    public
     * @return    bool    True on success
     *
     * @throws \Exception
     * @since     1.5
     */
    public function move(string $direction): bool
    {
        $row = $this->getTable();

        if (!$row->load($this->_id)) {
            return false;
        }

        if (!$row->move($direction, ' study_id = ' . (int)$row->study_id . ' AND published >= 0 ')) {
            return false;
        }

        return true;
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
    public function getTable($name = 'Cwmmediafile', $prefix = '', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Overrides the JModelAdmin save routine in order to implode the podcast_id
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean True on successfully save
     *
     * @since   7.0
     */
    public function save($data): bool
    {
        if ($data) {
            // Implode only if they selected at least one podcast. Otherwise just clear the podcast_id field
            $data['podcast_id'] = empty($data['podcast_id']) ? '' : implode(",", $data['podcast_id']);

            $params = new Registry();
            $params->loadArray($data['params']);

            $jdb   = Factory::getContainer()->get('DatabaseDriver');
            $table = new CwmserverTable($jdb);
            $table->load($data['server_id']);

            $path = new Registry();
            $path->loadString($table->params);
            $set_path = '';

            if ($path->get('path')) {
                $set_path = $path->get('path') . '/';
            }

            if (isset($params->toObject()->size) && $params->get('size', '0') === '0') {
                if ($set_path && !$path->get('protocal')) {
                    $path->set('protocal', 'https://');
                } else {
                    $path->set('protocal', rtrim(Uri::root(), '/'));
                }

                if ($table->type === 'legacy' || $table->type === 'local') {
                    $size = Cwmhelper::getRemoteFileSize(
                        Cwmhelper::mediaBuildUrl($set_path, $params->get('filename'), $params, true, true)
                    );
                    $params->set('size', $size);
                }
            }

            if (
                !Cwmmedia::isExternal($params->get('filename'))
                && ($params->toObject()->media_hours === '00' || empty($params->toObject()->media_hours))
                && ($params->toObject()->media_minutes === '00' || empty($params->toObject()->media_minutes))
                && ($params->toObject()->media_seconds === '00' || (empty($params->toObject()->media_seconds)))
            ) {
                $path_server = Cwmhelper::mediaBuildUrl(
                    $set_path,
                    $params->get('filename'),
                    $params,
                    false,
                    false,
                    true
                );
                $jbspodcast  = new Cwmpodcast();

                // Make a duration build from Params of media.
                $prefix   = Uri::root();
                $nohttp   = $jbspodcast->removeHttp($prefix);
                $siteinfo = strpos($path_server, $nohttp);

                if ($siteinfo) {
                    $filename = substr($path_server, strlen($nohttp));
                    $filename = JPATH_SITE . '/' . $filename;
                } else {
                    $filename = $path_server;
                }

                $duration = $jbspodcast->formatTime($jbspodcast->getDuration($filename));

                $params->set('media_hours', $duration->hourse);
                $params->set('media_minutes', $duration->minutes);
                $params->set('media_seconds', $duration->seconds);
            }

            $data['params'] = $params->toArray();

            if (parent::save($data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the media form
     *
     * @return boolean|mixed
     *
     * @throws \Exception
     *
     * @since   9.0.0
     */
    public function getMediaForm()
    {
        // If a user hasn't selected a server yet, just return an empty form
        $server_id = $this->data->server_id;

        if ($server_id === null) {
            /** @var Registry $params */
            $params    = Cwmparams::getAdmin()->params;
            $server_id = $params->get('server');

            if ($server_id !== '-1') {
                $this->data->server_id = $server_id;
            } else {
                $server_id = null;
            }
        }

        // Reverse lookup server_id to server type
        $model       = new CwmserverModel();
        $s_item      = $model->getItem($server_id);
        $server_type = $s_item->type;

        $reg = new Registry();
        $reg->loadArray($s_item->params);

        $reg1 = new Registry();
        $reg1->loadArray($s_item->media);
        $reg1->merge($reg);

        if ($server_type) {
            $path = Path::clean(
                JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/' . ucfirst($server_type)
            );

            Form::addFormPath($path);
            Form::addFieldPath($path . '/Field');

            // Add language files
            $lang = Factory::getApplication()->getLanguage();
            $path = \Joomla\Filesystem\Path::clean(
                JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/' . ucfirst($server_type)
            );

            if (!$lang->load('jbs_addon_' . strtolower($server_type), $path)) {
                Factory::getApplication()->enqueueMessage(Text::_('JBS_CMN_ERROR_ADDON_LANGUAGE_NOT_LOADED'), 'error');
            }

            $form = $this->loadForm(
                'com_proclaim.mediafile.media',
                "media",
                ['control' => 'jform', 'load_data' => true],
                true,
                "/media"
            );
        } else {
            Factory::getApplication()->enqueueMessage(Text::_('JBS_CMN_ERROR_ADDON_LANGUAGE_NOT_LOADED'), 'warning');
            $form = $this->getForm();
        }

        if (empty($form)) {
            return false;
        }

        // Pass this data through state.
        $this->setState('s_params', $reg1->toArray());
        $this->setState('type', $server_type);

        return $form;
    }

    /**
     * Method to get media item
     *
     * @param   int  $pk  int
     *
     * @return  mixed
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    public function getItem($pk = null): mixed
    {
        $jinput = Factory::getApplication()->input;

        // The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
        if ($jinput->get('a_id')) {
            $pk = $jinput->get('a_id', 0);
        } else {
            // The back end uses id so we use that the rest of the time and set it to 0 by default.
            $pk = $jinput->get('id', 0);
        }

        if (!empty($this->data)) {
            return $this->data;
        }

        $this->data = parent::getItem($pk);

        if (!empty($this->data)) {
            // Make PodCast Id to be array for view
            if (!empty($this->data->podcast_id)) {
                $this->data->podcast_id = explode(',', $this->data->podcast_id);
            }

            // Convert metadata field to array if not null
            if ($this->data->metadata !== null) {
                $registry             = new Registry($this->data->metadata);
                $this->data->metadata = $registry->toArray();
            }

            // Set the server_id from session if available or fall back on the db value
            $server_id             = $this->getState('mediafile.server_id');
            $this->data->server_id = empty($server_id) ? $this->data->server_id : $server_id;

            $study_id             = $this->getState('mediafile.study_id');
            $this->data->study_id = empty($study_id) ? $this->data->study_id : $study_id;

            $createdate             = $this->getState('mediafile.createdate');
            $this->data->createdate = empty($createdate) ? $this->data->createdate : $createdate;
        }

        return $this->data;
    }

    /**
     * Get the form data
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return boolean|object
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getForm($data = [], $loadData = true)
    {
        if (empty($data)) {
            $this->getItem();
        }

        // Get the form.
        // @TODO Rename the form to "media" instead of mediafile
        $form = $this->loadForm(
            'com_proclaim.mediafile',
            'mediafile',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if ($form === null) {
            return false;
        }

        $jinput = Factory::getApplication()->input;

        // The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
        if ($jinput->get('a_id')) {
            $id = $jinput->get('a_id', 0);
        } else {
            // The back end uses id so we use that the rest of the time and set it to 0 by default.
            $id = $jinput->get('id', 0);
        }

        $user = Factory::getApplication()->getSession()->get('user');

        // Check for existing article.
        // Modify the form based on Edit State access controls.
        if (
            ($id !== 0 && (!$user->authorise('core.edit.state', 'com_proclaim.mediafile.' . (int)$id)))
            || ($id === 0 && !$user->authorise('core.edit.state', 'com_proclaim'))
        ) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Batch Player changes for a group of mediafiles.
     *
     * @param   string  $value     The new value matching a player.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @throws \Exception
     * @since   2.5
     */
    protected function batchPlayer($value, $pks, $contexts): bool
    {
        // Set the variables
        $user = Factory::getApplication()->getSession()->get('user');
        /** @type CwmmediafileTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);

                // Todo Need to move to params BCC
                $table->player = (int)$value;

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Custom clean the cache of com_proclaim and Proclaim modules
     *
     * @param   string   $group      The cache group
     * @param   integer  $client_id  The ID of the client
     *
     * @return  void
     *
     * @since 7.0.0
     */
    protected function cleanCache($group = null, $client_id = 0): void
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }

    /**
     * Batch popup changes for a group of media files.
     *
     * @param   string  $value     The new value matching a client.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @throws \Exception
     * @since   2.5
     */
    protected function batchLinkType($value, $pks, $contexts): bool
    {
        // Set the variables
        $user = Factory::getApplication()->getSession()->get('user');
        /** @type CwmmediafileTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $reg = new Registry();
                $reg->loadString($table->params);
                $reg->set('link_type', (int)$value);
                $table->params = $reg->toString();

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch popup changes for a group of media files.
     *
     * @param   string  $value     The new value matching a client.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @throws \Exception
     * @since   2.5
     */
    protected function batchMimetype($value, $pks, $contexts): bool
    {
        // Set the variables
        $user = Factory::getApplication()->getSession()->get('user');
        /** @type CwmmediafileTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $reg = new Registry();
                $reg->loadString($table->params);
                $reg->set('mime_type', (int)$value);
                $table->params = $reg->toString();

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch popup changes for a group of media files.
     *
     * @param   string  $value     The new value matching a client.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @throws \Exception
     * @since   2.5
     */
    protected function batchMediatype($value, $pks, $contexts): bool
    {
        // Set the variables
        $user  = Factory::getApplication()->getSession()->get('user');
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $reg = new Registry();
                $reg->loadString($table->params);
                $reg->set('media_image', (int)$value);
                $table->params = $reg->toString();

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch popup changes for a group of media files.
     *
     * @param   string  $value     The new value matching a client.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @throws \Exception
     * @since   2.5
     */
    protected function batchPopup($value, $pks, $contexts): bool
    {
        // Set the variables
        $user  = Factory::getApplication()->getSession()->get('user');
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $reg = new Registry();
                $reg->loadString($table->params);
                $reg->set('popup', (int)$value);
                $table->params = $reg->toString();

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method override to check-in a record or an array of record
     *
     * @param   mixed  $pks  The ID of the primary key or an array of IDs
     *
     * @return  mixed  Boolean false if there is an error, otherwise the count of records checked in.
     *
     * @throws \Exception
     * @since   12.2
     */
    public function checkin($pks = [])
    {
        $pks   = (array)$pks;
        $table = $this->getTable();
        $count = 0;

        if (empty($pks)) {
            $pks = [(int)$this->getState('mediafile.id')];
        }

        // Check in all items.
        foreach ($pks as $pk) {
            if ($table->load($pk)) {
                if ($table->checked_out > 0) {
                    if (!parent::checkin($pk)) {
                        return false;
                    }

                    $count++;
                }
            } else {
                $this->setError($table->getError());

                return false;
            }
        }

        return $count;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return    boolean    True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @throws \Exception
     * @since    1.6
     */
    protected function canDelete($record): bool
    {
        if (!empty($record->id)) {
            if ($record->published !== -2) {
                return false;
            }

            return Factory::getApplication()->getSession()->get('user')->authorise(
                'core.delete',
                'com_proclaim.mediafile.' . (int)$record->id
            );
        }

        return false;
    }

    /**
     * Load Form Data
     *
     * @return object
     *
     * @throws  \Exception
     * @since   7.0
     */
    protected function loadFormData(): object
    {
        $session = Factory::getApplication()->getUserState('com_proclaim.mediafile.edit.data', []);

        return empty($session) ? $this->data : $session;
    }

    /**
     * Auto-populate the model state
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    protected function populateState(): void
    {
        $app   = Factory::getApplication('administrator');
        $input = $app->input;

        // Load the Admin settings
        $admin    = Cwmparams::getAdmin();
        $registry = new Registry();
        $registry->loadString($admin->params);
        $this->setState('administrator', $registry);

        $pk = $input->get('id', null, 'INTEGER');
        $this->setState('mediafile.id', $pk);

        $server_id = $app->getUserState('com_proclaim.edit.mediafile.server_id');
        $this->setState('mediafile.server_id', $server_id);

        $study_id = $app->getUserState('com_proclaim.edit.mediafile.study_id');
        $this->setState('mediafile.study_id', $study_id);

        $createdate = $app->getUserState('com_proclaim.edit.mediafile.createdate');
        $this->setState('mediafile.createdate', $createdate);
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   object  $table  A record object.
     *
     * @return    array    An array of conditions to add to add to ordering queries.
     *
     * @since    1.6
     */
    protected function getReorderConditions($table): array
    {
        $condition   = [];
        $condition[] = 'study_id = ' . (int)$table->study_id;

        return $condition;
    }
}
