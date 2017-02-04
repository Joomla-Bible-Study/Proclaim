<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Base this model on the backend version.
JLoader::register('BiblestudyModelComment', JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/comment.php');
use Joomla\Utilities\ArrayHelper;

/**
 * Comment model class
 *
 * @since  7.0.0
 */
class BiblestudyModelCommentform extends BiblestudyModelComment
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    7.0.0
	 */
	public function getItem($pk = null)
	{
		// Initialise variables.
		$pk = (int) (!empty($pk)) ? $pk : $this->getState('comment.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($pk);

		// Check for a table object error.
		if ($return === false)
		{
			return false;
		}

		$properties = $table->getProperties(1);
		$value      = ArrayHelper::toObject($properties, 'JObject');

		return $value;
	}

	/**
	 * Get the return URL.
	 *
	 * @return  string    The return URL.
	 *
	 * @since    1.6
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{
		/** @type JApplicationSite $app */
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('a_id');
		$this->setState('comment.id', $pk);

		$option = $app->input->get('option', '', 'cmd');
		$app    = JFactory::getApplication();
		$app->setUserState($option . 'comment.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params   = $app->getParams();
		$this->setState('params', $params);
		$template = JBSMParams::getTemplateparams();
		$admin    = JBSMParams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);
		$params = $template->params;

		$t = $params->get('commentid');

		if (!$t)
		{
			$input = new JInput;
			$t     = $input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('admin', $admin);

		$this->setState('layout', $app->input->get('layout'));
	}
}
