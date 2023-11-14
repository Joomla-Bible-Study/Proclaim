<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel;
use CWM\Component\Proclaim\Administrator\Table\CwmmediafileTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;

/**
 * Controller For MediaFile
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmediafileController extends FormController
{
	/**
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanism from kicking in
	 *
	 * @var string
	 * @since 7.0
	 */
	protected $view_list = 'cwmmediafiles';

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $option = 'com_proclaim';

	/**
	 * Method to add a new record.
	 *
	 * @return  bool  True, if the record can be added, a error object if not.
	 *
	 * @throws  \Exception
	 * @since   12.2
	 */
	public function add(): bool
	{
		$app = Factory::getApplication();

		if (parent::add())
		{
			$app->setUserState('com_proclaim.edit.mediafile.createdate', null);
			$app->setUserState('com_proclaim.edit.mediafile.study_id', null);
			$app->setUserState('com_proclaim.edit.mediafile.server_id', null);

			return true;
		}

		return false;
	}

	/**
	 * Resets the User state for the server type. Needed to allow the value from the DB to be used
	 *
	 * @param   int     $key     ?
	 * @param   string  $urlVar  ?
	 *
	 * @return  boolean
	 *
	 * @throws  \Exception
	 * @since   9.0.0
	 */
	public function edit($key = null, $urlVar = null): bool
	{
		$app    = Factory::getApplication();
		$result = parent::edit();

		if ($result)
		{
			$app->setUserState('com_proclaim.edit.mediafile.createdate', null);
			$app->setUserState('com_proclaim.edit.mediafile.study_id', null);
			$app->setUserState('com_proclaim.edit.mediafile.server_id', null);
		}

		return true;
	}

	/**
	 * Handles XHR requests (i.e. File uploads)
	 *
	 * @return void
	 *
	 * @throws  \Exception
	 * @since   9.0.0
	 */
	public function xhr(): void
	{
		Session::checkToken('get') or die('Invalid Token');
		$input = Factory::getApplication()->input;

		$addonType = $input->get('type', 'Legacy', 'string');
		$handler   = $input->get('handler');

		// Load the addon
		$addon = CWMAddon::getInstance($addonType);

		if (method_exists($addon, $handler))
		{
			echo json_encode($addon->$handler($input), JSON_THROW_ON_ERROR);

			$app = Factory::getApplication();
			$app->close();
		}
		else
		{
			throw new \RuntimeException(Text::sprintf('Handler: "' . $handler . '" does not exist!'), 404);
		}
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   CwmmediafileModel  $model  The model.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null): bool
	{
		// Preset the redirect
		$this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmmediafiles' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @throws \Exception
	 * @since   12.2
	 */
	public function cancel($key = null): bool
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app   = Factory::getApplication();
		$model = $this->getModel();
		/** @type CwmmediafileTable $table */
		$table   = $model->getTable();
		$checkin = property_exists($table, 'checked_out');

		if (empty($key))
		{
			$key = (string) $table->getKeyName();
		}

		$recordId = $app->input->getInt($key);

		// Attempt to check in the current record.
		if ($recordId)
		{
			if ($checkin)
			{
				if ($model->checkin($recordId) === false)
				{
					// Check-in failed, go back to the record and display a notice.
					$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
					$this->setMessage($this->getError(), 'error');

					$this->setRedirect(
						Route::_(
							'index.php?option=' . $this->option . '&view=' . $this->view_item
							. $this->getRedirectToItemAppend($recordId, $key), false
						)
					);

					return false;
				}
			}
		}

		if ($this->input->getCmd('return') && parent::cancel($key))
		{
			$this->setRedirect(base64_decode($this->input->getCmd('return')));

			return true;
		}

		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

		return false;
	}

	/**
	 * Sets the server for this media record
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 * @since   9.0.0
	 */
	public function setServer(): void
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app   = Factory::getApplication();
		$input = $app->input;

		$data      = $input->get('jform', array(), 'post', 'array');
		$cdate     = $data['createdate'];
		$study_id  = $data['study_id'];
		$server_id = $data['server_id'];

		// Save server in the session
		$app->setUserState('com_proclaim.edit.mediafile.createdate', $cdate);
		$app->setUserState('com_proclaim.edit.mediafile.study_id', $study_id);
		$app->setUserState('com_proclaim.edit.mediafile.server_id', $server_id);

		$redirect = $this->getRedirectToItemAppend($data['id']);
		$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $redirect, false));
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   BaseModel  $model      The data model object.
	 * @param   array      $validData  The validated data.
	 *
	 * @return    void
	 *
	 * @throws   \Exception
	 * @since    3.1
	 */
	protected function postSaveHook($model, $validData = array()): void
	{
		$return = $this->input->getCmd('return');
		$task   = $this->input->get('task');

		if ($return && $task !== 'apply')
		{
			Factory::getApplication()->enqueueMessage(Text::_('JBS_MED_SAVE'), 'message');
			$this->setRedirect(base64_decode($return));
		}
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   12.2
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id'): string
	{
		$tmpl    = $this->input->get('tmpl');
		$layout  = $this->input->get('layout', 'edit', 'string');
		$return  = $this->input->getCmd('return');
		$options = $this->input->get('options');
		$append  = '';

		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		if ($layout)
		{
			$append .= '&layout=' . $layout;
		}

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		if ($options)
		{
			$append .= '&options=' . $options;
		}

		if ($return)
		{
			$append .= '&return=' . $return;
		}

		return $append;
	}
}
