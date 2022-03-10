<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Versioning\VersionableModelTrait;

defined('_JEXEC') or die;

/**
 * Controller for Archive
 *
 * @since  9.0.1
 */
class CWMArchiveModel extends AdminModel
{
	use VersionableModelTrait;

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'com_proclaim';

	/**
	 * Gets the form from the XML file.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since 7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_proclaim.archive', 'archive', array('control' => 'jform', 'load_data' => $loadData));

		if ($form === null)
		{
			return false;
		}

		return $form;
	}

	/**
	 * Do Archive of Sermons and Media
	 *
	 * @return string
	 *
	 * @since  9.0.1
	 */
	public function doArchive()
	{
		$db   = Factory::getDbo();
		$query = $db->getQuery(true);
		$studies = 0;
		$mediafiles = 0;

		$data = Factory::getApplication()->input->get('jform', array(), 'array');

		// Used this field to show how long back to archive.
		$timeframe = (int) $data['timeframe'];

		// Use this to field (year, month, day)
		$swich = $data['swich'];

		// Fields to update.
		$fields = array(
			$db->qn('published') . ' =' . $db->q('2')
		);

		// Conditions for which records should be updated.
		$conditions = array(
			$db->qn('studydate') . ' <= NOW() - INTERVAL ' . $timeframe . ' ' . strtoupper($swich)
		);

		$query->update($db->quoteName('#__bsms_studies'))->set($fields)->where($conditions);

		$db->setQuery($query);

		if ($db->execute())
		{
			$studies = $db->getAffectedRows();
		}

		$query = $db->getQuery(true);

		// Conditions for which records should be updated.
		$conditions = array(
			$db->qn('createdate') . ' <= NOW() - INTERVAL ' . $timeframe . ' ' . strtoupper($swich)
		);

		$query->update($db->quoteName('#__bsms_mediafiles'))->set($fields)->where($conditions);

		$db->setQuery($query);

		if ($db->execute())
		{
			$mediafiles = $db->getAffectedRows();
		}

		$frame = $timeframe . ' ' . $swich . 's';

		return JText::sprintf('JBS_ARCHIVE_DB_CHANGE', $studies, $mediafiles, $frame);
	}
}
