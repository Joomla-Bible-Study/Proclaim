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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;

/**
 * Podcast model class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMPodcastModel extends AdminModel
{
	/**
	 * Protect prefix
	 *
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'com_proclaim';

	/**
	 * Get the form data
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form  A JForm object on success, false on failure
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true): Form
	{
		// Get the form.
		return $this->loadForm('com_proclaim.podcast', 'podcast', array('control' => 'jform', 'load_data' => $loadData));
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer  $pk  The numeric id of the primary key.
	 *
	 * @return  integer  False on failure or error, true otherwise.
	 *
	 * @since   11.1
	 */
	public function checkout($pk = null): ?int
	{
		return (int) $pk;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  CMSObject|array   The default data is an empty array.
	 *
	 * @throws \Exception
	 * @since   7.0
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_proclaim.edit.podcast.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
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
	protected function cleanCache($group = null, int $client_id = 0): void
	{
		parent::cleanCache('com_proclaim');
		parent::cleanCache('mod_proclaim');
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
	public function getTable($name = 'CWMPodcast', $prefix = '', $options = array()): Table
	{
		return parent::getTable($name, $prefix, $options);
	}

/**
* Cleans image
*
* @param   array  $data  Data
*
* @return boolean
*
* @throws \Exception
* @since 9.0.0
*/


}
