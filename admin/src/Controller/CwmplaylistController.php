<?php

/**
 * Controller for a single Playlist
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

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Playlist item controller class
 *
 * @package  Proclaim.Admin
 * @since    10.3.3
 */
class CwmplaylistController extends FormController
{
    /**
     * The URL view list variable.
     *
     * @var    string
     * @since  10.3.3
     */
    protected $view_list = 'cwmplaylists';
}
