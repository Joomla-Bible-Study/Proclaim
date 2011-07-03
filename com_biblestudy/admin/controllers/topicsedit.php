<?php

/**
 * @version     $Id: topicsedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.controllerform');

    abstract class controllerClass extends JControllerForm {

    }

class biblestudyControllertopicsedit extends controllerClass
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	protected $view_list = 'topicslist';
	 
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks

	}

	/**
	 * Method to save a topic item.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// get the model and check alias
		$app = JFactory::getApplication();
		$model = $this->getModel();
		$table = $model->getTable();
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		// Determine the name of the primary key for the data.
		if (empty($key)) {
			$key = $table->getKeyName();
		}
		// The urlVar may be different from the primary key to avoid data collisions.
		if (empty($urlVar)) {
			$urlVar = $key;
		}
		$recordId = JRequest::getInt($urlVar);
		$data = $table->checkAlias($data, $recordId);
		// push back to JRequest
		JRequest::setVar('jform', $data, 'post', true);

		parent::save($key, $urlVar);
	}

}
?>