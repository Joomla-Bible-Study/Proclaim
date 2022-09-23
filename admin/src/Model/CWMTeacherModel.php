<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Model;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use CWM\Component\Proclaim\Administrator\Helper\CWMThumbnail;
use CWM\Component\Proclaim\Administrator\Table\CWMTeacherTable;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Teacher model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMTeacherModel extends AdminModel
{
	/**
	 * Controller Prefix
	 *
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'com_proclaim';

	/**
	 * The type alias for this content type (for example, 'com_content.article').
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_proclaim.cwmteacher';

	/**
	 * Items data
	 *
	 * @var  object|boolean
	 * @since 10.0.0
	 */
	private $data;

	/**
	 * Name of the form
	 *
	 * @var string
	 * @since  4.0.0
	 */
	protected $formName = 'teacher';

	/**
	 * Get the form data
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		if (empty($data))
		{
			$this->getItem();
		}

		// Get the form.
		$form = $this->loadForm('com_proclaim.' . $this->formName, $this->formName, array('control' => 'jform', 'load_data' => $loadData));

		if ($form === null)
		{
			return false;
		}

		$jinput = Factory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0);
		}
		else
		{
			// The back end uses id so we use that the rest of the time and set it to 0 by default.
			$id = $jinput->get('id', 0);
		}

		$user = Factory::getApplication()->getSession()->get('user');

		// Check for existing article.
		// Modify the form based on Edit State access controls.
		if (($id != 0 && (!$user->authorise('core.edit.state', 'com_proclaim.teacher.' . (int) $id)))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_proclaim')))
		{
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
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the
	 *                   component.
	 *
	 * @throws \Exception
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		$tmp        = (array) $record;
		$db         = Factory::getContainer()->get('DatabaseDriver');
		$user       = $user = Factory::getApplication()->getSession()->get('user');
		$canDoState = $user->authorise('core.edit.state', $this->option);
		$text       = '';

		if (!empty($tmp))
		{
			$query = $db->getQuery(true);
			$query->select('id, studytitle')
				->from('#__bsms_studies')
				->where('teacher_id = ' . $record->id)
				->where('published != ' . $db->q('-2'));
			$db->setQuery($query);
			$studies = $db->loadObjectList();

			if (!$studies && $canDoState)
			{
				return true;
			}

			if ($record->published == '-2' || $record->published == '0')
			{
				foreach ($studies as $studie)
				{
					$text .= ' ' . $studie->id . '-"' . $studie->studytitle . '",';
				}

				Factory::getApplication()->enqueueMessage(Text::_('JBS_TCH_CAN_NOT_DELETE') . $text);
			}

			return false;
		}

		return $canDoState;
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer  $pk  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   11.1
	 */
	public function checkout($pk = null)
	{
		return true;
	}

	/**
	 * Saves data creating image thumbnails
	 *
	 * @param   array  $data  Data
	 *
	 * @return boolean
	 *
	 * @throws \Exception
	 * @since 9.0.0
	 */
	public function save($data)
    {
        //var_dump($data); die;
        /** @var Registry $params */
        $params = CWMParams::getAdmin()->params;
        $path = 'images/biblestudy/teachers/' . $data['id'];
        $prefix = 'thumb_';
        $image = HTMLHelper::cleanImageURL($data['image']);
        $data['image'] = $image->url;
        // If no image uploaded, just save data as usual
        if (empty($data['image']) || strpos($data['image'], $prefix) !== false) {
            if (empty($data['image'])) {
                // Modify model data if no image is set.
                $data['teacher_image'] = "";
                $data['teacher_thumbnail'] = "";
            } elseif (!CWMProclaimHelper::startsWith(basename($data['image']), $prefix)) {
                // Modify the image and model data
                CWMThumbnail::create($data['image'], $path, $params->get('thumbnail_teacher_size', 100));
                $data['teacher_image'] = $data['image'];
                $data['teacher_thumbnail'] = $path . '/thumb_' . basename($data['image']);
            } elseif (substr_count(basename($data['image']), $prefix) > 1) {
                // Out Fix removing redundent 'thumb_' in path.
                $x = substr_count(basename($data['image']), $prefix);

                while ($x > 1) {
                    if (substr(basename($data['image']), 0, strlen($prefix)) == $prefix) {
                        $str = substr(basename($data['image']), strlen($prefix));
                        $data['teacher_image'] = $path . '/' . $str;
                        $data['teacher_thumbnail'] = $path . '/' . $str;
                        $data['image'] = $path . '/' . $str;
                    }

                    $x--;
                }
            }
        }
        // Set contact to be a Int to work with Database

        $data['contact'] = intval($data['contact']);

        //Fix Save of update file to match path.
        if ($data['teacher_image'] != $data['image']) {
            $data['teacher_thumbnail'] = $data['image'];
            $data['teacher_image'] = $data['image'];
        }

		return parent::save($data);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 *
	 * @throws \Exception
	 * @since   7.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$session = Factory::getApplication()->getUserState('com_proclaim.edit.teacher.data', array());

		return empty($session) ? $this->data : $session;
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
	public function getItem($pk = null)
	{
		$jinput = new Input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$pk = $jinput->get('a_id', 0);
		}
		else
		{
			// The back end uses id so we use that the rest of the time and set it to 0 by default.
			$pk = $jinput->get('id', 0);
		}

		if (!empty($this->data))
		{
			return $this->data;
		}

		$this->data = parent::getItem($pk);

		return $this->data;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   CWMTeacherTable  $table  A reference to a JTable object.
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		$table->teachername = htmlspecialchars_decode($table->teachername, ENT_QUOTES);
		$table->alias       = ApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias))
		{
			$table->alias = ApplicationHelper::stringURLSafe($table->teachername);
		}

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = Factory::getContainer()->get('DatabaseDriver');
				$query = $db->getQuery(true);
				$query->select('MAX(ordering)')->from('#__bsms_teachers');
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Custom clean the cache of com_proclaim and biblestudy modules
	 *
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_proclaim');
		parent::cleanCache('mod_biblestudy');
	}
}
