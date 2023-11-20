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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Template Code controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmtemplatecodeController extends FormController
{
    /**
     * Protect the view
     *
     * @var string
     *
     * @since 1.5
     */
    protected $view_list = 'cwmtemplatecodes';

    /**
     * The URL option for the component.
     *
     * @var    string
     * @since  12.2
     */
    protected $option = 'com_proclaim';
}
