<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Model;

// No Direct Access
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use CWM\Component\Proclaim\Administrator\Helper\CWMThumbnail;
use CWM\Component\Proclaim\Administrator\Helper\CWMTranslated;
use CWM\Component\Proclaim\Administrator\Model\CWMMessageModel;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
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
class CWMMessageFormModel extends CWMMessageModel
{
	/**
	 * Model typeAlias string. Used for version history.
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	public $typeAlias = 'com_proclaim.cwmmessage';

	/**
	 * Name of the form
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $formName = 'cwmmessageform';

	/**
	 * Get the form data
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return boolean|\Joomla\CMS\Form\Form
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = parent::getForm($data, $loadData);

		// Prevent messing with article language and category when editing existing contact with associations
		if ($id = ($this->getState('message.id') && Associations::isEnabled()))
		{
			$associations = Associations::getAssociations('com_proclaim', '#__bsms_studies', 'com_message.item', $id);

			// Make fields read only
			if (!empty($associations))
			{
				$form->setFieldAttribute('language', 'readonly', 'true');
				$form->setFieldAttribute('language', 'filter', 'unset');
			}
		}

		return $form;
	}

	/**
	 * Get the return URL.
	 *
	 * @return  string  The return URL.
	 *
	 * @since   4.0.0
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 *
	 * @throws  \Exception
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('message.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->getString('layout'));
	}

	/**
	 * Allows preprocessing of the JForm object.
	 *
	 * @param   Form    $form   The form object
	 * @param   array   $data   The data to be merged into the form object
	 * @param   string  $group  The plugin group to be executed
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since   4.0.0
	 */
	protected function preprocessForm(Form $form, $data, $group = 'contact')
	{
		if (!Multilanguage::isEnabled())
		{
			$form->setFieldAttribute('language', 'type', 'hidden');
			$form->setFieldAttribute('language', 'default', '*');
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  bool|Table  A Table object
	 *
	 * @since   4.0.0

	 * @throws  \Exception
	 */
	public function getTable($name = 'CWMMessage', $prefix = 'Administrator', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to get media item
	 *
	 * @param   integer  $itemId ID
	 *
	 * @return  mixed
	 *
	 * @throws \Exception
	 * @since   9.0.0
	 */
	public function getItem($itemId = null)
	{
		$jinput = Factory::getApplication()->getInput();

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$itemId = $jinput->get('a_id', 0);
		}
		else
		{
			// The back end uses id so we use that the rest of the time and set it to 0 by default.
			$itemId = $jinput->get('id', 0);
		}

		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('message.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		try
		{
			if (!$table->load($itemId))
			{
				return false;
			}
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage());

			return false;
		}

		$properties = $table->getProperties();
		$value      = ArrayHelper::toObject($properties, \Joomla\CMS\Object\CMSObject::class);

		// Convert field to Registry.
		$value->params = new Registry($value->params);

		if ($itemId)
		{
			$value->tags = new TagsHelper;
			$value->tags->getTagIds($value->id, 'com_proclaim.messsage');
			$value->metadata['tags'] = $value->tags;
		}

		return $value;
	}
}
