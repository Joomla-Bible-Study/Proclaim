<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\Model;
// No Direct Access
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Factory;
use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use CWM\Component\Proclaim\Administrator\Controller\MessageController;
// Base this model on the backend version.
//JLoader::register('BiblestudyModelMessage', JPATH_ADMINISTRATOR . '/components/com_proclaim/models/MessageController.php');

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Model class for Message
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class CWMMessageFormModel extends ItemModel
{
	/**
	 * Method to get article data.
	 *
	 * @param   int  $pk  The id of the article.
	 *
	 * @return    mixed    Content item data object on success, false on failure.
	 *
	 * @since 7.0
	 */
	public function getItem($pk = null)
	{
		// Initialise variables.
		$pk = (int) (!empty($pk)) ? $pk : $this->getState('sermon.id');

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

		// Convert params field to Registry.
		$registry = new Registry;
		$registry->loadString($value->params);
		$value->params = $registry->toArray();

		return $value;
	}

	/**
	 * Get the return URL.
	 *
	 * @return    string    The return URL.
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
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('a_id');
		$this->setState('sermon.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		/** @var Registry $params */
		$params   = $app->getParams();
		$this->setState('params', $params);
		$template = CWMParams::getTemplateparams();
		$admin    = CWMParams::getAdmin();

		$params->merge($template->params);
		$params->merge($admin->params);
		$t = $params->get('teachertemplateid');

		if (!$t)
		{
			$input = Factory::getApplication();
			$t     = $input->get('t', 1, 'int');
		}

		$template->id = $t;

		$this->setState('template', $template);
		$this->setState('administrator', $admin);

		$this->setState('layout', $app->input->get('layout'));
	}
}
