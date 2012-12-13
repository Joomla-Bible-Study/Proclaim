<?php

/**
 * Controller for a Comment
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Controller for a Comment
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerComment extends JControllerForm
{

	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since    7.0.0
	 */
	public function __construct($config = array())
	{
		$input = new JInput;
        $input->set('a_id', $input->get('a_id',0,'int'));
        parent::__construct($config);
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowAdd($data = array())
	{
		// In the absense of better information, revert to the component permissions.
		return parent::allowAdd();
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = array(), $key = 'a_id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = JFactory::getUser();
		$userId = $user->get('id');

		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_biblestudy.comment.' . $recordId))
		{
			return true;
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Comment', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_biblestudy&view=comments' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}
/**
     * Method to cancel an edit.
     *
     * @param	string	$key	The name of the primary key of the URL variable.
     *
     * @return	Boolean	True if access level checks pass, false otherwise.
     * @since	1.6
     */
    public function cancel($key = 'a_id') {
        parent::cancel($key);
    }

    /**
     * Method to edit an existing record.
     *
     * @param	string	$key	The name of the primary key of the URL variable.
     * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return	Boolean	True if access level check and checkout passes, false otherwise.
     * @since	1.6
     */
    public function edit($key = null, $urlVar = 'a_id') {
        $result = parent::edit($key, $urlVar);
        return $result;
    }

    /**
     * Method to save a record.
     *
     * @param	string	$key	The name of the primary key of the URL variable.
     * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return	Boolean	True if successful, false otherwise.
     * @since	1.6
     */
    public function save($key = null, $urlVar = 'a_id') {

        $result = parent::save($key, $urlVar);
        return $result;
    }
    /**
     * Method to get a model object, loading it if required.
     *
     * @param	string	$name	The model name. Optional.
     * @param	string	$prefix	The class prefix. Optional.
     * @param	array	$config	Configuration array for model. Optional.
     *
     * @return	object	The model.
     *
     * @since	1.5
     */
    public function getModel($name = 'Comment', $prefix = 'biblestudyModel', $config = array('ignore_request' => true)) {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}
