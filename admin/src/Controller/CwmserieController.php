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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * Controller for a Serie
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmserieController extends FormController
{
    /**
     * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
     *
     * @var   string
     * @since 7.0
     */
    protected $view_list = 'cwmseries';

    /**
     * Method to run batch operations.
     *
     * @param   object  $model  The model.
     *
     * @return  boolean     True if successful, false otherwise and internal error is set.
     *
     * @since   1.6
     */
    public function batch($model = null): bool
    {
        // Preset the redirect
        $this->setRedirect(
            Route::_('index.php?option=com_proclaim&view=cwmseries' . $this->getRedirectToListAppend(), false)
        );

        return parent::batch($this->getModel('Cwmserie', '', []));
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
    protected function allowAdd($data = []): bool
    {
        $allow = null;

        return $allow ?? parent::allowAdd();
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @throws \Exception
     * @since   1.6
     */
    protected function allowEdit($data = [], $key = 'id'): bool
    {
        $recordId = (int)isset($data[$key]) ? $data[$key] : 0;
        $user     = Factory::getApplication()->getIdentity();

        // Check general edit permission first.
        if ($user->authorise('core.edit', 'com_proclaim.serie.' . $recordId)) {
            return true;
        }

        // Since there is no asset tracking, revert to the component permissions.
        return parent::allowEdit($data, $key);
    }
}
