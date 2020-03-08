<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Message Type List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class JFormFieldMessagetypelist extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since 7.0
	 */
	protected $type = 'Messagetypes';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 *
	 * @since 7.0
	 */
	protected function getOptions()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id,message_type');
		$query->from('#__bsms_message_type');
		$query->where('published = 1');
		$db->setQuery((string) $query);
		$messages = $db->loadObjectList();
		$options  = array();

		if ($messages)
		{
			foreach ($messages as $message)
			{
				$options[] = JHtml::_('select.option', $message->id, $message->message_type);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
