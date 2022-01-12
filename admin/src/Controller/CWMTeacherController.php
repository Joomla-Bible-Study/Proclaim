<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// No Direct Access
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

/**
 * Controller for Teacher
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMTeacherController extends FormController
{
	/**
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
	 *
	 * @var   string
	 * @since 7.0
	 */
	protected $view_list = 'cwmteachers';

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $option = 'com_proclaim';

	/**
	 * Method to run batch operations.
	 *
	 * @param   BaseModel  $model  The model.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		// Preset the redirect
		$this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmteachers' . $this->getRedirectToListAppend(), false));

		return parent::batch($this->getModel());
	}

	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since 7.0
	 */
	public function getModel($name = 'CWMTeacher', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
