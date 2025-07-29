<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use CWM\Component\Proclaim\Administrator\Table\CwmmessageTable;
use Exception;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Message model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmessageModel extends AdminModel
{
    /**
     * The type alias for this content type (for example, 'com_content.article').
     *
     * @var      string
     * @since    3.2
     */
    public $typeAlias = 'com_proclaim.cwmmessage';
    /**
     * @var    string  The prefix to use with controller messages.
     * @since  1.6
     */
    protected $text_prefix = 'com_proclaim';

    /**
     * Allowed batch commands
     *
     * @var array
     * @since 10.0.0
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'language_id'   => 'batchLanguage',
        'teacher'       => 'batchTeacher',
        'series'        => 'batchSeries',
        'messageType'   => 'batchMessagetype',
    ];

    /**
     * Duplicate Check
     *
     * @param   int  $study_id  Study ID
     * @param   int  $topic_id  Topic ID
     *
     * @return bool
     *
     * @since 7.0
     */
    public function isDuplicate(int $study_id, int $topic_id): bool
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__bsms_studytopics')
            ->where('study_id = ' . $study_id)
            ->where('topic_id = ' . $topic_id);
        $db->setQuery($query);
        $tresult = $db->loadObject();

        if (empty($tresult)) {
            return false;
        }

        return true;
    }

    /**
     * Gets all the topics associated with a particular study
     *
     * @return string JSON Object containing the topics
     *
     * @throws Exception
     * @since 7.0.1
     */
    public function getTopics(): string
    {
        // Do search in case of present study only, suppress otherwise
        $input          = new Input();
        $translatedList = array();
        $id             = $input->get('a_id', 0, 'int');

        if (!$id) {
            $id = $input->get('id', 0, 'int');
        }

        if ($id > 0) {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);

            $query->select('topic.id, topic.topic_text, topic.params AS topic_params');
            $query->from('#__bsms_studytopics AS studytopics');

            $query->join('LEFT', '#__bsms_topics AS topic ON topic.id = studytopics.topic_id');
            $query->where('studytopics.study_id = ' . (int)$id);

            $db->setQuery($query->__toString());
            $topics = $db->loadObjectList();

            if ($topics) {
                foreach ($topics as $topic) {
                    $text             = Cwmtranslated::getTopicItemTranslated($topic);
                    $translatedList[] = array(
                        'id'   => $topic->id,
                        'name' => $text
                    );
                }
            }
        }

        return json_encode($translatedList, JSON_THROW_ON_ERROR);
    }

    /**
     * Gets all topics available
     *
     * @return string JSON Object containing the topics
     *
     * @throws Exception
     * @since 7.0.1
     */
    public function getAlltopics()
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $query->select('topic.id, topic.topic_text, topic.params AS topic_params');
        $query->from('#__bsms_topics AS topic');

        $db->setQuery($query->__toString());
        $topics         = $db->loadObjectList();
        $translatedList = array();

        if ($topics) {
            foreach ($topics as $topic) {
                $text             = Cwmtranslated::getTopicItemTranslated($topic);
                $translatedList[] = array(
                    'id'   => $topic->id,
                    'name' => $text
                );
            }
        }

        return json_encode($translatedList, JSON_THROW_ON_ERROR);
    }

    /**
     * Returns a list of media files associated with this study
     *
     * @return object
     * @throws Exception
     * @since   7.0
     */
    public function getMediaFiles()
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $query->select('m.id, m.language, m.published, m.createdate, m.params, m.access');
        $query->from('#__bsms_mediafiles AS m');
        $query->where('m.study_id = ' . (int)$this->getItem()->id);
        $query->where('(m.published = 0 OR m.published = 1)');
        $query->order('m.createdate DESC');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = m.access');

        $db->setQuery($query->__toString());
        $mediafiles = $db->loadObjectList();

        foreach ($mediafiles as $i => $mediafile) {
            $reg = new Registry();
            $reg->loadString($mediafile->params);
            $mediafiles[$i]->params = $reg;
        }

        return $mediafiles;
    }

    /**
     * Method to get media item
     *
     * @param   int  $pk  int
     *
     * @return  mixed
     *
     * @throws  Exception
     * @since   9.0.0
     */
    public function getItem($pk = null): mixed
    {
        $input = Factory::getApplication()->getInput();

        // The front end calls this model and uses a_id to avoid id clashes, so we need to check for that first.
        if ($input->get('a_id')) {
            $pk = $input->get('a_id', 0);
        } else {
            // The back end uses id so we use that the rest of the time and set it to 0 by default.
            $pk = $input->get('id', 0);
        }

        if (!empty($this->data)) {
            return $this->data;
        }

        $this->data = parent::getItem($pk);

        return $this->data;
    }

    /**
     * Overrides the AdminModule save routine to save the topics(tags)
     *
     * @param   array  $data  The form data.
     *
     * @return bool
     *
     * @throws Exception
     * @since 7.0.1
     */
    public function save($data): bool
    {
        /** @var Registry $params */
        $app           = Factory::getApplication();
        $params        = Cwmparams::getAdmin()->params;
        $input         = $app->input;
        $path          = 'images/biblestudy/studies/' . (int)$data['id'];
        $image         = HTMLHelper::cleanImageURL((string)$data['image']);
        $data['image'] = $image->url;
        $this->cleanCache();

        if ($input->get('a_id')) {
            $data['id'] = $input->get('a_id');
        }

        // If no image uploaded, just save data as usual
        if (empty($data['image']) || strpos($data['image'], 'thumb_') !== false) {
            if (empty($data['image'])) {
                // Modify model data if no image is set.
                $data['thumbnailm'] = "";
            } elseif (!CwmproclaimHelper::startsWith(basename($data['image']), 'thumb_')) {
                // Modify model data
                $data['thumbnailm'] = $path . '/thumb_' . basename($data['image']);
            }

            return parent::save($data);
        }

        Cwmthumbnail::create($data['image'], $path, $params->get('thumbnail_study_size', 100));

        // Modify model data
        $data['thumbnailm'] = $path . '/thumb_' . basename($data['image']);

        return parent::save($data);
    }

    /**
     * Custom clean the cache of com_proclaim and proclaim modules
     *
     * @param   string  $group      The cache group
     *
     * @return  void
     *
     * @since    1.6
     */
    protected function cleanCache($group = null): void
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }

    /**
     * Get the form data
     *
     * @param   array  $data      Data for the form.
     * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return bool|Form
     *
     * @throws Exception
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true): bool|Form
    {
        $app = Factory::getApplication();

        // Get the form.
        $form = $this->loadForm(
            'com_proclaim.cwmmessage',
            'message',
            array('control' => 'jform', 'load_data' => $loadData)
        );

        if ($form === null) {
            return false;
        }

        // Object uses for checking edit state permission of article
        $record = new \stdClass();

        // Get ID of the article from input, for frontend, we use a_id while backend uses id
        $messageIdFromInput = $app->isClient('site')
            ? $app->input->getInt('a_id', 0)
            : $app->input->getInt('id', 0);

        // On edit article, we get ID of article from article.id state, but on save, we use data from input
        $id = (int)$this->getState('message.id', $messageIdFromInput);

        $record->id = $id;

        // Check for an existing message.
        // Modify the form based on Edit State access controls.
        if (!$this->canEditState($record)) {
            // Disable fields for display.
            $form->setFieldAttribute('featured', 'disabled', 'true');
            $form->setFieldAttribute('featured_up', 'disabled', 'true');
            $form->setFieldAttribute('featured_down', 'disabled', 'true');
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a message you can edit.
            $form->setFieldAttribute('featured', 'filter', 'unset');
            $form->setFieldAttribute('featured_up', 'filter', 'unset');
            $form->setFieldAttribute('featured_down', 'filter', 'unset');
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to check-out a row for editing.
     *
     * @param   int  $pk  The numeric id of the primary key.
     *
     * @return  bool  False on failure or error, true otherwise.
     *
     * @since   11.1
     */
    public function checkout($pk = null): bool
    {
        return true;
    }

    /**
     * Saves the manually set order of records.
     *
     * @param   array  $pks    An array of primary key ids.
     * @param   int    $order  +1 or -1
     *
     * @return  mixed
     *
     * @throws Exception
     * @since    11.1
     */
    public function saveorder($pks = null, $order = null): mixed
    {
        $db         = Factory::getContainer()->get('DatabaseDriver');
        $row        = new CwmmessageTable($db);
        $conditions = array();

        // Update ordering values
        foreach ($pks as $i => $pk) {
            $row->load((int)$pk);

            // Track categories
            $groupings[] = $row->id;

            if ($row->ordering != $order[$i]) {
                $row->ordering = $order[$i];

                if (!$row->store()) {
                    $this->setError($this->_db->getErrorMsg());

                    return false;
                }

                // Remember to reorder within position and client_id
                $condition = $this->getReorderConditions($row);
                $found     = false;

                foreach ($conditions as $cond) {
                    if ($cond[1] == $condition) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $key          = $row->getKeyName();
                    $conditions[] = array($row->$key, $condition);
                }
            }
        }

        foreach ($conditions as $cond) {
            // $row->reorder('id = ' . (int) $group);
            $row->load($cond[0]);
            $row->reorder($cond[1]);
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch popup changes for a group of media files.
     *
     * @param   string  $value     The new value matching a client.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  bool  True if successful, false otherwise and internal error is set.
     *
     * @throws Exception
     * @since   2.5
     */
    protected function batchTeacher($value, $pks, $contexts): bool
    {
        // Set the variables
        $user = Factory::getApplication()->getIdentity();
        /** @var CwmmessageTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $table->teacher_id = (int)$value;

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @throws  Exception
     * @since   3.0
     */
    public function getTable($name = 'Cwmmessage', $prefix = '', $options = array()): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Batch popup changes for a group of media files.
     *
     * @param   string  $value     The new value matching a client.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  boolean  True if successful, false otherwise and internal error is set.
     *
     * @throws Exception
     * @since   2.5
     */
    protected function batchSeries($value, $pks, $contexts): bool
    {
        // Set the variables
        $user = Factory::getApplication()->getIdentity();
        /** @var CwmmessageTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $table->series_id = (int)$value;

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch popup changes for a group of media files.
     *
     * @param   string  $value     The new value matching a client.
     * @param   array   $pks       An array of row IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  bool  True if successful, false otherwise and internal error is set.
     *
     * @throws Exception
     * @since   2.5
     */
    protected function batchMessageType(string $value, array $pks, array $contexts): bool
    {
        // Set the variables
        $user = Factory::getApplication()->getIdentity();
        /** @var CwmmessageTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $table->messagetype = (int)$value;

                if (!$table->store()) {
                    $this->setError($table->getError());

                    return false;
                }
            } else {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed    The default data is an empty array.
     *
     * @throws  Exception
     * @since   7.0
     */
    protected function loadFormData(): mixed
    {
        $data = Factory::getApplication()->getUserState('com_proclaim.edit.message.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   CwmmessageTable  $table  A reference to a JTable object.
     *
     * @return    void
     *
     * @throws Exception
     * @since    1.6
     */
    protected function prepareTable($table): void
    {
        $date = Factory::getDate();
        $user = Factory::getApplication()->getIdentity();

        // Set the publishing date to now
        if ($table->published === Workflow::CONDITION_PUBLISHED && (int)$table->publish_up === 0) {
            $table->publish_up = Factory::getDate()->toSql();
        }

        if ($table->published === Workflow::CONDITION_PUBLISHED && (int)$table->publish_down === 0) {
            $table->publish_down = null;
        }

        $table->studytitle = htmlspecialchars_decode($table->studytitle, ENT_QUOTES);
        $table->alias      = ApplicationHelper::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = ApplicationHelper::stringURLSafe($table->studytitle);
        }

        if (empty($table->id)) {
            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db    = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true)
                    ->select('MAX(ordering)')
                    ->from($db->quoteName('#__bsms_studies'));
                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        } else {
            // Set the values
            $table->modified    = $date->toSql();
            $table->modified_by = $user->get('id');
        }
    }
}
