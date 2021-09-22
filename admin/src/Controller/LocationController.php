<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\BibleStudy\Administrator\Controller;

// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Location controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class LocationController extends FormController
{
	/**
	 * Method to run batch operations.
	 *
	 * @param   JModelLegacy  $model  The model.
	 *
	 * @return  boolean     True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.6
	 */
	public function batch($model = null)
	{
		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_proclaim&view=locations' . $this->getRedirectToListAppend(), false));

		return parent::batch($this->getModel('Location', '', array()));
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
	 * @since 7.1.0
	 */
	public function getModel($name = 'Location', $prefix = 'BiblestudyModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
}
