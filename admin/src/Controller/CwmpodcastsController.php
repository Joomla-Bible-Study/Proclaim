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

use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;

/**
 * Controller for Podcasts
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmpodcastsController extends AdminController
{
    /**
     * Write the XML file Called from admin podcast list page.
     * Used for the Podcasts Page to create xml files.
     *
     * @return void
     *
     * @throws \Exception
     * @since 10.0.0
     */
    public function writeXMLFile(): void
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $podcasts = new Cwmpodcast();
        $result   = $podcasts->makePodcasts();
        $this->setRedirect('index.php?option=com_proclaim&view=Cwmpodcasts&' . Session::getFormToken() . '=1', $result);
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  The array of possible config values. Optional.
     *
     * @return  BaseDatabaseModel
     *
     * @since   1.6
     */
    public function getModel($name = 'Cwmpodcast', $prefix = 'Administrator', $config = ['ignore_request' => true]): BaseDatabaseModel
    {
        return parent::getModel($name, $prefix, $config);
    }
}
