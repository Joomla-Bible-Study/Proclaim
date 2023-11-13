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

use CWM\Component\Proclaim\Administrator\Model\CwmlocationModel;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;

/**
 * Location controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmlocationController extends FormController
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
	 * @param   CwmlocationModel  $model  The model.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		$this->checkToken();

		// Preset the redirect
		$this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmlocations' . $this->getRedirectToListAppend(), false));

		return parent::batch($this->getModel());
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  BaseDatabaseModel  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Cwmlocation', $prefix = '', $config = array('ignore_request' => true)): BaseDatabaseModel
	{
		return parent::getModel($name, $prefix, $config);
	}
}
