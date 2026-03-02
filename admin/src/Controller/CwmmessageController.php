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
use CWM\Component\Proclaim\Administrator\Helper\CwmaiHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmtopicSuggestionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

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
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__bsms_studies'))
            ->set($db->quoteName('hits') . ' = ' . $db->q('0'))
            ->where($db->quoteName('id') . ' = ' . (int)$id);
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

        // Auto-match existing topics from text if none manually assigned
        if (empty($iTags)) {
            $text = strip_tags(($data['studyintro'] ?? '') . ' ' . ($data['studytext'] ?? ''));

            if (!empty(trim($text))) {
                $matched = CwmtopicSuggestionHelper::matchExistingTopics($text);
                $iTags   = array_column($matched, 'id');
            }
        }

        // Remove Exerting StudyTopics tags
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $qurey = $db->getQuery(true);
        $qurey->delete($db->quoteName('#__bsms_studytopics'))
            ->where($db->quoteName('study_id') . ' = ' . (int) $data['id']);
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
     * AJAX endpoint: suggest topics from sermon text
     *
     * Reads studyintro and studytext from POST, returns matched existing topics
     * and keyword suggestions as JSON.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function suggestTopics(): void
    {
        if (!Session::checkToken('get') && !Session::checkToken('post')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => Text::_('JINVALID_TOKEN')]);
            Factory::getApplication()->close();

            return;
        }

        $input     = $this->input;
        $introText = $input->post->getString('studyintro', '');
        $studyText = $input->post->getString('studytext', '');
        $text      = strip_tags($introText . ' ' . $studyText);

        $existing  = CwmtopicSuggestionHelper::matchExistingTopics($text);
        $excludes  = array_column($existing, 'text');
        $suggested = CwmtopicSuggestionHelper::extractKeywordSuggestions($text, $excludes);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'existing'  => $existing,
            'suggested' => $suggested,
        ]);

        Factory::getApplication()->close();
    }

    /**
     * AJAX endpoint: generate sermon content using AI
     *
     * Gathers sermon context (title, scripture, media metadata) and calls the
     * configured AI provider to generate topics, description, and study text.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function aiAssist(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get') && !Session::checkToken('post')) {
            echo json_encode(['error' => Text::_('JINVALID_TOKEN')]);
            Factory::getApplication()->close();

            return;
        }

        $input = $this->input;

        // Build context from POST data
        $context = [
            'title'             => $input->post->getString('title', ''),
            'scripture'         => $input->post->getString('scripture', ''),
            'existing_intro'    => $input->post->getString('studyintro', ''),
            'existing_text'     => $input->post->getString('studytext', ''),
            'existing_topics'   => $input->post->getString('topics', ''),
            'video_title'       => '',
            'video_description' => '',
            'video_tags'        => [],
        ];

        // Attempt to get video metadata from attached media file
        $mediaFileId = $input->post->getInt('media_file_id', 0);

        if ($mediaFileId > 0) {
            $videoContext = CwmaiHelper::getVideoContext($mediaFileId);
            $context      = array_merge($context, $videoContext);
        }

        try {
            $result = CwmaiHelper::generateSermonContent($context);
            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }

        Factory::getApplication()->close();
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
        $recordId = (int) ($data[$key] ?? 0);
        $user     = Factory::getApplication()->getIdentity();
        $userId   = $user->id;

        // Non-admin users must have access to the item's view level
        if (!$user->authorise('core.admin') && $recordId > 0) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('access'))
                ->from($db->quoteName('#__bsms_studies'))
                ->where($db->quoteName('id') . ' = :rid')
                ->bind(':rid', $recordId, ParameterType::INTEGER);
            $db->setQuery($query);
            $access = (int) $db->loadResult();

            if ($access && !\in_array($access, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

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
