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

use CWM\Component\Proclaim\Administrator\Table\CwmserverTable;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

/**
 * Server administrator model
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmserverModel extends AdminModel
{
    /**
     * The type alias for this content type (for example, 'com_content.article').
     *
     * @var    string
     * @since  3.2
     */
    public $typeAlias = 'com_proclaim.server';
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  1.6
     */
    protected $text_prefix = 'COM_PROCLAIM';

    /**
     * Allowed batch commands
     *
     * @var array
     * @since 10.0.0
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
    ];
    /**
     * Data
     *
     * @var object
     * @since   9.0.0
     */
    private object $data;

    /**
     * @var bool
     * @since 9.0.0
     * @todo  need to look into this and see if we need it still.
     */
    private $event_after_upload;

    /**
     * Constructor
     *
     * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
     *
     * @throws  \Exception
     * @since   12.2
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if (isset($config['event_after_upload'])) {
            $this->event_after_upload = $config['event_after_upload'];
        }
    }

    /**
     * Reverse look up of id to server_type
     *
     * @param   int   $pk   ID to get
     * @param   bool  $ext  If coming from external
     *
     * @return string
     *
     * @since 9.0.0
     */
    public function getType(int $pk, bool $ext = false): string
    {
        return $this->getItem($pk, $ext)->type;
    }

    /**
     * Method to get a server item.
     *
     * @param   int   $pk   An optional id of the object to get
     * @param   bool  $ext  If coming from external
     *
     * @return false|object|\stdClass Server data object, false on failure
     *
     * @since 9.0.0
     */
    public function getItem($pk = null, bool $ext = false): mixed
    {
        if (!empty($this->data)) {
            return $this->data;
        }

        $this->data = parent::getItem($pk);

        if ($this->data) {
            // Convert media field to array
            $registry          = new Registry($this->data->media);
            $this->data->media = $registry->toArray();

            // Set the type from session if available or fall back on the db value
            $server_name             = $this->getState('cwmserver.server_name');
            $this->data->server_name = empty($server_name) ? $this->data->server_name : $server_name;

            $type = null;

            if (!$ext) {
                $type = $this->getState('cwmserver.type');
            }

            $this->data->type = empty($type) ? $this->data->type : $type;

            // Load server type configuration if available
            if ($this->data->type) {
                $this->data->addon = $this->getConfig($this->data->type);
            }
        }

        return $this->data;
    }

    /**
     * Return the configuration xml of a server
     *
     * @param   string  $addon  Type of server
     *
     * @return \SimpleXMLElement|bool  SimpleXMLElement on success, false on failure
     *
     * @since   9.0.0
     */
    public function getConfig(string $addon): \SimpleXMLElement|bool
    {
        $path = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/' . ucfirst(
            $addon
        ) . '/' . strtolower($addon) . '.xml';

        if (!is_file($path)) {
            return false;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return false;
        }

        return simplexml_load_string($contents);
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  bool  True on success.
     *
     * @throws \Exception
     * @since   1.6
     */
    public function save($data): bool
    {
        $db         = Factory::getContainer()->get('DatabaseDriver');
        $text       = '';

        // Sanitize server_name to prevent XSS attacks
        if (isset($data['server_name'])) {
            $filter    = InputFilter::getInstance();
            $cleanName = $filter->clean($data['server_name'], 'STRING');

            // Check if the name was altered (indicating potentially malicious content)
            if ($cleanName !== $data['server_name']) {
                // Use the sanitized version
                $data['server_name'] = $cleanName;
            }

            // Additional check for HTML/script injection attempts
            if (preg_match('/<[^>]*>|javascript:|on\w+\s*=/i', $data['server_name'])) {
                throw new \RuntimeException(Text::_('JBS_SVR_ILLEGAL_CHARACTERS'));
            }
        }

        if (isset($data['params']['path'])) {
            if (str_contains($data['params']['path'], '//')) {
                $data['params']['path'] = substr($data['params']['path'], strpos($data['params']['path'], '//'));
            } elseif (!str_contains($data['params']['path'], '//')) {
                $data['params']['path'] = '//' . $data['params']['path'];
            }
        }

        if (!empty($data) && !empty($data['id'])) {
            $query = $db->getQuery(true);
            $query->select($db->qn(['id', 'params']))
                ->from($db->qn('#__bsms_mediafiles'))
                ->where($db->qn('server_id') . ' = :serverId')
                ->bind(':serverId', $data['id'], \Joomla\Database\ParameterType::INTEGER);
            $db->setQuery($query);
            $studies = $db->loadObjectList();

            if ($data['published'] == '-2' || $data['published'] == '0') {
                foreach ($studies as $studie) {
                    $registry = new Registry();
                    $registry->loadString($studie->params);
                    $studie->params = $registry;
                    $text .= ' ' . $studie->id . '-"' . $studie->params->get('filename') . '",';
                }

                Factory::getApplication()->enqueueMessage(Text::sprintf('JBS_SVR_CAN_NOT_DELETE', $text));

                return false;
            }
        }

        if (parent::save($data)) {
            return true;
        }

        return false;
    }

    /**
     * Get the server form
     *
     * @return \Joomla\CMS\Form\Form|null  Form object on success, null if no server type selected
     *
     * @throws \Exception
     *
     * @since   9.0.0
     */
    public function getAddonServerForm(): ?Form
    {
        // If user hasn't selected a server type yet, return null
        $type = $this->data->type ?? null;

        if (empty($type)) {
            return null;
        }

        $path = Path::clean(JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/' . ucfirst($type));

        Form::addFormPath($path);
        Form::addFieldPath($path . '/fields');

        // Add language files
        $lang = Factory::getApplication()->getLanguage();
        $lang->load('jbs_addon_' . strtolower($type), $path);

        return $this->loadForm(
            'com_proclaim.server.' . $type,
            $type,
            ['control' => 'jform', 'load_data' => true],
            true,
            "/server"
        );
    }

    /**
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   bool  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getForm($data = [], $loadData = true): mixed
    {
        if (empty($data)) {
            $this->getItem();
        }

        // Get the forms.
        $form = $this->loadForm(
            'com_proclaim.cwmserver',
            'server',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        return $form ?? false;
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
    public function getTable($name = 'Cwmserver', $prefix = '', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  bool  True if allowed to delete the record. Defaults to the permission for the component.
     *
     * @throws \Exception
     * @since    1.6
     */
    protected function canDelete($record): bool
    {
        return Factory::getApplication()->getIdentity()->authorise(
            'core.delete',
            'com_proclaim.cwmserver.' . (int)$record->id
        );
    }

    /**
     * Method to test whether a state can be edited.
     *
     * @param   object  $record  A record object.
     *
     * @return   bool  True if allowed to change the state of the record. Defaults to the permission set in the
     *                    component.
     *
     * @throws \Exception
     * @since    1.6
     */
    protected function canEditState($record): bool
    {
        $user = Factory::getApplication()->getIdentity();

        // Check for existing server record
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', 'com_proclaim.cwmserver.' . (int)$record->id);
        }

        return parent::canEditState($record);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array|object    The default data is an empty array.
     *
     * @throws \Exception
     * @since   9.0.0
     */
    protected function loadFormData(): array|object
    {
        // If the current state has data, use it instead of data from db
        $session = Factory::getApplication()->getUserState('com_proclaim.edit.cwmserver.data', []);

        return empty($session) ? $this->data : $session;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   CwmserverTable  $table  A reference to a Table object.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.0.0
     */
    protected function prepareTable($table): void
    {
        $date = new Date();
        $user = Factory::getApplication()->getIdentity();

        // Always ensure created date is set (handles empty string from form)
        if (empty($table->created) || $table->created === '') {
            $table->created = $date->toSql();
        }

        if (empty($table->id)) {
            // Set the values for a new record
            if (empty($table->created_by)) {
                $table->created_by = $user->id;
            }
        } else {
            // Set the values for existing records
            $table->modified    = $date->toSql();
            $table->modified_by = $user->id;
        }
    }

    /**
     * Custom clean the cache of com_proclaim and proclaim modules
     *
     * @param   string  $group      The cache group
     * @param   int     $client_id  The ID of the client
     *
     * @return  void
     *
     * @since    1.6
     */
    protected function cleanCache($group = null, int $client_id = 0): void
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }

    /**
     * Auto-populate the model state.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   9.0.0
     */
    protected function populateState(): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $pk = $input->get('id', null, 'INTEGER');
        $this->setState('cwmserver.id', $pk);

        $sname = $app->getUserState('com_proclaim.edit.cwmserver.server_name');
        $this->setState('cwmserver.server_name', $sname);

        $type = $app->getUserState('com_proclaim.edit.cwmserver.type');
        $this->setState('cwmserver.type', $type);
    }
}
