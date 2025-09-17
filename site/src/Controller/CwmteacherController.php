<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller class for Teacher
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmteacherController extends BaseController
{
    /**
     * Display the edit form
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0
     */
    public function view(): void
    {
        $input = Factory::getApplication()->input;
        $input->set('view', 'cwmteacher');
        $input->set('layout', 'default');

        $this->display();
    }
}
