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

use CWM\Component\Proclaim\Administrator\Controller\Trait\MultiCampusAccessTrait;
use CWM\Component\Proclaim\Administrator\Controller\Trait\SectionAccessTrait;
use CWM\Component\Proclaim\Administrator\Helper\CwmactionlogHelper;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Topic controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmtopicController extends FormController
{
    use MultiCampusAccessTrait;
    use SectionAccessTrait;

    /**
     * The database table for access level checks.
     *
     * @var    string
     * @since  10.3.0
     */
    protected string $accessTable = '#__bsms_topics';

    /**
     * The access.xml section these records belong to.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected string $aclSection = 'topic';

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
        $id    = (int) $model->getState('cwmtopic.id');
        $isNew = empty($validData['id']);
        $key   = $isNew ? 'COM_PROCLAIM_ACTION_LOG_ITEM_ADDED' : 'COM_PROCLAIM_ACTION_LOG_ITEM_UPDATED';

        CwmactionlogHelper::log($key, $validData['topic_text'] ?? '', 'topic', $id);
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
     * @since   10.1.0
     */
    protected function allowEdit($data = [], $key = 'id'): bool
    {
        $denied = $this->checkRecordAccessLevel((int) ($data[$key] ?? 0));
        if ($denied === false) {
            return false;
        }

        return $this->allowSectionEdit();
    }
}
