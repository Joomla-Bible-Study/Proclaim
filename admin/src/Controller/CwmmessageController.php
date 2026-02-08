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

use CWM\Component\Proclaim\Administrator\Helper\CwmactionlogHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;


/**
 * Controller for Message
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmessageController extends FormController
{
    /**
     * Prevents Joomla's pluralization mechanism from altering the view name.
     *
     * @var   string
     * @since 7.0
     */
    protected $view_list = 'cwmmessages';

    /**
     * Reset Hits
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.5
     */
    public function resetHits(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmmessages', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $msg   = null;
        $input = $this->input;
        $id    = $input->get('id', 0, 'int');
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->update($db->qn('#__bsms_studies'))
            ->set($db->qn('hits') . ' = ' . $db->q('0'))
            ->where($db->qn('id') . ' = ' . (int)$id);
        $db->setQuery($query);

        if (!$db->execute()) {
            $msg = Text::_('JBS_CMN_ERROR_RESETTING_HITS');
            $this->setRedirect(
                'index.php?option=com_proclaim&view=cwmmessage&controller=administrator&layout=form&cid[]=' . $id,
                $msg
            );
        } else {
            $updated = $db->getAffectedRows();
            $msg     = Text::_('JBS_CMN_RESET_SUCCESSFUL') . ' ' . $updated . ' ' . Text::_('JBS_CMN_ROWS_RESET');
            $this->setRedirect(
                'index.php?option=com_proclaim&view=cwmmessage&controller=message&layout=form&cid[]=' . $id,
                $msg
            );
        }
    }

    /**
     * Method to run batch operations.
     *
     * @param   BaseDatabaseModel  $model  The model.
     *
     * @return  bool     True if successful, false otherwise, and an internal error is set.
     *
     * @throws \Exception
     * @since   1.6
     */
    public function batch($model = null): bool
    {
        $this->checkToken();

        // Set the model
        /** @var CwmmessagesModel $model */
        $model = $this->getModel('Cwmmessage', 'Administrator', []);

        // Preset the redirect
        $this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmmessages' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }

    /**
     * Method to save a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  bool  True if successful, false otherwise.
     *
     * @throws \Exception
     * @since 1.5
     */
    public function save($key = null, $urlVar = null): bool
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            throw new \Exception(Text::_('JINVALID_TOKEN'));
        }

        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmtopicModel $model */
        $model = $this->getModel('Cwmtopic');
        $app   = Factory::getApplication();
        $data  = $this->input->post->get('jform', [], 'array');

        // Get Tags - use topic_ids field (hidden input synced from fancy select)
        // Falls back to topics for backward compatibility
        $vTags = $data['topic_ids'] ?? $data['topics'] ?? '';

        if (\is_string($vTags)) {
            $iTags = $vTags !== '' ? explode(",", $vTags) : [];
        } else {
            $iTags = (array)$vTags;
        }

        // Filter out empty values
        $iTags = array_filter($iTags, function ($tag) {
            return $tag !== '' && $tag !== null;
        });

        // Remove Exerting StudyTopics tags
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $qurey = $db->getQuery(true);
        $qurey->delete($db->qn('#__bsms_studytopics'))
            ->where($db->qn('study_id') . ' = ' . (int) $data['id']);
        $db->setQuery($qurey);

        if (!$db->execute()) {
            $app->enqueueMessage('error deleting topics', 'error');
        }

        foreach ($iTags as $aTag) {
            if (is_numeric($aTag)) {
                // It's an existing tag.  Add it
                if ($aTag != "") {
                    $tagRow = Factory::getApplication()->bootComponent('com_proclaim')
                        ->getMVCFactory()->createTable('Cwmstudytopics', 'Administrator');
                    $tagRow->study_id = $data['id'];
                    $tagRow->topic_id = $aTag;

                    if (!$tagRow->store()) {
                        $app->enqueueMessage('Error Storing Tags with Message', 'error');

                        return false;
                    }
                }
            } else {
                // It's a new tag.  Gotta insert it into the Topics table.
                if ($aTag != "") {
                    $model->save(['topic_text' => $aTag, 'language' => $data['language']]);

                    // Gotta somehow make sure this isn't a duplicate...
                    $tagRow = Factory::getApplication()->bootComponent('com_proclaim')
                        ->getMVCFactory()->createTable('Cwmstudytopics', 'Administrator');
                    $tagRow->study_id = $data['id'];
                    $tagRow->topic_id = $model->getState('topic.id');

                    if (!$tagRow->store()) {
                        $app->enqueueMessage('Error Storing New Tags', 'error');

                        return false;
                    }
                }
            }
        }

        return parent::save($key, $urlVar);
    }

    /**
     * Method to run after a successful save.
     *
     * @param   BaseDatabaseModel  $model      The model.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = []): void
    {
        $id    = (int) $model->getState('cwmmessage.id');
        $isNew = (bool) $validData['id'] === 0 || empty($validData['id']);
        $key   = $isNew ? 'COM_PROCLAIM_ACTION_LOG_MESSAGE_ADDED' : 'COM_PROCLAIM_ACTION_LOG_MESSAGE_UPDATED';
        $title = $validData['studytitle'] ?? '';

        CwmactionlogHelper::log($key, $title, 'message', $id);
    }

    /**
     * Method overrides to check if you can edit an existing record.
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
        $recordId = (int)isset($data[$key]) ? $data[$key] : 0;
        $user     = Factory::getApplication()->getIdentity();
        $userId   = $user->get('id');

        // Check general edit permission first.
        if ($user->authorise('core.edit', 'com_proclaim.message.' . $recordId)) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if ($user->authorise('core.edit.own', 'com_proclaim.message.' . $recordId)) {
            // Now test the owner is the user.
            $ownerId = (int)isset($data['created_by']) ? $data['created_by'] : 0;

            if (empty($ownerId) && $recordId) {
                // Need to do a lookup from the model.
                $record = $this->getModel()->getItem($recordId);

                if (empty($record)) {
                    return false;
                }

                $ownerId = $record->created_by;
            }

            // If the owner matches 'me' then do the test.
            if ($ownerId === $userId) {
                return true;
            }
        }

        // Since there is no asset tracking, revert to the component permissions.
        return parent::allowEdit($data, $key);
    }
}
