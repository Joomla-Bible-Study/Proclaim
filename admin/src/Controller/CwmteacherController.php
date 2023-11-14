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

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for Teacher
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmteacherController extends FormController
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
}
