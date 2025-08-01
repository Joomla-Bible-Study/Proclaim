<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Administrator\Table\CwmteacherTable;
use Exception;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Input\Input;
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
     * @throws Exception
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true): mixed
    {
        if (empty($data)) {
            $this->getItem();
        }

        // Get the form.
        $form = $this->loadForm(
            'com_proclaim.' . $this->formName,
            $this->formName,
            array('control' => 'jform', 'load_data' => $loadData)
        );

        if ($form === null) {
            return false;
        }

        $jinput = Factory::getApplication()->input;

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
     * @throws Exception
     * @since    1.7.0
     */
    public function getItem($pk = null): mixed
    {
        $jinput = new Input();

        // The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
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

        return $this->data;
    }

    /**
     * Method to check out a row for editing.
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
     * Method to validate the form data.
     *
     * @param   Form    $form   The form to validate against.
     * @param   array   $data   The data to validate.
     * @param   string  $group  The name of the field group to validate.
     *
     * @return  array|bool  Array of filtered data if valid, false otherwise.
     *
     * @throws Exception
     * @see     JFilterInput
     * @since   3.7.0
     * @see     \Joomla\CMS\Form\FormRule
     */
    public function validate($form, $data, $group = null): bool|array
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        if (!$this->getCurrentUser()->authorise('core.admin', 'com_proclaim') && isset($data['rules'])) {
            unset($data['rules']);
        }

        // Check for duplicates on new teachers
        if (
            (!isset($data['id']) || (int) $data['id'] === 0) && $data['alias'] === null &&
            in_array(
                $input->get('task'),
                ['apply', 'save', 'save2new']
            )
        ) {
            if ((int) $app->get('unicodeslugs') === 1) {
                $data['alias'] = OutputFilter::stringUrlUnicodeSlug($data['teachername']);
            } else {
                $data['alias'] = OutputFilter::stringURLSafe($data['teachername']);
            }

            $table = $this->getTable();

            if ($table->load(['alias' => $data['alias']])) {
                $this->setError(Text::_('JBS_TCH_DUPLICATE'));

                return false;
            }
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
     * @throws Exception
     * @since   1.6
     */
    protected function canEditState($record): bool
    {
        $db   = Factory::getContainer()->get('DatabaseDriver');
        $text = '';

        if (!empty($record) && $this->getState('task') === 'trash') {
            $query = $db->getQuery(true);
            $query->select('id, studytitle')
                ->from('#__bsms_studies')
                ->where('teacher_id = ' . $record->id)
                ->where('published != ' . $db->q('-2'));
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
     * @throws Exception
     * @since 9.0.0
     */
    public function save($data): bool
    {
        /** @var Registry $params */
        $params        = Cwmparams::getAdmin()->params;
        $path          = 'images/biblestudy/teachers/' . $data['id'];
        $prefix        = 'thumb_';
        $image         = HTMLHelper::cleanImageURL($data['image']);
        $data['image'] = $image->url;

        // If no image uploaded, just save data as usual
        if (empty($data['image']) || strpos($data['image'], $prefix) !== false) {
            if (empty($data['image'])) {
                // Modify model data if no image is set.
                $data['teacher_image']     = "";
                $data['teacher_thumbnail'] = "";
            } elseif (!str_starts_with(basename($data['image']), $prefix)) {
                // Modify the image and model data
                Cwmthumbnail::create($data['image'], $path, $params->get('thumbnail_teacher_size', 100));
                $data['teacher_image']     = $data['image'];
                $data['teacher_thumbnail'] = $path . '/thumb_' . basename($data['image']);
            } elseif (substr_count(basename($data['image']), $prefix) > 1) {
                // Out Fix removing redundant 'thumb_' in path.
                $x = substr_count(basename($data['image']), $prefix);

                while ($x > 1) {
                    if (str_starts_with(basename($data['image']), $prefix)) {
                        $str                       = substr(basename($data['image']), strlen($prefix));
                        $data['teacher_image']     = $path . '/' . $str;
                        $data['teacher_thumbnail'] = $path . '/' . $str;
                        $data['image']             = $path . '/' . $str;
                    }

                    $x--;
                }
            }
        }

        // Set contact to be an Int to work with Database
        $data['contact'] = (int) $data['contact'];

        // Fix Save of an update file to match paths.
        if ($data['teacher_image'] !== $data['image']) {
            $data['teacher_thumbnail'] = $data['image'];
            $data['teacher_image']     = $data['image'];
        }

        return parent::save($data);
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
     *
     * @throws Exception
     * @since   1.6
     */
    protected function canDelete($record): bool
    {
        $app        = Factory::getApplication();
        $db         = Factory::getContainer()->get('DatabaseDriver');
        $user       = $app->getIdentity();
        $canDoState = $user->authorise('core.edit.state', $this->option);
        $text       = '';

        // Iterate the items to delete each one.
        $query = $db->getQuery(true);
        $query->select('id, studytitle')
            ->from('#__bsms_studies')
            ->where('teacher_id = ' . $record->id);
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
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     *
     * @throws Exception
     * @since   7.0
     */
    protected function loadFormData(): mixed
    {
        // Check the session for previously entered form data.
        $session = Factory::getApplication()->getUserState('com_proclaim.edit.teacher.data', array());

        return empty($session) ? $this->data : $session;
    }

    /**
     * Prepare and sanitize the table prior to saving.
     *
     * @param   CwmteacherTable  $table  A reference to a JTable object.
     *
     * @return  void
     *
     * @since    1.6
     */
    protected function prepareTable($table): void
    {
        $table->teachername = htmlspecialchars_decode($table->teachername, ENT_QUOTES);
        $table->alias       = ApplicationHelper::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = ApplicationHelper::stringURLSafe($table->teachername);
        }

        if (empty($table->id)) {
            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db    = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true);
                $query->select('MAX(ordering)')->from('#__bsms_teachers');
                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        }
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
}
