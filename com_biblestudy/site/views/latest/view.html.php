<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Latest
 *
 * @package  BibleStudy.Site
 * @since    7.1.0
 */
class BiblestudyViewLatest extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since 7.0
	 */
	public function display($tpl = null)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery('true');
		$query->select('id')
				->from('#__bsms_studies')
				->where('published = 1')
				->order('studydate DESC LIMIT 1');
		$db->setQuery($query);
		$id    = $db->loadResult();
		$input = new JInput;
		$t     = $input->getInt('t', '1');

		$link = JRoute::_('index.php?option=com_biblestudy&view=sermon&id=' . $id . '&t=' . $t);
		$app  = JFactory::getApplication();

		$app->redirect($link);
	}
}
