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
use CWM\Component\Proclaim\Administrator\Helper\CwmschemaorgHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Administrator\Table\CwmserieTable;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Serie administrator model
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmserieModel extends AdminModel
{
    /**
     * The type alias for this content type (for example, 'com_content.article').
     *
     * @var      string
     * @since    3.2
     */
    public $typeAlias = 'com_proclaim.cwmserie';
    /**
     * Controller Prefix
     *
     * @var        string    The prefix to use with controller messages.
     * @since    1.6
     */
    protected $text_prefix = 'com_proclaim';
    /**
     * Allowed batch commands
     *
     * @var array
     * @since 10.1.0
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'language_id'   => 'batchLanguage',
        'location'      => 'batchLocation',
    ];

    /**
     * Name of the form
     *
     * @var string
     * @since  4.0.0
     */
    protected string $formName = 'serie';
    protected mixed $teacher;
    /**
     * Items data
     *
     * @var  object|bool
     * @since 10.0.0
     */
    private mixed $data;

    /**
     * Abstract method for getting the form from the model.
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
        $form = $this->loadForm('com_proclaim.serie', 'serie', ['control' => 'jform', 'load_data' => $loadData]);

        if ($form === null) {
            return false;
        }

        $input = Factory::getApplication()->getInput();

        // The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
        if ($input->get('a_id')) {
            $id = $input->get('a_id', 0);
        } else {
            // The back end uses id so we use that the rest of the time and set it to 0 by default.
            $id = $input->get('id', 0);
        }

        $user = Factory::getApplication()->getIdentity();

        // Check for existing article.
        // Modify the form based on Edit State access controls.
        if (
            ($id !== 0 && (!$user->authorise('core.edit.state', 'com_proclaim.serie.' . (int)$id)))
            || ($id === 0 && !$user->authorise('core.edit.state', 'com_proclaim'))
        ) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.
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
     * @since 7.0
     */
    public function getItem($pk = null): mixed
    {
        $item = parent::getItem($pk);

        if ($item) {
            $item->admin = Cwmparams::getAdmin();
        }

        return $item;
    }

    /**
     * Get Teacher data
     *
     * @return object
     *
     * @since 7.0
     */
    public function getTeacher(): mixed
    {
        if (empty($this->teacher)) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('id', 'value') . ', ' . $db->quoteName('teachername', 'text'))
                ->from($db->quoteName('#__bsms_teachers'))
                ->where($db->quoteName('published') . ' = 1');
            $this->teacher = $this->_getList($query);
        }

        return $this->teacher;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return    bool    True on success.
     *
     * @throws \Exception
     * @since    1.6
     */
    public function save($data): bool
    {
        /** @var Registry $params */
        $params        = Cwmparams::getAdmin()->params;
        $app           = Factory::getApplication();
        $image         = HTMLHelper::cleanImageURL($data['image']);
        $data['image'] = $image->url;

        // Alter the title for save as copy
        if ($app->getInput()->get('task') === 'save2copy') {
            list($title, $alias) = $this->generateNewTitle('0', $data['alias'], $data['series_text']);
            $data['series_text'] = $title;
            $data['alias']       = $alias;
        }

        // If no image, clear thumbnail and save
        if (empty($data['image'])) {
            $data['series_thumbnail'] = '';

            return parent::save($data);
        }

        // Core component images — save path as-is without thumbnail processing
        if (CwmImageMigration::isCoreImage($data['image'])) {
            return parent::save($data);
        }

        // Correct legacy thumb_ paths
        $imageBasename = basename($data['image']);
        if (str_starts_with($imageBasename, 'thumb_') && str_contains($data['image'], '/series/')) {
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
        $seriesTitle   = $data['series_text'] ?? $data['alias'] ?? null;
        $isNew         = empty($data['id']);

        // Validate image before processing
        $absolutePath = JPATH_ROOT . '/' . $originalImage;
        $validation   = Cwmthumbnail::validate($absolutePath);

        if (!$validation['valid']) {
            $app->enqueueMessage(
                Text::sprintf('JBS_STY_IMAGE_VALIDATION_FAILED', $validation['error']),
                'error'
            );
            $data['image']            = '';
            $data['series_thumbnail'] = '';

            return parent::save($data);
        }

        // For new records, save first to get the ID
        if ($isNew) {
            $data['image']            = '';
            $data['series_thumbnail'] = '';

            if (!parent::save($data)) {
                return false;
            }

            // Get the new ID from the saved record
            $data['id'] = $this->getState($this->getName() . '.id');
        }

        // Build path with title-ID format
        $alias = ApplicationHelper::stringURLSafe($seriesTitle ?: 'series');
        $path  = 'images/biblestudy/series/' . $alias . '-' . (int)$data['id'];

        $result = Cwmthumbnail::create(
            $originalImage,
            $path,
            $params->get('thumbnail_series_size', 300),
            $seriesTitle
        );

        if ($result === false) {
            $app->enqueueMessage(Text::_('JBS_STY_IMAGE_NOT_FOUND'), 'warning');

            return $isNew || parent::save($data);
        }

        // Update paths with new locations
        $data['image']            = $result['image'];
        $data['series_thumbnail'] = $result['thumbnail'];

        return parent::save($data);
    }

    /**
     * Batch copy items to a new category or the current one.
     *
     * @param   int    $value     The new category.
     * @param   array  $pks       An array of row IDs.
     * @param   array  $contexts  An array of item contexts.
     *
     * @return  array|false  IDs on success, false on failure.
     *
     * @throws \Exception
     * @since    11.1
     */
    protected function batchCopy($value, $pks, $contexts): array|bool
    {
        $app = Factory::getApplication();
        /** @type CwmserieTable $table */
        $table  = $this->getTable();
        $i      = 0;
        $newIds = [];

        // Check that the user has create permission for the component
        $extension = $app->getInput()->get('option', '');
        $user      = Factory::getApplication()->getIdentity();

        if (!$user->authorise('core.create', $extension)) {
            $app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'), 'error');

            return false;
        }

        // Parent exists, so we let's proceed
        while (!empty($pks)) {
            // Pop the first ID off the stack
            $pk = array_shift($pks);

            $table->reset();

            // Check that the row actually exists
            if (!$table->load($pk)) {
                $app->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk), 'warning');
                continue;
            }

            // Alter the title & alias
            $data               = $this->generateNewTitle('', $table->alias, $table->series_text);
            $table->series_text = $data['0'];
            $table->alias       = $data['1'];

            // Reset the ID because we are making a copy
            $table->id = 0;

            // Check the row.
            if (!$table->check()) {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
            }

            // Store the row.
            if (!$table->store()) {
                throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));
            }

            // Get the new item ID
            $newId = $table->get('id');

            // Add the new ID to the array
            $newIds[$i] = $newId;
            $i++;
        }

        // Clean the cache
        $this->cleanCache();

        return $newIds;
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
    public function getTable($name = 'Cwmserie', $prefix = '', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Custom cleans the cache of the com_proclaim and proclaim modules
     *
     * @param   string  $group      The cache group
     * @param   int     $client_id  The ID of the client
     *
     * @return  void
     *
     * @since    1.6
     */
    protected function cleanCache($group = null, $client_id = 0): void
    {
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');
        parent::cleanCache('mod_proclaim_podcast');
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  bool  True if allowed to delete the record. Defaults to the permission for the component.
     *
     * @throws \Exception
     * @since   12.2
     */
    protected function canDelete($record): bool
    {
        if (!empty($record->id)) {
            if ($record->published != -2) {
                return false;
            }

            return Factory::getApplication()->getIdentity()->authorise(
                'core.delete',
                'com_proclaim.serie.' . (int)$record->id
            );
        }

        return false;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  bool  True if allowed to change the state of the record. Defaults to the permission for the component.
     *
     * @throws \Exception
     * @since    1.6
     */
    protected function canEditState($record): bool
    {
        $user = Factory::getApplication()->getIdentity();

        // Check for existing series.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', 'com_proclaim.serie.' . (int)$record->id);
        }

        // Default to component settings if series known.
        return parent::canEditState($record);
    }

    /**
     * Prepare and sanitize the table data before saving.
     *
     * @param   CwmserieTable  $table  A reference to a JTable object.
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

        $table->series_text = htmlspecialchars_decode($table->series_text, ENT_QUOTES);
        $table->alias       = ApplicationHelper::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = ApplicationHelper::stringURLSafe($table->series_text);
        }

        // Always ensure the created date is set (handles empty string from form)
        if (empty($table->created)) {
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
                $query->select('MAX(' . $db->quoteName('ordering') . ')')->from($db->quoteName('#__bsms_series'));
                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        } else {
            // Set the values for existing records
            $table->modified    = $date->toSql();
            $table->modified_by = $user->id;
        }

        if ($table->ordering === 0) {
            $table->ordering = 1;
            $db              = Factory::getContainer()->get(DatabaseInterface::class);
            $table->reorder($db->quoteName('id') . ' = ' . (int)$table->id);
        }
    }

    /**
     * Batch set location for a list of series.
     *
     * @param   string  $value     The location ID (or 0/empty to clear).
     * @param   array   $pks       An array of row IDs.
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

        /** @var CwmserieTable $table */
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
     * Method to get the data that should be injected into the form.
     *
     * @return  mixed    The default data is an empty array.
     *
     * @throws \Exception
     * @since   7.0
     */
    protected function loadFormData(): mixed
    {
        // Check the session for previously entered form data.
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_proclaim.edit.serie.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        // Auto-populate Schema.org defaults from series data if not already saved
        if (\is_object($data) && !empty($data->id)
            && !CwmschemaorgHelper::hasJoomlaSchema((int) $data->id, 'com_proclaim.serie')) {
                $data->schema               = $data->schema ?? [];
                $data->schema['schemaType'] = 'Series';

                $series = ['@type' => 'CreativeWorkSeries'];

                if (!empty($data->series_text)) {
                    $series['name'] = $data->series_text;
                }

                if (!empty($data->description)) {
                    $series['description'] = trim(strip_tags(html_entity_decode($data->description, ENT_QUOTES, 'UTF-8')));
                }

                if (!empty($data->series_thumbnail)) {
                    $series['image'] = $data->series_thumbnail;
                }

                $data->schema['Series'] = $series;
        }

        return $data;
    }

    /**
     * Get messages belonging to this series.
     *
     * @return  array  List of message objects with title, date, teacher, location, published, id.
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

        // Join over Teachers (via junction table, primary teacher; falls back to legacy teacher_id)
        $query->select($db->quoteName('teacher.teachername'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_study_teachers', 'stj') . ' ON ' . $db->quoteName('stj.study_id') . ' = ' . $db->quoteName('study.id')
            . ' AND ' . $db->quoteName('stj.ordering') . ' = 0'
        );
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_teachers', 'teacher') . ' ON ' . $db->quoteName('teacher.id')
            . ' = COALESCE(' . $db->quoteName('stj.teacher_id') . ', ' . $db->quoteName('study.teacher_id') . ')'
        );

        // Join over Location
        $query->select($db->quoteName('loc.location_text'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_locations', 'loc') . ' ON ' . $db->quoteName('loc.id') . ' = ' . $db->quoteName('study.location_id')
        );

        $query->where($db->quoteName('study.series_id') . ' = ' . (int) $item->id);

        // Restrict non-admin users to their authorised view levels
        $user = $this->getCurrentUser();

        if (!$user->authorise('core.admin')) {
            $query->whereIn($db->quoteName('study.access'), $user->getAuthorisedViewLevels());
        }

        $query->order($db->quoteName('study.studydate') . ' DESC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   Table  $table  A JTable object.
     *
     * @return  array  An array of conditions to add to ordering queries.
     *
     * @since    1.6
     */
    protected function getReorderConditions($table): array
    {
        return [];
    }
}
