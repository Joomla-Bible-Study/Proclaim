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
use CWM\Component\Proclaim\Administrator\Helper\CwmprotectedMove;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
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
     * Move the selected media files into protected storage.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public function protect(): void
    {
        $this->moveSelected(true);
    }

    /**
     * Move the selected media files back out of protected storage.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public function unprotect(): void
    {
        $this->moveSelected(false);
    }

    /**
     * Run one direction of the protected-storage move over the selection.
     *
     * Per-item outcomes, not one verdict: a selection is allowed to mix
     * eligible and ineligible rows, and the administrator is told which were
     * skipped and why rather than the whole batch failing over one podcast
     * reference.
     *
     * @param   bool  $into  True to move into protected storage, false out.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    private function moveSelected(bool $into): void
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $ids = array_filter(array_map('intval', (array) $this->input->get('cid', [], 'array')));

        // Moving a file rewrites its record; the permission is the record's.
        if (!$app->getIdentity()->authorise('core.edit', 'com_proclaim.mediafile')) {
            $app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 'error');
            $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

            return;
        }

        if ($ids === []) {
            $app->enqueueMessage(Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 'warning');
            $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

            return;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $moved = 0;

        foreach ($ids as $id) {
            $result = $into
                ? CwmprotectedMove::moveIn($db, $id)
                : CwmprotectedMove::moveOut($db, $id);

            if ($result['ok']) {
                $moved++;

                continue;
            }

            $app->enqueueMessage(
                Text::sprintf('JBS_MED_PROTECT_SKIPPED', '#' . $id, Text::_($result['reason'])),
                'warning'
            );
        }

        if ($moved > 0) {
            $app->enqueueMessage(
                Text::plural($into ? 'JBS_MED_PROTECT_DONE_N' : 'JBS_MED_UNPROTECT_DONE_N', $moved)
            );
        }

        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
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
