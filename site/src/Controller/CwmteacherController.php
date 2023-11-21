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
