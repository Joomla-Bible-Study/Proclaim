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

// Import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Teachers List Form Field class for the Joomla Bible Study component
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JFormFieldTeacherlist extends JFormFieldList
{

	/**
	 * The field type.
	 *
	 * @var         string
	 */
	protected $type = 'Teachers';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 */
	protected function getOptions()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id,teachername');
		$query->from('#__bsms_teachers');
		$db->setQuery((string) $query);
		$messages = $db->loadObjectList();
		$options  = array();

		if ($messages)
		{
			foreach ($messages as $message)
			{
				$options[] = JHtml::_('select.option', $message->id, $message->teachername);
			}
		}
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
