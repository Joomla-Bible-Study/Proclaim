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

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;
use SimpleXMLElement;
use stdClass;

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
     * @throws  Exception
     * @since   12.2
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        if (isset($config['event_after_upload'])) {
            $this->event_after_upload = isset($config['event_after_upload']);
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
     * @return false|object|stdClass Server data object, false on failure
     *
     * @since 9.0.0
     */
    public function getItem($pk = null, bool $ext = false)
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
     * @return SimpleXMLElement
     *
     * @since   9.0.0
     */
    public function getConfig(string $addon): SimpleXMLElement|bool
    {
        $path = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Addons/Servers/' . ucfirst(
            $addon
        ) . '/' . strtolower($addon) . '.xml';

        return simplexml_load_string(file_get_contents($path));
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function save($data): bool
    {
        $db         = Factory::getContainer()->get('DatabaseDriver');
        $text       = '';

        if (strpos($data['server_name'], '"onmouseover="prompt(1)"') !== false) {
            $this->setError('"Illegal character use in Server Name field"');

            return false;
        }

        if (isset($data['params']['path'])) {
            if (strpos($data['params']['path'], '//')) {
                $data['params']['path'] = substr($data['params']['path'], strpos($data['params']['path'], '//'));
            } elseif (strpos($data['params']['path'], '//') === false) {
                $data['params']['path'] = '//' . $data['params']['path'];
            }
        }

        if (!empty($data)) {
            $query = $db->getQuery(true);
            $query->select('id, params')
                ->from('#__bsms_mediafiles')
                ->where('server_id = ' . $data['id']);
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
     * @return \Joomla\CMS\Form\Form|string
     *
     * @throws Exception
     *
     * @since   9.0.0
     */
    public function getAddonServerForm()
    {
        // If user hasn't selected a server type yet, just return an empty form
        $type = $this->data->type;

        if (empty($type)) {
            // @TODO This may not be optimal, seems like a hack
            return "no-data-type";
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
            array('control' => 'jform', 'load_data' => true),
            true,
            "/server"
        );
    }

    /**
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @throws Exception
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true)
    {
        if (empty($data)) {
            $this->getItem();
        }

        // Get the forms.
        $form = $this->loadForm(
            'com_proclaim.cwmserver',
            'server',
            array('control' => 'jform', 'load_data' => $loadData)
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
     * @throws  Exception
     * @since   3.0
     */
    public function getTable($name = 'Cwmserver', $prefix = '', $options = array()): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
     *
     * @throws Exception
     * @since    1.6
     */
    protected function canDelete($record): bool
    {
        return Factory::getApplication()->getSession()->get('user')->authorise(
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
     * @throws Exception
     * @since    1.6
     */
    protected function canEditState($record)
    {
        $tmp        = (array)$record;
        $db         = Factory::getContainer()->get('DatabaseDriver');
        $user       = Factory::getApplication()->getSession()->get('user');
        $canDoState = $user->authorise('core.edit.state', $this->option);

        // Check for existing article.
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
     * @throws Exception
     * @since   9.0.0
     */
    protected function loadFormData(): array|object
    {
        // If the current state has data, use it instead of data from db
        $session = Factory::getApplication()->getUserState('com_proclaim.edit.cwmserver.data', array());

        return empty($session) ? $this->data : $session;
    }

    /**
     * Custom clean the cache of com_proclaim and proclaim modules
     *
     * @param   string   $group      The cache group
     * @param   integer  $client_id  The ID of the client
     *
     * @return  void
     *
     * @since    1.6
     */
    protected function cleanCache($group = null, int $client_id = 0)
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }

    /**
     * Auto-populate the model state.
     *
     * @return  void
     *
     * @throws Exception
     * @since   9.0.0
     */
    protected function populateState()
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        $pk = $input->get('id', null, 'INTEGER');
        $this->setState('cwmserver.id', $pk);

        $sname = $app->getUserState('com_proclaim.edit.cwmserver.server_name');
        $this->setState('cwmserver.server_name', $sname);

        $type = $app->getUserState('com_proclaim.edit.cwmserver.type');
        $this->setState('cwmserver.type', $type);
    }
}
