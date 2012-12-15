<?php

/**
 * Message Model
 *
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 *          class biblestudyModelmessage extends JModelAdmin
 */
//No Direct Access
defined('_JEXEC') or die;
if(BIBLESTUDY_CHECKREL)
jimport('joomla.application.component.modeladmin');

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/message.php';

/**
 * Model class for Message
 *
 * @package BibleStudy.Site
 * @since   7.0.0
 */
class BiblestudyModelMessageform extends BiblestudyModelMessage
{

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('a_id');
		$this->setState('sermon.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->get('layout'));
	}

	/**
	 * Method to get article data.
	 *
	 * @param    integer    The id of the article.
	 *
	 * @return    mixed    Content item data object on success, false on failure.
	 */
	public function getItem($itemId = null)
	{
		// Initialise variables.
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('sermon.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());

			return false;
		}

		$properties = $table->getProperties(1);
		$value      = JArrayHelper::toObject($properties, 'JObject');

		return $value;
	}


	/**
	 * Get the return URL.
	 *
	 * @return    string    The return URL.
	 * @since    1.6
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

}