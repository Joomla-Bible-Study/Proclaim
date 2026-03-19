<?php

/**
 * Controller for a Comment
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Controller\Trait\MultiCampusAccessTrait;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;

/**
 * Controller for a Comment
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmcommentController extends FormController
{
    use MultiCampusAccessTrait;

    /**
     * Prevents Joomla's pluralization mechanism from altering the view name.
     *
     * @var   string
     * @since 7.0
     */
    protected $view_list = 'cwmcomments';

    /**
     * The database table for access level checks.
     *
     * @var    string
     * @since  10.3.0
     */
    protected string $accessTable = '#__bsms_comments';

    /**
     * Method to run batch operations.
     *
     * @param   BaseDatabaseModel  $model  The model.
     *
     * @return  bool     True if successful, false otherwise and internal error is set.
     *
     * @since   1.6
     */
    public function batch($model = null): bool
    {
        // Set the model
        if ($model === null) {
            $model = $this->getModel('Comment', '', []);
        }

        // Preset the redirect
        $this->setRedirect(
            Route::_('index.php?option=com_proclaim&view=cwmcomments' . $this->getRedirectToListAppend(), false)
        );

        return parent::batch($model);
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  bool
     *
     * @throws \Exception
     * @since   1.6
     */
    protected function allowEdit($data = [], $key = 'id'): bool
    {
        $denied = $this->checkRecordAccessLevel((int) ($data[$key] ?? 0));
        if ($denied === false) {
            return false;
        }

        return parent::allowEdit($data, $key);
    }
}
