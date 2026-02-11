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

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Administrator\Table\CwmserieTable;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
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
     * Name of the form
     *
     * @var string
     * @since  4.0.0
     */
    protected $formName = 'serie';
    protected $teacher;
    /**
     * Items data
     *
     * @var  object|bool
     * @since 10.0.0
     */
    private $data;

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
            ($id != 0 && (!$user->authorise('core.edit.state', 'com_proclaim.serie.' . (int)$id)))
            || ($id == 0 && !$user->authorise('core.edit.state', 'com_proclaim'))
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
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select($db->qn('id', 'value') . ', ' . $db->qn('teachername', 'text'))
                ->from($db->qn('#__bsms_teachers'))
                ->where($db->qn('published') . ' = 1');
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
        if ($app->input->get('task') == 'save2copy') {
            list($title, $alias) = $this->generateNewTitle('0', $data['alias'], $data['series_text']);
            $data['series_text'] = $title;
            $data['alias']       = $alias;
        }

        // If no image uploaded or already processed, just save data as usual
        if (empty($data['image']) || str_contains($data['image'], '/series/')) {
            if (empty($data['image'])) {
                $data['series_thumbnail'] = '';
            }

            return parent::save($data);
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
        $extension = $app->input->get('option', '');
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

        // Check for existing article.
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

        // Always ensure created date is set (handles empty string from form)
        if (empty($table->created) || $table->created === '') {
            $table->created = $date->toSql();
        }

        if (empty($table->id)) {
            // Set the values for a new record
            if (empty($table->created_by)) {
                $table->created_by = $user->get('id');
            }

            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db    = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true);
                $query->select('MAX(' . $db->qn('ordering') . ')')->from($db->qn('#__bsms_series'));
                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        } else {
            // Set the values for existing records
            $table->modified    = $date->toSql();
            $table->modified_by = $user->get('id');
        }

        if ($table->ordering == 0) {
            $table->ordering = 1;
            $db              = Factory::getContainer()->get('DatabaseDriver');
            $table->reorder($db->qn('id') . ' = ' . (int)$table->id);
        }
    }

    /**
     * Method to get the data that should be injected into the form.
     *
     * @return  array    The default data is an empty array.
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

        return $data;
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
