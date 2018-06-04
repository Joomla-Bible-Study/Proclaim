<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2016 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Teachers List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
JFormHelper::loadFieldClass('list');

class JFormFieldTemplatelist extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since 9.0.0
	 */
	protected $type = 'Templates';

	/** @var  Object Template Table
	 *
	 * @since 9.0.13 */
	public static $templates;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 *
	 * @since 9.0.0
	 */
	protected function getOptions()
	{
		$options = [];

		if (!self::$templates)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id,title');
			$query->from('#__bsms_templates');
			$query->where('published = 1');
			$query->order('text ASC');
			$db->setQuery((string) $query);
			$messages = $db->loadObjectList();

			foreach ($messages as $message)
			{
				$options[] = JHtml::_('select.option', $message->id, $message->title);
			}

			self::$templates = array_merge(parent::getOptions(), $options);
		}

		return self::$templates;
	}
}
