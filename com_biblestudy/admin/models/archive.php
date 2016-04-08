<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

defined('_JEXEC') or die;

/**
 * Controller for Archive
 *
 * @since  9.0.1
 */
class BiblestudyModelArchive extends JModelAdmin
{

	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_BIBLESTUDY';

	/**
	 * Gets the form from the XML file.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_biblestudy.archive', 'archive', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
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
		$db   = JFactory::getDbo();
		$query = $db->getQuery(true);
		$studies = 0;
		$mediafiles = 0;

		$data = JFactory::getApplication()->input->get('jform', array(), 'array');

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
