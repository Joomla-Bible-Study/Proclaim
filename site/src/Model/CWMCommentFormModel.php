<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Model;

// No Direct Access
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use CWM\Component\Proclaim\Administrator\Model\CWMCommentModel;
use CWM\Component\Proclaim\Administrator\Table\CWMCommentTable;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

/**
 * Comment model class
 *
 * @since  7.0.0
 */
class CWMCommentFormModel extends CWMCommentModel
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @throws \Exception
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

		return ArrayHelper::toObject($properties, 'JObject');
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  CWMCommentTable  A Table object
	 *
	 * @throws  \Exception
	 * @since   3.0
	 */
	public function getTable($name = '', $prefix = '', $options = array())
	{
		$db = Factory::getDbo();

		return new CWMCommentTable($db);
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
	 * @throws \Exception
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('a_id');
		$this->setState('comment.id', $pk);

		$option = $app->input->get('option', '', 'cmd');
		$app    = Factory::getApplication();
		$app->setUserState($option . 'comment.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		$template = CWMParams::getTemplateparams();
		$admin    = CWMParams::getAdmin();

		$template->params->merge($params);
		$template->params->merge($admin->params);
		$params = $template->params;

		$t = $params->get('commentid');

		if (!$t)
		{
			$t     = $app->input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('administrator', $admin);

		$this->setState('layout', $app->input->get('layout'));
	}
}
