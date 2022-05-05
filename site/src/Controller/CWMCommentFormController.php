<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CWMCommentController;
use CWM\Component\Proclaim\Administrator\Model\CWMCommentModel;
use CWM\Component\Proclaim\Site\Model\CWMCommentFormModel;
use CWM\Component\Proclaim\Site\Model\CWMCommentListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;

// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller for a Comment
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMCommentFormController extends CWMCommentController
{
	/**
	 * @var string View item
	 *
	 * @since    1.6
	 */
	protected $view_item = 'cwmcommentform';

	/**
	 * @var string View list
	 *
	 * @since    1.6
	 */
	protected $view_list = 'cwmcommentlist';

	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @throws \Exception
	 * @since    7.0.0
	 */
	public function __construct($config = array())
	{
		$input = new Input;
		$input->set('a_id', $input->get('a_id', 0, 'int'));
		parent::__construct($config);
	}

	/**
	 * Method to add a new record.
	 *
	 * @return    void    True if the article can be added, false if not.
	 *
	 * @since    1.6
	 */
	public function add()
	{
		if (!parent::add())
		{
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		}
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return    string    The return URL.
	 *
	 * @throws \Exception
	 * @since    1.6
	 */
	protected function getReturnPage()
	{
		$return = Factory::getApplication()->input->get('return', null, 'base64');

		if (empty($return) || !Uri::isInternal(base64_decode($return)))
		{
			return Uri::base() . 'index.php?option=com_proclaim&view=cwmcommentlist';
		}

		return base64_decode($return);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   CWMCommentListModel  $model  The model of the component being processed.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Set the model
		$model = new CWMCommentListModel;

		// Preset the redirect
		$this->setRedirect(
			Route::_('index.php?option=com_proclaim&view=cwmcommentlist' . $this->getRedirectToListAppend(), false)
		);

		return parent::batch($model);
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return    CWMCommentModel    The model.
	 *
	 * @since    1.5
	 */
	public function getModel($name = 'CWMCommentFormModel', $prefix = 'administrator', $config = array('ignore_request' => true))
	{
		return new CWMCommentModel;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return   boolean  True if access level checks pass, false otherwise.
	 *
	 * @throws \Exception
	 * @since    1.6
	 */
	public function cancel($key = 'a_id')
	{
		parent::cancel($key);

		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage());

		return true;
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return   boolean  True if access level check and checkout passes, false otherwise.
	 *
	 * @since    1.6
	 */
	public function edit($key = null, $urlVar = 'a_id')
	{
		return parent::edit($key, $urlVar);
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return    boolean    True if successful, false otherwise.
	 *
	 * @throws \Exception
	 * @since    1.6
	 */
	public function save($key = null, $urlVar = 'a_id')
	{
		$result = parent::save($key, $urlVar);

		// If ok, redirect to the return page.
		if ($result)
		{
			$this->setRedirect($this->getReturnPage());
		}

		return $result;
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
		// In the absence of better information, revert to the component permissions.
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
		return true;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   int     $recordId  The primary key id for the item.
	 * @param   string  $urlVar    The name of the URL variable for the id.
	 *
	 * @return    string    The arguments to append to the redirect URL.
	 *
	 * @throws \Exception
	 * @since    1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
	{
		$this->input = new Input;

		// Need to override the parent method completely.
		$tmpl   = $this->input->get('tmpl');
		$append = '';

		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		$append .= '&layout=edit';

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}

		$itemId = $this->input->getInt('Itemid');
		$return = $this->getReturnPage();
		$catId  = $this->input->getInt('catid', null);

		if ($itemId)
		{
			$append .= '&Itemid=' . $itemId;
		}

		if ($catId)
		{
			$append .= '&catid=' . $catId;
		}

		if ($return)
		{
			$append .= '&return=' . base64_encode($return);
		}

		return $append;
	}
}
