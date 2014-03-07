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

jimport('joomla.application.component.modeladmin');

/**
 * Server admin model
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelServer extends JModelAdmin
{
    /**
     * Data
     *
     * @var
     * @since   8.1.0
     */
    private $data;

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since    1.6
	 */
	protected function canDelete($record)
	{
		$user = JFactory::getUser();

		return $user->authorise('core.delete', 'com_biblestudy.article.' . (int) $record->id);
	}

	/**
	 * Method to test whether a state can be edited.
	 *
	 * @param   object $record  A record object.
	 *
	 * @return   boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since    1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check for existing article.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_biblestudy.server.' . (int) $record->id);
		}

		// Default to component settings if neither article nor category known.
		else
		{
			return parent::canEditState($record);
		}
	}

	/**
	 * Method to store a record
	 *
	 * @access    public
	 * @return    boolean    True on success
	 */
	public function store()
	{
		$row   = & $this->getTable();
		$input = new JInput;
		$data  = $input->get('post');

		// Remove starting and trailing spaces
		$data['server_path'] = trim($data['server_path']);

		// Bind the form fields to the server table
		if (!$row->bind($data))
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Make sure the record is valid
		if (!$row->check())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Store the web link table to the database
		if (!$row->store())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		return true;
	}

    public function getType($pk) {
        $item = $this->getItem($pk);
        return $item->server_type;
    }

    /**
     * Get the server form
     *
     * @return bool|mixed
     * @throws Exception
     *
     * @since   8.1.0
     */
    public function getServerForm() {
        $path = JPath::clean(JPATH_ADMINISTRATOR . '/components/com_biblestudy/addons/servers/'.$this->getState('server.type'));

        JForm::addFormPath($path);
        JForm::addFieldPath($path.'/fields');

        // Add language files
        $lang = JFactory::getLanguage();
        if(!$lang->load('jbs_addon_legacy', JPATH_ADMINISTRATOR.'/components/com_biblestudy/addons/servers/'.$this->getState('server.type')))
            throw new Exception(JText::_('JBS_ERR_ADDON_LANGUAGE_NOT_LOADED'));

        $form = $this->loadForm('com_biblestudy.server.'.$this->getState('server.type'), $this->getState('server.type'), array('control' => 'jform', 'load_data' => true), true, "/server");

        if (empty($form)) {
            return false;
        }

        return $form;

    }
	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array   $data      Data for the form.
	 * @param   boolean $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		if (empty($data)) {
            $item = $this->getItem();
		}else {
            $this->setState('server.type', JArrayHelper::getValue($data, 'server_type'));
        }


        // Get the forms.
        $form = $this->loadForm('com_biblestudy.server', 'server', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   7.0
     * @TODO    This gets called twice, because we're loading two forms. (There is redundcancy
     *          in the bind() because the data is itereted over 2 times, 1 for each form). Possibly,
     *          figure out a way to iterate over only the relevant data)
	 */
	protected function loadFormData()
	{
        // If current state has data use it instead of data from db
		$session = JFactory::getApplication()->getUserState('com_biblestudy.edit.server.data', array());

        $data = empty($session) ? $this->data : $session;

		return $data;
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer $pk  The numeric id of the primary key.
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
	 * Custom clean the cache of com_biblestudy and biblestudy modules
	 *
	 * @param   string  $group      The cache group
	 * @param   integer $client_id  The ID of the client
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

    /**
     * Method to get a server item.
     *
     * @param null $pk  An optional id of the object to get
     *
     * @return mixed Server Server data object, false on failure
     *
     * @since 8.1.0
     */
    public function getItem($pk = null) {
        if(!empty($this->data))
            return $this->data;

        $this->data = parent::getItem($pk);

        if(!empty($this->data)) {
            // Convert media field to array
            $registry = new JRegistry($this->data->media);
            $this->data->media = $registry->toArray();

            // Save server type in state if needed
            if(is_null($this->getState('server.type')))
                $this->setState('server.type', $this->data->server_type);
        }

        return $this->data;
    }

    /**
     * Auto-populate the model state.
     *
     * @return  void
     *
     * @since   8.1.0
     */
    protected function populateState() {
        $app = JFactory::getApplication('administrator');
        $input = $app->input;

        $pk = $input->get('id', null, 'INTEGER');
        $this->setState('server.id', $pk);

        $server = $app->getUserState('com_biblestudy.edit.server.type');
        $this->setState('server.type', $server);
    }

    /**
     * @param   JForm   $form
     * @param   mixed   $data
     * @param   string  $group
     * @throws  Exception   If there is an error in loading server config files
     *
     * @since   8.1.0
     */
    protected function preprocessForm(JForm $form, $data, $group = 'content') {
        parent::preprocessForm($form, $data, $group);
    }
}
