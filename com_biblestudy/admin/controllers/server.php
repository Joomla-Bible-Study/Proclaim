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

jimport('joomla.application.component.controllerform');

/**
 * Controller for Server
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerServer extends JControllerForm
{

	/**
	 * Class constructor.
	 *
	 * @param   array $config  A named array of configuration variables.
	 *
	 * @since    7.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

    public function add() {
        $app = JFactory::getApplication();

        if(parent::add()) {
            $app->setUserState('com_biblestudy.edit.server.type', null);
            return true;
        }

        return false;
    }
    /**
     * Resets the User state for the server type. Needed to allow the value from the DB to be used
     *
     * @param   null $key
     * @param   null $urlVar
     * @return  bool
     *
     * @since   8.1.0
     */
    public function edit($key = null, $urlVar = null) {
        $app = JFactory::getApplication();
        $result = parent::edit();

        if($result) {
            $app->setUserState('com_biblestudy.edit.server.type', null);
        }

        return true;
    }

    /**
     * Sets the type of endpoint currently being configured.
     *
     * @return  void
     * @since   8.1.0
     */
    function setType() {
        $app = JFactory::getApplication();
        $input = $app->input;

        $data = $input->get('jform', array(), 'post', 'array');
        $type = json_decode(base64_decode($data['type']));

        $recordId = isset($type->id) ? $type->id : 0;

        // Save the endpoint in the session
        $app->setUserState('com_biblestudy.edit.server.type', $type->name);

        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId), false));
    }
}
