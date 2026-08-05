<?php

/**
 * Part of Proclaim Package
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

use CWM\Component\Proclaim\Administrator\Controller\Trait\CwmActionlogListTrait;
use CWM\Component\Proclaim\Administrator\Helper\CwmcountHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmmediafilesHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;

/**
 * MediaFiles list controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmediafilesController extends AdminController
{
    use CwmActionlogListTrait;

    /**
     * @var string
     * @since 10.3.3
     */
    protected string $actionlogType = 'mediafile';

    /**
     * @var string
     * @since 10.3.3
     */
    protected string $actionlogTable = '#__bsms_mediafiles';

    /**
     * @var string
     * @since 10.3.3
     */
    protected string $actionlogTitleColumn = '';

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
    public function getModel($name = 'Cwmmediafile', $prefix = 'Administrator', $config = ['ignore_request' => true]): BaseDatabaseModel
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to get the JSON-encoded counts for Media Files
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public function getQuickIconMediaFiles(): void
    {
        CwmcountHelper::sendQuickIconResponse('#__bsms_mediafiles', 'COM_PROCLAIM_N_QUICKICON_MEDIAFILES', 'study');
    }

    /**
     * AJAX: check whether selected media files have physical files on delete-enabled servers.
     *
     * Returns JSON: {success: bool, hasFiles: bool, files: [{id, filename, message, server}]}
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function checkDeleteFiles(): void
    {
        $app = Factory::getApplication();

        if (!Session::checkToken('get')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Invalid token']);
            $app->close();

            return;
        }

        $ids = $this->input->get('cid', [], 'array');
        ArrayHelper::toInteger($ids);
        $ids = array_filter($ids);

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $files = CwmmediafilesHelper::findFilesForDelete($db, $ids, 'id');

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'  => true,
            'hasFiles' => !empty($files),
            'files'    => $files,
        ]);
        $app->close();
    }
}
