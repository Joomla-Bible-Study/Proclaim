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

use CWM\Component\Proclaim\Administrator\Controller\Trait\MultiCampusAccessTrait;
use CWM\Component\Proclaim\Administrator\Helper\CwmactionlogHelper;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Playlist item controller class
 *
 * @package  Proclaim.Admin
 * @since    10.3.3
 */
class CwmplaylistController extends FormController
{
    use MultiCampusAccessTrait;

    /**
     * The URL view list variable.
     *
     * @var    string
     * @since  10.3.3
     */
    protected $view_list = 'cwmplaylists';

    /**
     * The database table for access level checks.
     *
     * @var    string
     * @since  10.5.6
     */
    protected string $accessTable = '#__bsms_playlists';

    /**
     * Method override to check if you can edit an existing record.
     *
     * Without this, a non-admin editor in a multi-campus install could edit
     * any playlist regardless of its access level — every other ACL-scoped
     * singular controller (Location, Serie, Topic) already uses this trait.
     * See #1438.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  bool
     *
     * @throws \Exception
     * @since   10.5.6
     */
    protected function allowEdit($data = [], $key = 'id'): bool
    {
        $denied = $this->checkRecordAccessLevel((int) ($data[$key] ?? 0));
        if ($denied === false) {
            return false;
        }

        return parent::allowEdit($data, $key);
    }

    /**
     * Method to run after a successful save — records the action in Joomla's logs.
     *
     * @param   BaseDatabaseModel  $model      The model.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = []): void
    {
        $id    = (int) $model->getState('cwmplaylist.id');
        $isNew = empty($validData['id']);
        $key   = $isNew ? 'COM_PROCLAIM_ACTION_LOG_ITEM_ADDED' : 'COM_PROCLAIM_ACTION_LOG_ITEM_UPDATED';

        CwmactionlogHelper::log($key, $validData['title'] ?? '', 'playlist', $id);
    }
}
