<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmImageMigration;
use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmschemaorgHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmstudyteacherHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use CWM\Component\Proclaim\Administrator\Helper\ScriptureReference;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

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
        'location'      => 'batchLocation',
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
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__bsms_studytopics'))
            ->where($db->quoteName('study_id') . ' = ' . (int) $study_id)
            ->where($db->quoteName('topic_id') . ' = ' . (int) $topic_id);
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
     * @throws \Exception
     * @since 7.0.1
     */
    public function getTopics(): string
    {
        // Do search in case of present study only, suppress otherwise
        $input          = Factory::getApplication()->getInput();
        $translatedList = [];
        $id             = $input->get('a_id', 0, 'int');

        if (!$id) {
            $id = $input->get('id', 0, 'int');
        }

        if ($id > 0) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);

            $query->select($db->quoteName('topic.id') . ', ' . $db->quoteName('topic.topic_text') . ', ' . $db->quoteName('topic.params', 'topic_params'));
            $query->from($db->quoteName('#__bsms_studytopics', 'studytopics'));

            $query->join('LEFT', $db->quoteName('#__bsms_topics', 'topic') . ' ON ' . $db->quoteName('topic.id') . ' = ' . $db->quoteName('studytopics.topic_id'));
            $query->where($db->quoteName('studytopics.study_id') . ' = ' . (int)$id);

            $db->setQuery($query);
            $topics = $db->loadObjectList();

            if ($topics) {
                foreach ($topics as $topic) {
                    $text             = Cwmtranslated::getTopicItemTranslated($topic);
                    $translatedList[] = [
                        'id'   => $topic->id,
                        'name' => $text,
                    ];
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
     * @throws \Exception
     * @since 7.0.1
     */
    public function getAlltopics(): string
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select($db->quoteName('topic.id') . ', ' . $db->quoteName('topic.topic_text') . ', ' . $db->quoteName('topic.params', 'topic_params'));
        $query->from($db->quoteName('#__bsms_topics', 'topic'));

        $db->setQuery($query);
        $topics         = $db->loadObjectList();
        $translatedList = [];

        if ($topics) {
            foreach ($topics as $topic) {
                $text             = Cwmtranslated::getTopicItemTranslated($topic);
                $translatedList[] = [
                    'id'   => $topic->id,
                    'name' => $text,
                ];
            }
        }

        return json_encode($translatedList, JSON_THROW_ON_ERROR);
    }

    /**
     * Returns a list of media files associated with this study
     *
     * @return array
     * @throws \Exception
     * @since   7.0
     */
    public function getMediaFiles(): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(
            $db->quoteName(
                [
                    'm.id', 'm.language', 'm.published', 'm.createdate',
                    'm.params', 'm.access', 'm.ordering', 'm.metadata',
                    'm.server_id', 'm.hits', 'm.downloads', 'm.plays',
                ]
            )
        );
        $query->from($db->quoteName('#__bsms_mediafiles', 'm'));
        $query->where($db->quoteName('m.study_id') . ' = ' . (int) $this->getItem()->id);
        $query->whereIn($db->quoteName('m.published'), [0, 1, 2]);
        $query->order($db->quoteName('m.ordering') . ' ASC, ' . $db->quoteName('m.createdate') . ' DESC');

        // Join over the asset groups.
        $query->select($db->quoteName('ag.title', 'access_level'));
        $query->join('LEFT', $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('m.access'));

        // Join over the server to get name and type.
        $query->select($db->quoteName(['s.server_name', 's.type'], ['server_name', 'server_type']));
        $query->join('LEFT', $db->quoteName('#__bsms_servers', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.server_id'));

        $db->setQuery($query);
        $mediafiles = $db->loadObjectList();

        foreach ($mediafiles as $i => $mediafile) {
            $reg = new Registry();
            $reg->loadString($mediafile->params);
            $mediafiles[$i]->params = $reg;

            $meta = new Registry();
            $meta->loadString($mediafile->metadata);
            $mediafiles[$i]->metadata = $meta;
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
     * @throws  \Exception
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

        // Load scripture references from junction table for the subform
        if ($this->data && !empty($this->data->id)) {
            $refs = CwmscriptureHelper::getScripturesForStudy((int) $this->data->id);

            $subformData = [];

            if (!empty($refs)) {
                // Junction table has data — use it
                foreach ($refs as $ref) {
                    $subformData[] = [
                        'reference_text' => $ref->referenceText,
                        'bible_version'  => $ref->bibleVersion,
                    ];
                }
            } else {
                // Fallback: build from legacy flat columns for pre-migration records
                $bn1 = (int) ($this->data->booknumber ?? 0);

                if ($bn1 > 0) {
                    $subformData[] = [
                        'reference_text' => CwmscriptureHelper::formatReference(
                            $bn1,
                            (int) ($this->data->chapter_begin ?? 0),
                            (int) ($this->data->verse_begin ?? 0),
                            (int) ($this->data->chapter_end ?? 0),
                            (int) ($this->data->verse_end ?? 0)
                        ),
                        'bible_version' => (string) ($this->data->bible_version ?? ''),
                    ];
                }

                $bn2 = (int) ($this->data->booknumber2 ?? 0);

                if ($bn2 > 0) {
                    $subformData[] = [
                        'reference_text' => CwmscriptureHelper::formatReference(
                            $bn2,
                            (int) ($this->data->chapter_begin2 ?? 0),
                            (int) ($this->data->verse_begin2 ?? 0),
                            (int) ($this->data->chapter_end2 ?? 0),
                            (int) ($this->data->verse_end2 ?? 0)
                        ),
                        'bible_version' => (string) ($this->data->bible_version2 ?? ''),
                    ];
                }
            }

            $this->data->scriptures = $subformData;

            // Load teachers from junction table for the subform
            $teachers = CwmstudyteacherHelper::getTeachersForStudy((int) $this->data->id);

            if (!empty($teachers)) {
                $teacherSubform = [];

                foreach ($teachers as $t) {
                    $teacherSubform[] = [
                        'teacher_id' => (int) $t->teacher_id,
                    ];
                }

                $this->data->teachers = $teacherSubform;
            } elseif (!empty($this->data->teacher_id) && (int) $this->data->teacher_id > 0) {
                // Fallback: build from legacy flat column for pre-migration records
                $this->data->teachers = [
                    ['teacher_id' => (int) $this->data->teacher_id],
                ];
            } else {
                $this->data->teachers = [];
            }
        } elseif ($this->data) {
            // New record — seed teachers subform from admin default if configured
            $admin   = Cwmparams::getAdmin();
            $params  = new Registry($admin->params);
            $default = (int) $params->get('teacher_id', 0);

            if ($default > 0) {
                $this->data->teachers = [
                    ['teacher_id' => $default],
                ];
            }
        }

        return $this->data;
    }

    /**
     * Overrides the AdminModule save routine to save the topics(tags)
     *
     * @param   array  $data  The form data.
     *
     * @return bool
     *
     * @throws \Exception
     * @since 7.0.1
     */
    public function save($data): bool
    {
        /** @var Registry $params */
        $app    = Factory::getApplication();
        $params = Cwmparams::getAdmin()->params;
        $input  = $app->getInput();
        $image  = HTMLHelper::cleanImageURL((string)$data['image']);

        // Extract subform data before table binding strips it
        $scripturesData = $data['scriptures'] ?? [];
        unset($data['scriptures']);

        $teachersData = $data['teachers'] ?? [];
        unset($data['teachers']);

        $data['image'] = $image->url;
        $this->cleanCache();

        if ($input->get('a_id')) {
            $data['id'] = $input->get('a_id');
        }

        // Correct legacy thumb_ paths submitted from pre-migration records
        $imageBasename = basename($data['image']);
        if (str_starts_with($imageBasename, 'thumb_') && str_contains($data['image'], '/studies/')) {
            $dir            = \dirname(JPATH_ROOT . '/' . $data['image']);
            $strippedName   = pathinfo(substr($imageBasename, 6), PATHINFO_FILENAME);

            foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $ext) {
                if (is_file($dir . '/' . $strippedName . '.' . $ext)) {
                    $data['image'] = \dirname($data['image']) . '/' . $strippedName . '.' . $ext;
                    break;
                }
            }
        }

        // If no image, save without touching thumbnailm (preserve existing thumbnail)
        if (empty($data['image'])) {
            if (!parent::save($data)) {
                return false;
            }

            $this->saveScriptures($scripturesData);
            $this->saveTeachers($teachersData);

            return true;
        }

        // Core component images (media/com_proclaim/images/*) — save path as-is
        if (CwmImageMigration::isCoreImage($data['image'])) {
            if (!parent::save($data)) {
                return false;
            }

            $this->saveScriptures($scripturesData);
            $this->saveTeachers($teachersData);

            return true;
        }

        // Always regenerate thumbnail + WebP on save (Cwmthumbnail::create skips
        // the file copy when the original is already in the destination folder)

        // Store the original image path for processing after save
        $originalImage = $data['image'];
        $studyTitle    = $data['studytitle'] ?? $data['alias'] ?? null;
        $isNew         = empty($data['id']);

        // Validate image before processing
        $absolutePath = JPATH_ROOT . '/' . $originalImage;
        $validation   = Cwmthumbnail::validate($absolutePath);

        if (!$validation['valid']) {
            $app->enqueueMessage(
                Text::sprintf('JBS_STY_IMAGE_VALIDATION_FAILED', $validation['error']),
                'error'
            );
            $data['image']      = '';
            $data['thumbnailm'] = '';

            if (!parent::save($data)) {
                return false;
            }

            $this->saveScriptures($scripturesData);
            $this->saveTeachers($teachersData);

            return true;
        }

        // For new records, save first to get the ID
        if ($isNew) {
            $data['image']      = '';
            $data['thumbnailm'] = '';

            if (!parent::save($data)) {
                return false;
            }

            // Get the new ID from the saved record
            $data['id'] = $this->getState($this->getName() . '.id');
        }

        // Build path with title-ID format
        $alias  = ApplicationHelper::stringURLSafe($studyTitle ?: 'study');
        $path   = 'images/biblestudy/studies/' . $alias . '-' . (int)$data['id'];
        $result = Cwmthumbnail::create(
            $originalImage,
            $path,
            $params->get('thumbnail_study_size', 600),
            $studyTitle
        );

        if ($result === false) {
            $app->enqueueMessage(Text::_('JBS_STY_IMAGE_NOT_FOUND'), 'warning');

            if ($isNew) {
                $this->saveScriptures($scripturesData);
                $this->saveTeachers($teachersData);

                return true;
            }

            if (!parent::save($data)) {
                return false;
            }

            $this->saveScriptures($scripturesData);
            $this->saveTeachers($teachersData);

            return true;
        }

        // Update paths with new locations
        $data['image']      = $result['image'];
        $data['thumbnailm'] = $result['thumbnail'];

        if (!parent::save($data)) {
            return false;
        }

        $this->saveScriptures($scripturesData);
        $this->saveTeachers($teachersData);

        return true;
    }

    /**
     * Process and save scripture references from the subform data.
     *
     * @param   array  $scripturesData  Subform array of ['reference_text' => ..., 'bible_version' => ...]
     *
     * @return  void
     *
     * @since  10.1.0
     */
    private function saveScriptures(array $scripturesData): void
    {
        $studyId = (int) $this->getState($this->getName() . '.id');

        if ($studyId <= 0) {
            return;
        }

        $scriptures = [];

        $ordering = 0;

        foreach ($scripturesData as $entry) {
            $text    = trim($entry['reference_text'] ?? '');
            $version = trim($entry['bible_version'] ?? '');

            if ($text === '') {
                continue;
            }

            $parsed = CwmscriptureHelper::parseReference($text);

            if ($parsed !== null) {
                $parsed->bibleVersion  = $version;
                $parsed->ordering      = $ordering;
                $parsed->referenceText = $text;
            } else {
                // Unparsable — store raw text so user can fix later
                $parsed = new ScriptureReference(
                    booknumber:    0,
                    referenceText: $text,
                    bibleVersion:  $version,
                    ordering:      $ordering,
                );
            }

            $scriptures[] = $parsed;
            $ordering++;
        }

        CwmscriptureHelper::saveScriptures($studyId, $scriptures);
        CwmscriptureHelper::syncLegacyColumns($studyId, $scriptures);
    }

    /**
     * Save teachers from the subform data to the junction table.
     *
     * @param   array  $teachersData  Subform array of ['teacher_id' => int]
     *
     * @return  void
     *
     * @since  10.1.0
     */
    private function saveTeachers(array $teachersData): void
    {
        $studyId = (int) $this->getState($this->getName() . '.id');

        if ($studyId <= 0) {
            return;
        }

        CwmstudyteacherHelper::saveTeachers($studyId, $teachersData);
        CwmstudyteacherHelper::syncLegacyColumn($studyId, $teachersData);
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
     * @throws \Exception
     * @since 7.0
     */
    public function getForm($data = [], $loadData = true): bool|Form
    {
        $app = Factory::getApplication();

        // Get the form.
        $form = $this->loadForm(
            'com_proclaim.cwmmessage',
            'message',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if ($form === null) {
            return false;
        }

        // Object uses for checking edit state permission of article
        $record = new \stdClass();

        // Get ID of the article from input, for frontend, we use a_id while backend uses id
        $messageIdFromInput = $app->isClient('site')
            ? $app->getInput()->getInt('a_id', 0)
            : $app->getInput()->getInt('id', 0);

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
     * Determine whether a record can be edited by the current user.
     *
     * Checks permissions in priority order:
     *   1. `core.edit`     — standard Joomla edit permission
     *   2. `core.edit.own` — user created the message
     *   3. Teacher check   — user is listed as a teacher on this message
     *
     * The teacher check delegates to CwmlocationHelper::userIsTeacher(), which
     * is currently a stub (returns false) until a user_id column is added to
     * #__bsms_teachers (Phase N). Once that column exists the stub will be
     * replaced with a real DB lookup and this method will automatically work.
     *
     * @param   object  $record  The record to check.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    protected function canEdit($record): bool
    {
        $user = Factory::getApplication()->getIdentity();

        // Standard edit permission
        if ($user->authorise('core.edit', 'com_proclaim.message.' . (int) $record->id)) {
            return true;
        }

        // Edit-own permission: user created the message
        if ($user->authorise('core.edit.own', 'com_proclaim.message.' . (int) $record->id)) {
            if ((int) ($record->created_by ?? 0) === (int) $user->id) {
                return true;
            }
        }

        // Teacher permission: user is listed as a teacher on this message
        if (CwmlocationHelper::userIsTeacher((int) $user->id, (int) $record->id)) {
            return true;
        }

        return false;
    }

    /**
     * Saves the manually set order of records.
     *
     * @param   array  $pks    An array of primary key ids.
     * @param   int    $order  +1 or -1
     *
     * @return  mixed
     *
     * @throws \Exception
     * @since    11.1
     */
    public function saveorder($pks = null, $order = null): mixed
    {
        $row        = Factory::getApplication()->bootComponent('com_proclaim')
            ->getMVCFactory()->createTable('Cwmmessage', 'Administrator');
        $conditions = [];

        // Update ordering values
        foreach ($pks as $i => $pk) {
            $row->load((int)$pk);

            // Track categories
            $groupings[] = $row->id;

            if ($row->ordering != $order[$i]) {
                $row->ordering = $order[$i];

                if (!$row->store()) {
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
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
                    $conditions[] = [$row->$key, $condition];
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
     * @throws \Exception
     * @since   2.5
     */
    protected function batchTeacher($value, $pks, $contexts): bool
    {
        // Set the variables
        $user      = Factory::getApplication()->getIdentity();
        /** @var CwmmessageTable $table */
        $table     = $this->getTable();
        $teacherId = (int) $value;

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $table->teacher_id = $teacherId;

                if (!$table->store()) {
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }

                // Update junction table to match
                $teachers = $teacherId > 0
                    ? [['teacher_id' => $teacherId]]
                    : [];
                CwmstudyteacherHelper::saveTeachers((int) $pk, $teachers);
            } else {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
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
     * @throws  \Exception
     * @since   3.0
     */
    public function getTable($name = 'Cwmmessage', $prefix = '', $options = []): Table
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
     * @return  bool  True if successful, false otherwise and internal error is set.
     *
     * @throws \Exception
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
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }
            } else {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
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
     * @throws \Exception
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
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }
            } else {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch-update the location for a group of messages.
     *
     * When the location system is enabled, non-admin users may only assign
     * locations they have visibility over. Passing an empty string clears
     * the location_id (set to NULL).
     *
     * @param   string  $value     The new location ID, or '' to clear.
     * @param   array   $pks       An array of primary key IDs.
     * @param   array   $contexts  An array of item contexts.
     *
     * @return  bool  True if successful.
     *
     * @throws  \RuntimeException  When the user lacks edit or location access.
     * @throws  \Exception
     * @since   10.1.0
     */
    protected function batchLocation(string $value, array $pks, array $contexts): bool
    {
        $user       = Factory::getApplication()->getIdentity();
        $locationId = (int) $value;

        // Validate location access when the system is enabled
        if ($locationId > 0 && CwmlocationHelper::isEnabled() && !$user->authorise('core.admin')) {
            $accessible = CwmlocationHelper::getUserLocations((int) $user->id);

            if (!empty($accessible) && !\in_array($locationId, $accessible, true)) {
                throw new \RuntimeException(Text::_('JBS_BAT_LOCATION_ACCESS_DENIED'));
            }
        }

        /** @var CwmmessageTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $table->location_id = $locationId > 0 ? $locationId : null;

                if (!$table->store()) {
                    throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
                }
            } else {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
            }
        }

        $this->cleanCache();

        return true;
    }

    /**
     * Preprocess the form to import system plugins (needed for Schema.org tab).
     *
     * Joomla's default preprocessForm only imports the 'content' plugin group.
     * System plugins (like plg_system_schemaorg) need explicit import to
     * participate in form preparation for third-party components.
     *
     * @param   Form    $form   The form to preprocess
     * @param   mixed   $data   The form data
     * @param   string  $group  Plugin group (default: 'content')
     *
     * @return  void
     *
     * @since   10.3.0
     */
    protected function preprocessForm(Form $form, $data, $group = 'content'): void
    {
        PluginHelper::importPlugin('system', null, true, $this->getDispatcher());

        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Import system plugins for data preparation.
     *
     * The system schemaorg plugin listens on onContentPrepareData to load
     * saved schema from #__schemaorg. The base method only imports the
     * 'content' group, so system plugins never hear the event.
     *
     * @param   string  $context  The context identifier
     * @param   mixed   &$data   The data to process
     * @param   string  $group   Plugin group (default: 'content')
     *
     * @return  void
     *
     * @since   10.3.0
     */
    protected function preprocessData($context, &$data, $group = 'content'): void
    {
        PluginHelper::importPlugin('system', null, true, $this->getDispatcher());

        parent::preprocessData($context, $data, $group);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed    The default data is an empty array.
     *
     * @throws  \Exception
     * @since   7.0
     */
    protected function loadFormData(): mixed
    {
        $data = Factory::getApplication()->getUserState('com_proclaim.edit.message.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        // Auto-populate Schema.org defaults from message data.
        // Always set defaults — Joomla's system plugin onContentPrepareData will
        // overwrite with saved schema data from #__schemaorg if it exists.
        if (\is_object($data) && !empty($data->id)) {
            $hasSchema = !empty($data->schema['schemaType']) && $data->schema['schemaType'] !== 'None';

            if (!$hasSchema) {
                $data->schema               = $data->schema ?? [];
                $data->schema['schemaType'] = 'Sermon';

                $sermon = ['@type' => 'CreativeWork'];

                if (!empty($data->studytitle)) {
                    $sermon['headline'] = $data->studytitle;
                }

                if (!empty($data->studyintro)) {
                    $sermon['description'] = trim(strip_tags(html_entity_decode($data->studyintro, ENT_QUOTES, 'UTF-8')));
                }

                if (!empty($data->studydate)) {
                    $sermon['datePublished'] = $data->studydate;
                }

                // Look up all teachers from junction table
                if (!empty($data->id)) {
                    try {
                        $db    = Factory::getContainer()->get(DatabaseInterface::class);
                        $query = $db->getQuery(true)
                            ->select($db->quoteName('t.teachername'))
                            ->from($db->quoteName('#__bsms_teachers', 't'))
                            ->innerJoin(
                                $db->quoteName('#__bsms_study_teachers', 'st') . ' ON '
                                . $db->quoteName('st.teacher_id') . ' = ' . $db->quoteName('t.id')
                            )
                            ->where($db->quoteName('st.study_id') . ' = ' . (int) $data->id)
                            ->order($db->quoteName('st.ordering') . ' ASC');
                        $db->setQuery($query);
                        $teacherNames = $db->loadColumn() ?: [];
                    } catch (\Throwable) {
                        $teacherNames = [];
                    }

                    if (!empty($teacherNames)) {
                        $sermon['author'] = [
                            '@type' => 'person',
                            'name'  => implode(', ', $teacherNames),
                        ];
                    }
                }

                if (!empty($data->modified) && $data->modified !== '0000-00-00 00:00:00') {
                    $sermon['dateModified'] = $data->modified;
                }

                if (!empty($data->image)) {
                    $sermon['image'] = $data->image;
                }

                // Add series and topics as custom schema fields
                $customFields = [];

                // Series as isPartOf
                if (!empty($data->series_id) && (int) $data->series_id > 0) {
                    try {
                        $db    = $db ?? Factory::getContainer()->get(DatabaseInterface::class);
                        $query = $db->getQuery(true)
                            ->select($db->quoteName('series_text'))
                            ->from($db->quoteName('#__bsms_series'))
                            ->where($db->quoteName('id') . ' = ' . (int) $data->series_id);
                        $db->setQuery($query);
                        $seriesName = $db->loadResult();

                        if ($seriesName) {
                            $customFields[] = ['genericTitle' => 'isPartOf', 'genericValue' => $seriesName];
                        }
                    } catch (\Throwable) {
                        // DB not available
                    }
                }

                // Topics as about
                if (!empty($data->id)) {
                    try {
                        $db    = $db ?? Factory::getContainer()->get(DatabaseInterface::class);
                        $query = $db->getQuery(true)
                            ->select($db->quoteName('t.topic_text'))
                            ->from($db->quoteName('#__bsms_topics', 't'))
                            ->innerJoin(
                                $db->quoteName('#__bsms_studytopics', 'st') . ' ON '
                                . $db->quoteName('st.topic_id') . ' = ' . $db->quoteName('t.id')
                            )
                            ->where($db->quoteName('st.study_id') . ' = ' . (int) $data->id);
                        $db->setQuery($query);
                        $topics = $db->loadColumn() ?: [];

                        if (!empty($topics)) {
                            // Translate language keys (e.g., JBS_TOP_APOLOGETICS → Apologetics)
                            $translated     = array_map(static fn ($t) => Text::_($t), $topics);
                            $customFields[] = ['genericTitle' => 'about', 'genericValue' => implode(', ', $translated)];
                        }
                    } catch (\Throwable) {
                        // DB not available
                    }
                }

                // Message type as genre
                if (!empty($data->messagetype) && (int) $data->messagetype > 0) {
                    try {
                        $db    = $db ?? Factory::getContainer()->get(DatabaseInterface::class);
                        $query = $db->getQuery(true)
                            ->select($db->quoteName('message_type'))
                            ->from($db->quoteName('#__bsms_message_type'))
                            ->where($db->quoteName('id') . ' = ' . (int) $data->messagetype);
                        $db->setQuery($query);
                        $messageType = $db->loadResult();

                        if ($messageType) {
                            $customFields[] = ['genericTitle' => 'genre', 'genericValue' => Text::_($messageType)];
                        }
                    } catch (\Throwable) {
                        // DB not available
                    }
                }

                // Location as locationCreated
                if (!empty($data->location_id) && (int) $data->location_id > 0) {
                    try {
                        $db    = $db ?? Factory::getContainer()->get(DatabaseInterface::class);
                        $query = $db->getQuery(true)
                            ->select($db->quoteName('location_text'))
                            ->from($db->quoteName('#__bsms_locations'))
                            ->where($db->quoteName('id') . ' = ' . (int) $data->location_id);
                        $db->setQuery($query);
                        $location = $db->loadResult();

                        if ($location) {
                            $customFields[] = ['genericTitle' => 'locationCreated', 'genericValue' => $location];
                        }
                    } catch (\Throwable) {
                        // DB not available
                    }
                }

                // Publisher (org name or site name)
                $orgName = CwmschemaorgHelper::getOrgName();

                if ($orgName !== '') {
                    $customFields[] = ['genericTitle' => 'publisher', 'genericValue' => $orgName];
                }

                if (!empty($customFields)) {
                    $sermon['genericField'] = $customFields;
                }

                $data->schema['Sermon'] = $sermon;
            }
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
     * @throws \Exception
     * @since    1.6
     */
    protected function prepareTable($table): void
    {
        $date = new Date();
        $user = Factory::getApplication()->getIdentity();

        // Set the publishing date to now
        if ($table->published === Workflow::CONDITION_PUBLISHED && (int)$table->publish_up === 0) {
            $table->publish_up = (new Date())->toSql();
        }

        if ($table->published === Workflow::CONDITION_PUBLISHED && (int)$table->publish_down === 0) {
            $table->publish_down = null;
        }

        $table->studytitle = htmlspecialchars_decode($table->studytitle, ENT_QUOTES);
        $table->alias      = ApplicationHelper::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = ApplicationHelper::stringURLSafe($table->studytitle);
        }

        // Always ensure created date is set (handles empty string from form)
        if (empty($table->created) || $table->created === '') {
            $table->created = $date->toSql();
        }

        if (empty($table->id)) {
            // Set the values for a new record
            if (empty($table->created_by)) {
                $table->created_by = $user->id;
            }

            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db    = Factory::getContainer()->get(DatabaseInterface::class);
                $query = $db->getQuery(true)
                    ->select('MAX(' . $db->quoteName('ordering') . ')')
                    ->from($db->quoteName('#__bsms_studies'));
                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        } else {
            // Set the values for existing records
            $table->modified    = $date->toSql();
            $table->modified_by = $user->id;
        }
    }
}
