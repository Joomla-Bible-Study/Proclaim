<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmlocationModel;
use Joomla\CMS\MVC\Controller\FormController;
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
     * @throws \Exception
     * @since   1.6
     */
    public function batch($model = null)
    {
        $this->checkToken();

        // Preset the redirect
        $this->setRedirect(
            Route::_('index.php?option=com_proclaim&view=cwmlocations' . $this->getRedirectToListAppend(), false)
        );

        return parent::batch($this->getModel());
    }
}
