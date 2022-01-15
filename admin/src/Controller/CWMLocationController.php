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
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Model\CWMLocationModel;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * Location controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMLocationController extends FormController
{
	/**
	 * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
	 *
	 * @var string
	 * @since 7.0
	 */
	protected $view_list = 'cwmlocations';

	/**
	 * Method to run batch operations.
	 *
	 * @param   CWMLocationModel  $model  The model.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		// Preset the redirect
		$this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmlocations' . $this->getRedirectToListAppend(), false));

		return parent::batch($this->getModel('CWMLocation', '', array()));
	}
}
