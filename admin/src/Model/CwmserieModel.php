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
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Administrator\Table\CwmserieTable;
use Joomla\CMS\Application\ApplicationHelper;
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
     * @var  object|boolean
     * @since 10.0.0
     */
    private $data;

    /**
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @since 7.0
     */
    public function getForm($data = [], $loadData = true)
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

        $user = Factory::getApplication()->getSession()->get('user');

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
    public function getItem($pk = null)
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
    public function getTeacher()
    {
        if (empty($this->teacher)) {
            $query         = 'SELECT id AS value, teachername AS text'
                . ' FROM #__bsms_teachers'
                . ' WHERE published = 1';
            $this->teacher = $this->_getList($query);
        }

        return $this->teacher;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return    boolean    True on success.
     *
     * @since    1.6
     */
    public function save($data)
    {
        /** @var Registry $params */
        $params        = Cwmparams::getAdmin()->params;
        $app           = Factory::getApplication();
        $path          = 'images/biblestudy/series/' . $data['id'];
        $prefix        = 'thumb_';
        $image         = HTMLHelper::cleanImageURL($data['image']);
        $data['image'] = $image->url;
        // Alter the title for save as copy
        if ($app->input->get('task') == 'save2copy') {
            list($title, $alias) = $this->generateNewTitle('0', $data['alias'], $data['title']);
            $data['title']       = $title;
            $data['alias']       = $alias;
        }

        // If no image uploaded, just save data as usual
        if (empty($data['image']) || strpos($data['image'], $prefix) !== false) {
            if (empty($data['image'])) {
                // Modify model data if no image is set.
                $data['series_thumbnail'] = "";
            } elseif (!str_starts_with(basename($data['image']), $prefix)) {
                // Modify model data
                $data['series_thumbnail'] = $path . '/thumb_' . basename($data['image']);
            } elseif (substr_count(basename($data['image']), $prefix) > 1) {
                $x = substr_count(basename($data['image']), $prefix);

                while ($x > 1) {
                    if (substr(basename($data['image']), 0, strlen($prefix)) == $prefix) {
                        $str                      = substr(basename($data['image']), strlen($prefix));
                        $data['series_thumbnail'] = $path . '/' . $str;
                        $data['image']            = $path . '/' . $str;
                    }

                    $x--;
                }
            }

            return parent::save($data);
        }

        Cwmthumbnail::create($data['image'], $path, $params->get('thumbnail_series_size', 100));

        // Modify model data
        $data['series_thumbnail'] = $path . '/thumb_' . basename($data['image']);

        return parent::save($data);
    }

    /**
     * Method to check out a row for editing.
     *
     * @param   int  $pk  The numeric ID of the primary key.
     *
     * @return  bool  False on failure or error, true otherwise.
     *
     * @since   11.1
     */
    public function checkout($pk = null)
    {
        return true;
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
    protected function batchCopy($value, $pks, $contexts)
    {
        $app = Factory::getApplication();
        /** @type CwmserieTable $table */
        $table  = $this->getTable();
        $i      = 0;
        $newIds = [];

        // Check that the user has create permission for the component
        $extension = $app->input->get('option', '');
        $user      = $user = Factory::getApplication()->getSession()->get('user');

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
                if ($error = $table->getError()) {
                    // Fatal error
                    $app->enqueueMessage($error, 'error');

                    return false;
                }

                // Not fatal error
                $app->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
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
                $app->enqueueMessage($table->getError(), 'error');

                return false;
            }

            // Store the row.
            if (!$table->store()) {
                $app->enqueueMessage($table->getError(), 'error');

                return false;
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
    protected function cleanCache($group = null, $client_id = 0)
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
     * @since   12.2
     */
    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            if ($record->published != -2) {
                return false;
            }

            return Factory::getApplication()->getSession()->get('user')->authorise(
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
    protected function canEditState($record)
    {
        $user = Factory::getApplication()->getSession()->get('user');

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
     * @since    1.6
     */
    protected function prepareTable($table)
    {
        jimport('joomla.filter.output');

        $table->series_text = htmlspecialchars_decode($table->series_text, ENT_QUOTES);
        $table->alias       = ApplicationHelper::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = ApplicationHelper::stringURLSafe($table->series_text);
        }

        if (empty($table->id)) {
            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db    = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true);
                $query->select('MAX(ordering)')->from('#__bsms_series');
                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        }

        if ($table->ordering == 0) {
            $table->ordering = 1;
            $table->reorder('id = ' . (int)$table->id);
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
    protected function loadFormData()
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
    protected function getReorderConditions($table)
    {
        return [];
    }
}
