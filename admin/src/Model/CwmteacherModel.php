<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmImageMigration;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmschemaorgHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Administrator\Table\CwmteacherTable;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Teacher model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmteacherModel extends AdminModel
{
    /**
     * The type alias for this content type (for example, 'com_content.article').
     *
     * @var      string
     * @since    3.2
     */
    public $typeAlias = 'com_proclaim.teacher';

    /**
     * Batch commands mapping.
     *
     * @var   array
     * @since 10.3.0
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'landing_show'  => 'batchLandingShow',
        'list_show'     => 'batchListShow',
        'move_position' => 'batchMovePosition',
    ];

    /**
     * Controller Prefix
     *
     * @var        string    The prefix to use with controller messages.
     * @since    1.6
     */
    protected $text_prefix = 'com_proclaim';
    /**
     * Name of the form
     *
     * @var string
     * @since  4.0.0
     */
    protected string $formName = 'teacher';

    /**
     * @var mixed
     * @since 10.0.0
     */
    private mixed $data;

    /**
     * Get the form data
     *
     * @param   array  $data      Data for the form.
     * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @throws \Exception
     * @since 7.0
     */
    public function getForm($data = [], $loadData = true): mixed
    {
        if (empty($data)) {
            $this->getItem();
        }

        // Get the form.
        $form = $this->loadForm(
            'com_proclaim.' . $this->formName,
            $this->formName,
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if ($form === null) {
            return false;
        }

        $jinput = Factory::getApplication()->getInput();

        // The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
        if ($jinput->get('a_id')) {
            $id = $jinput->get('a_id', 0);
        } else {
            // The back end uses id so we use that the rest of the time and set it to 0 by default.
            $id = $jinput->get('id', 0);
        }

        $user = Factory::getApplication()->getIdentity();

        // Check for existing article.
        // Modify the form based on Edit State access controls.
        if (
            ($id !== 0 && (!$user->authorise('core.edit.state', 'com_proclaim.teacher.' . (int) $id)))
            || ($id === 0 && !$user->authorise('core.edit.state', 'com_proclaim'))
        ) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get a single record.
     *
     * @param   int  $pk  The id of the primary key.
     *
     * @return    mixed    Object on success, false on failure.
     *
     * @throws \Exception
     * @since    1.7.0
     */
    public function getItem($pk = null): mixed
    {
        $jinput = Factory::getApplication()->getInput();

        // The front end calls this model and uses a_id to avoid id clashes, so we need to check for that first.
        if ($jinput->get('a_id')) {
            $pk = $jinput->get('a_id', 0);
        } else {
            // The back end uses id so we use that the rest of the time and set it to 0 by default.
            $pk = $jinput->get('id', 0);
        }

        if (!empty($this->data)) {
            return $this->data;
        }

        $this->data = parent::getItem($pk);

        // Auto-populate social_links from legacy columns if not yet migrated
        if ($this->data && !empty($this->data->id) && empty($this->data->social_links)) {
            $migrated = [];

            if (!empty($this->data->facebooklink)) {
                $migrated[] = ['platform' => 'facebook', 'url' => $this->data->facebooklink, 'label' => ''];
            }

            if (!empty($this->data->twitterlink)) {
                $migrated[] = ['platform' => 'x-twitter', 'url' => $this->data->twitterlink, 'label' => ''];
            }

            if (!empty($this->data->bloglink)) {
                $migrated[] = ['platform' => 'blog', 'url' => $this->data->bloglink, 'label' => ''];
            }

            for ($i = 1; $i <= 3; $i++) {
                $linkField  = 'link' . $i;
                $labelField = 'linklabel' . $i;

                if (!empty($this->data->$linkField)) {
                    $migrated[] = [
                        'platform' => 'other',
                        'url'      => $this->data->$linkField,
                        'label'    => $this->data->$labelField ?? '',
                    ];
                }
            }

            if (!empty($migrated)) {
                $this->data->social_links = json_encode($migrated);
            }
        }

        return $this->data;
    }

    /**
     * Method to validate the form data.
     *
     * @param   Form    $form   The form to validate against.
     * @param   array   $data   The data to validate.
     * @param   string  $group  The name of the field group to validate.
     *
     * @return  array|bool  Array of filtered data if valid, false otherwise.
     *
     * @throws \Exception
     * @see     JFilterInput
     * @since   3.7.0
     * @see     \Joomla\CMS\Form\FormRule
     */
    public function validate($form, $data, $group = null): bool|array
    {
        if (!$this->getCurrentUser()->authorise('core.admin', 'com_proclaim') && isset($data['rules'])) {
            unset($data['rules']);
        }

        return parent::validate($form, $data, $group);
    }

    /**
     * Method to test whether a record can have its state changed.
     *
     * @param   object  $record  A record object.
     *
     * @return  bool  True if allowed to change the state of the record. Defaults to the permission for the component.
     *
     * @throws \Exception
     * @since   1.6
     */
    protected function canEditState($record): bool
    {
        $db   = Factory::getContainer()->get(DatabaseInterface::class);
        $text = '';

        if (!empty($record) && $this->getState('task') === 'trash') {
            $query = $db->getQuery(true);
            $query->select($db->quoteName(['s.id', 's.studytitle']))
                ->from($db->quoteName('#__bsms_studies', 's'))
                ->innerJoin(
                    $db->quoteName('#__bsms_study_teachers', 'st') . ' ON '
                    . $db->quoteName('st.study_id') . ' = ' . $db->quoteName('s.id')
                )
                ->where($db->quoteName('st.teacher_id') . ' = ' . (int) $record->id)
                ->where($db->quoteName('s.published') . ' != ' . $db->q('-2'));
            $db->setQuery($query, 10);
            $studies = $db->loadObjectList();

            if ($studies) {
                foreach ($studies as $studie) {
                    $text .= ' ' . $studie->id . '-"' . $studie->studytitle . '",';
                }

                Factory::getApplication()->enqueueMessage(Text::_('JBS_TCH_CAN_NOT_DELETE') . $text, 'warning');

                return false;
            }
        }

        return Factory::getApplication()->getIdentity()->authorise('core.edit.state', $this->option);
    }

    /**
     * Saves data creating image thumbnails
     *
     * @param   array  $data  Data
     *
     * @return bool
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function save($data): bool
    {
        $filter = InputFilter::getInstance();

        if (isset($data['metadata']['author'])) {
            $data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');
        }

        if (isset($data['created_by_alias'])) {
            $data['created_by_alias'] = $filter->clean($data['created_by_alias'], 'TRIM');
        }

        // Sync social_links subform → legacy columns for frontend backward compatibility
        if (!empty($data['social_links']) && \is_array($data['social_links'])) {
            $data['social_links'] = json_encode(array_values($data['social_links']));

            try {
                $decoded = json_decode($data['social_links'], true, 512, JSON_THROW_ON_ERROR) ?: [];
            } catch (\JsonException) {
                $decoded = [];
            }

            // Map platform → legacy column (first match wins)
            $legacyMap = [
                'facebook'  => 'facebooklink',
                'x-twitter' => 'twitterlink',
                'blog'      => 'bloglink',
            ];

            foreach ($legacyMap as $platform => $column) {
                foreach ($decoded as $link) {
                    if (($link['platform'] ?? '') === $platform && !empty($link['url'])) {
                        $data[$column] = $link['url'];
                        break;
                    }
                }
            }
        } elseif (isset($data['social_links']) && \is_string($data['social_links'])) {
            // Already JSON string — leave as-is
        } else {
            $data['social_links'] = '';
        }

        /** @var Registry $params */
        $params        = Cwmparams::getAdmin()->params;
        $app           = Factory::getApplication();
        $image         = HTMLHelper::cleanImageURL($data['image']);
        $data['image'] = urldecode($image->url);

        // Set contact to be an Int to work with Database
        $data['contact'] = (int) $data['contact'];

        // If no image, clear thumbnail fields and save
        if (empty($data['image'])) {
            $data['teacher_image']     = '';
            $data['teacher_thumbnail'] = '';

            return parent::save($data);
        }

        // Core component images — save path as-is without thumbnail processing
        if (CwmImageMigration::isCoreImage($data['image'])) {
            return parent::save($data);
        }

        // Correct legacy thumb_ paths
        $imageBasename = basename($data['image']);
        if (str_starts_with($imageBasename, 'thumb_') && str_contains($data['image'], '/teachers/')) {
            $dir          = \dirname(JPATH_ROOT . '/' . $data['image']);
            $strippedName = pathinfo(substr($imageBasename, 6), PATHINFO_FILENAME);

            foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $ext) {
                if (is_file($dir . '/' . $strippedName . '.' . $ext)) {
                    $data['image'] = \dirname($data['image']) . '/' . $strippedName . '.' . $ext;
                    break;
                }
            }
        }

        // Store the original image path for processing after save
        $originalImage = $data['image'];
        $teacherName   = $data['teachername'] ?? $data['alias'] ?? null;
        $isNew         = empty($data['id']);

        // Validate image before processing
        $absolutePath = JPATH_ROOT . '/' . $originalImage;
        $validation   = Cwmthumbnail::validate($absolutePath);

        if (!$validation['valid']) {
            $app->enqueueMessage(
                Text::sprintf('JBS_STY_IMAGE_VALIDATION_FAILED', $validation['error']),
                'error'
            );
            $data['image']             = '';
            $data['teacher_image']     = '';
            $data['teacher_thumbnail'] = '';

            return parent::save($data);
        }

        if (!empty($validation['warning']) && $app->isClient('administrator')) {
            $app->enqueueMessage($validation['warning'], 'warning');
        }

        // For new records, save first to get the ID
        if ($isNew) {
            $data['image']             = '';
            $data['teacher_image']     = '';
            $data['teacher_thumbnail'] = '';

            if (!parent::save($data)) {
                return false;
            }

            // Get the new ID from the saved record
            $data['id'] = $this->getState($this->getName() . '.id');
        }

        // Build path with title-ID format
        $alias = ApplicationHelper::stringURLSafe($teacherName ?: 'teacher');
        $path  = 'images/biblestudy/teachers/' . $alias . '-' . (int)$data['id'];

        $result = Cwmthumbnail::create(
            $originalImage,
            $path,
            $params->get('thumbnail_teacher_size', 300),
            $teacherName
        );

        if ($result === false) {
            $app->enqueueMessage(Text::_('JBS_STY_IMAGE_NOT_FOUND'), 'warning');

            return $isNew || parent::save($data);
        }

        // Update paths with new locations
        $data['image']             = $result['image'];
        $data['teacher_image']     = $result['image'];
        $data['teacher_thumbnail'] = $result['thumbnail'];

        return parent::save($data);
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  bool  True if allowed to delete the record. Defaults to the permission for the component.
     *
     * @throws \Exception
     * @since   1.6
     */
    protected function canDelete($record): bool
    {
        $app        = Factory::getApplication();
        $db         = Factory::getContainer()->get(DatabaseInterface::class);
        $user       = $app->getIdentity();
        $canDoState = $user->authorise('core.edit.state', $this->option);
        $text       = '';

        // Iterate the items to delete each one.
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['s.id', 's.studytitle']))
            ->from($db->quoteName('#__bsms_studies', 's'))
            ->innerJoin(
                $db->quoteName('#__bsms_study_teachers', 'st') . ' ON '
                . $db->quoteName('st.study_id') . ' = ' . $db->quoteName('s.id')
            )
            ->where($db->quoteName('st.teacher_id') . ' = ' . (int) $record->id);
        $db->setQuery($query);
        $studies = $db->loadObjectList();

        if (!$studies && $canDoState) {
            return true;
        }

        if ($record->published == '-2' || $record->published == '0') {
            foreach ($studies as $studie) {
                $text .= ' ' . $studie->id . '-"' . $studie->studytitle . '",';
            }

            $app->enqueueMessage(Text::_('JBS_TCH_CAN_NOT_DELETE') . $text);
        }

        return $this->getCurrentUser()->authorise('core.delete', $this->option);
    }

    /**
     * Preprocess the form to import system plugins (needed for Schema.org tab).
     *
     * @param   Form    $form   The form to preprocess
     * @param   mixed   $data   The form data
     * @param   string  $group  Plugin group
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
     * @inheritDoc
     * @since 10.3.0
     */
    protected function preprocessData($context, &$data, $group = 'content'): void
    {
        PluginHelper::importPlugin('system', null, true, $this->getDispatcher());

        parent::preprocessData($context, $data, $group);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     *
     * @throws \Exception
     * @since   7.0
     */
    protected function loadFormData(): mixed
    {
        // Check the session for previously entered form data.
        $session = Factory::getApplication()->getUserState('com_proclaim.edit.teacher.data', []);
        $data    = empty($session) ? $this->data : $session;

        // Auto-populate Schema.org defaults from teacher data.
        // Always set defaults — Joomla's system plugin onContentPrepareData will
        // overwrite with saved schema data from #__schemaorg if it exists.
        if (\is_object($data) && !empty($data->id)) {
            $hasSchema = !empty($data->schema['schemaType']) && $data->schema['schemaType'] !== 'None';

            if (!$hasSchema) {
                $data->schema               = $data->schema ?? [];
                $data->schema['schemaType'] = 'Teacher';

                $teacher = ['@type' => 'Person'];

                if (!empty($data->teachername)) {
                    $teacher['name'] = $data->teachername;
                }

                if (!empty($data->title)) {
                    $teacher['jobTitle'] = $data->title;
                }

                if (!empty($data->short)) {
                    $teacher['description'] = trim(strip_tags(html_entity_decode($data->short, ENT_QUOTES, 'UTF-8')));
                } elseif (!empty($data->information)) {
                    $teacher['description'] = trim(strip_tags(html_entity_decode($data->information, ENT_QUOTES, 'UTF-8')));
                }

                if (!empty($data->teacher_image)) {
                    $teacher['image'] = $data->teacher_image;
                } elseif (!empty($data->teacher_thumbnail)) {
                    $teacher['image'] = $data->teacher_thumbnail;
                }

                // Website as url
                if (!empty($data->website)) {
                    $teacher['url'] = $data->website;
                }

                // Collect social links for sameAs
                $sameAs = [];

                // New social_links JSON field
                if (!empty($data->social_links) && \is_string($data->social_links)) {
                    try {
                        $links = json_decode($data->social_links, true, 512, JSON_THROW_ON_ERROR);

                        foreach ($links as $link) {
                            if (!empty($link['url']) && filter_var($link['url'], FILTER_VALIDATE_URL)) {
                                $sameAs[] = $link['url'];
                            }
                        }
                    } catch (\Throwable) {
                        // Malformed JSON — skip
                    }
                }

                // Legacy link fields as fallback
                if (empty($sameAs)) {
                    foreach (['facebooklink', 'twitterlink', 'bloglink', 'link1', 'link2', 'link3'] as $field) {
                        if (!empty($data->$field) && filter_var($data->$field, FILTER_VALIDATE_URL)) {
                            $sameAs[] = $data->$field;
                        }
                    }
                }

                if (!empty($sameAs)) {
                    // Structure as subform expects: array of {value: url}
                    $teacher['sameAs'] = array_map(
                        static fn ($url) => ['value' => $url],
                        $sameAs
                    );
                }

                // worksFor: teacher org_name → admin setting → site name
                $orgName = !empty($data->org_name) ? $data->org_name : CwmschemaorgHelper::getOrgName();

                if ($orgName !== '') {
                    $teacher['worksFor'] = [
                        '@type' => 'Organization',
                        'name'  => $orgName,
                    ];
                }

                $data->schema['Teacher'] = $teacher;
            }
        }

        $this->preprocessData('com_proclaim.teacher', $data);

        return $data;
    }

    /**
     * Prepare and sanitize the table prior to saving.
     *
     * @param   CwmteacherTable  $table  A reference to a JTable object.
     *
     * @return  void
     *
     * @throws \Exception
     * @since    1.6
     */
    protected function prepareTable($table): void
    {
        $date = new Date();
        $user = Factory::getApplication()->getIdentity();

        $table->teachername = htmlspecialchars_decode($table->teachername, ENT_QUOTES);
        $table->alias       = ApplicationHelper::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = ApplicationHelper::stringURLSafe($table->teachername);
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
                $query = $db->getQuery(true);
                $query->select('MAX(' . $db->quoteName('ordering') . ')')->from($db->quoteName('#__bsms_teachers'));
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

    /**
     * Get messages (sermons) by this teacher.
     *
     * @return  array  List of message objects
     *
     * @since   10.1.0
     */
    public function getMessages(): array
    {
        $item = $this->getItem();

        if (!$item || empty($item->id)) {
            return [];
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select([
            $db->quoteName('study.id'),
            $db->quoteName('study.studytitle'),
            $db->quoteName('study.studydate'),
            $db->quoteName('study.published'),
            $db->quoteName('study.access'),
        ]);
        $query->from($db->quoteName('#__bsms_studies', 'study'));

        // Join over Series
        $query->select($db->quoteName('series.series_text'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_series', 'series') . ' ON ' . $db->quoteName('series.id') . ' = ' . $db->quoteName('study.series_id')
        );

        // Join over Location
        $query->select($db->quoteName('loc.location_text'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_locations', 'loc') . ' ON ' . $db->quoteName('loc.id') . ' = ' . $db->quoteName('study.location_id')
        );

        // Filter by teacher via junction table OR legacy teacher_id
        $query->where(
            '(' . $db->quoteName('study.id') . ' IN ('
            . $db->getQuery(true)
                ->select($db->quoteName('st.study_id'))
                ->from($db->quoteName('#__bsms_study_teachers', 'st'))
                ->where($db->quoteName('st.teacher_id') . ' = ' . (int) $item->id)
            . ') OR ' . $db->quoteName('study.teacher_id') . ' = ' . (int) $item->id . ')'
        );

        // Restrict non-admin users to their authorised view levels
        $user = $this->getCurrentUser();

        if (!$user->authorise('core.admin')) {
            $query->whereIn($db->quoteName('study.access'), $user->getAuthorisedViewLevels());
        }

        $query->order($db->quoteName('study.studydate') . ' DESC');

        $db->setQuery($query, 0, 20);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Custom clean the cache of com_proclaim and proclaim modules
     *
     * @param   string  $group      The cache group
     * @param   int     $client_id  The ID of the client
     *
     * @return  void
     *
     * @since    1.6
     */
    protected function cleanCache($group = null, int $client_id = 0): void
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
    }

    /**
     * Batch set landing_show for a group of teachers.
     *
     * @param   int    $value     The landing_show value (0/1/2)
     * @param   array  $pks       An array of row IDs
     * @param   array  $contexts  An array of item contexts
     *
     * @return  bool
     *
     * @since   10.3.0
     */
    protected function batchLandingShow(int $value, array $pks, array $contexts): bool
    {
        $user  = Factory::getApplication()->getIdentity();
        /** @var CwmteacherTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $table->landing_show = $value;

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
     * Batch set list_show for a group of teachers.
     *
     * @param   int    $value     The list_show value (0/1)
     * @param   array  $pks       An array of row IDs
     * @param   array  $contexts  An array of item contexts
     *
     * @return  bool
     *
     * @since   10.3.0
     */
    protected function batchListShow(int $value, array $pks, array $contexts): bool
    {
        $user  = Factory::getApplication()->getIdentity();
        /** @var CwmteacherTable $table */
        $table = $this->getTable();

        foreach ($pks as $pk) {
            if ($user->authorise('core.edit', $contexts[$pk])) {
                $table->reset();
                $table->load($pk);
                $table->list_show = $value;

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
     * Batch move selected teachers to a specific ordering position.
     *
     * Inserts the selected teachers (sorted alphabetically) starting at the
     * given position and shifts all other teachers down to make room.
     *
     * @param   int    $value     The target ordering position (1-based)
     * @param   array  $pks       An array of row IDs
     * @param   array  $contexts  An array of item contexts
     *
     * @return  bool
     *
     * @since   10.3.0
     */
    protected function batchMovePosition(int $value, array $pks, array $contexts): bool
    {
        $user = Factory::getApplication()->getIdentity();

        // Verify permissions for all selected items
        foreach ($pks as $pk) {
            if (!$user->authorise('core.edit', $contexts[$pk])) {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
            }
        }

        $db       = Factory::getContainer()->get(DatabaseInterface::class);
        $position = max(1, $value);

        // Get all teacher IDs in current ordering, excluding the selected ones.
        // Tiebreaker on teachername prevents random shuffling when multiple
        // teachers share the same ordering value (e.g. all at 0).
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_teachers'))
            ->whereNotIn($db->quoteName('id'), $pks)
            ->order($db->quoteName('ordering') . ' ASC, ' . $db->quoteName('teachername') . ' ASC');
        $db->setQuery($query);
        $otherIds = $db->loadColumn();

        // Get the selected teacher IDs sorted alphabetically
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_teachers'))
            ->whereIn($db->quoteName('id'), $pks)
            ->order($db->quoteName('teachername') . ' ASC');
        $db->setQuery($query);
        $selectedIds = $db->loadColumn();

        // Build new ordering: insert selected at target position
        $insertAt = min($position - 1, \count($otherIds));
        $newOrder = array_merge(
            \array_slice($otherIds, 0, $insertAt),
            $selectedIds,
            \array_slice($otherIds, $insertAt)
        );

        // Update all ordering values
        foreach ($newOrder as $index => $id) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_teachers'))
                ->set($db->quoteName('ordering') . ' = ' . ($index + 1))
                ->where($db->quoteName('id') . ' = ' . (int) $id);
            $db->setQuery($query);
            $db->execute();
        }

        $this->cleanCache();

        return true;
    }
}
