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
use CWM\Component\Proclaim\Administrator\Table\CWMTemplateTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die;

/**
 * Template model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMTemplateModel extends AdminModel
{
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @throws \Exception
	 * @since   12.2
	 */
	public function save($data)
	{
		// Make sure we cannot unpublished default template.
		if ($data['id'] == '1' && $data['published'] != '1')
		{
			Factory::getApplication()->enqueueMessage(Text::_('JBS_TPL_DEFAULT_ERROR'), 'error');

			return false;
		}

		return parent::save($data);
	}

	/**
	 * Copy Template
	 *
	 * @param   array  $cid  ID of template
	 *
	 * @return boolean
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function copy($cid)
	{
		foreach ($cid as $id)
		{
			$db       = Factory::getContainer()->get('DatabaseDriver');
			$tmplCurr = new CWMTemplateTable($db);

			$tmplCurr->load($id);
			$tmplCurr->id    = '';
			$tmplCurr->title .= " - copy";

			if (!$tmplCurr->store())
			{
				Factory::getApplication()->enqueueMessage($tmplCurr->getError(), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    $pks    A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws \Exception
	 * @since   12.2
	 */
	public function publish(&$pks, $value = 1)
	{
		foreach ($pks as $i => $pk)
		{
			if ($pk == 1 && $value != 1)
			{
				Factory::getApplication()->enqueueMessage(Text::_('JBS_TPL_DEFAULT_ERROR'), 'error');
				unset($pks[$i]);
			}
		}

		return parent::publish($pks, $value);
	}

	/**
	 * Get the form data
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @throws \Exception
	 * @since  7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_proclaim.template', 'template', array('control' => 'jform', 'load_data' => $loadData));

		return $form ?? false;
	}

	/**
	 * Method to check out a row for editing.
	 *
	 * @param   integer  $pk  The numeric id of the primary key.
	 *
	 * @return  integer|null  False on failure or error, true otherwise.
	 *
	 * @since   11.1
	 */
	public function checkout($pk = null)
	{
		return $pk;
	}

	/**
	 * Load Form Date
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @throws \Exception
	 * @since   7.0
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_proclaim.edit.template.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Custom clean the cache of COM_Proclaim and Proclaim modules
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
		parent::cleanCache('mod_proclaim');
	}
}
