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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Input\Input;

defined('_JEXEC') or die;

/**
 * HelloWorld Model
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class CWMTemplateCodeModel extends AdminModel
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
	public $typeAlias = 'com_proclaim.cwmtemplatecode';

	/**
	 * Name of the form
	 *
	 * @var string
	 * @since  4.0.0
	 */
	protected $formName = 'templatecode';

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @throws \Exception
	 * @since    2.5
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_proclaim.' . $this->formName, $this->formName, array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
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
		return $pk;
	}

	/**
	 * Get Type Language String
	 *
	 * @return string|Null
	 *
	 * @since 7.0
	 */
	public function getType()
	{
		$item  = $this->getItem();
		$type2 = $item->type;
		$type  = null;

		switch ($type2)
		{
			case 1:
				$type = Text::_('JBS_TPLCODE_SERMONLIST');
				break;

			case 2:
				$type = Text::_('JBS_TPLCODE_SERMON');
				break;

			case 3:
				$type = Text::_('JBS_TPLCODE_TEACHERS');
				break;

			case 4:
				$type = Text::_('JBS_TPLCODE_TEACHER');
				break;

			case 5:
				$type = Text::_('JBS_TPLCODE_SERIESDISPLAYS');
				break;

			case 6:
				$type = Text::_('JBS_TPLCODE_SERIESDISPLAY');
				break;
			case 7:
				$type = Text::_('JBS_TPLCODE_MODULE');
				break;
			case null:
				$type = null;
				break;
		}

		return $type;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		return $item;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('administrator');

		// Save the syntax for later use
		$app->setUserState('editor.source.syntax', 'php');

		// Initialise variables.
		$table = $this->getTable();
		$key   = $table->getKeyName();

		// Get the pk of the record from the request.
		$input = new Input;
		$pk    = $input->get($key, '', 'int');
		$this->setState($this->getName() . '.id', $pk);

		// Load the parameters.
		$value = ComponentHelper::getParams($this->option);
		$this->setState('params', $value);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 *
	 * @throws \Exception
	 * @since    2.5
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_proclaim.edit.templatecode.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}
