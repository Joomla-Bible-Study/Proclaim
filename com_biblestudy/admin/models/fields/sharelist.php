<?php

/**
 * @author Tom Fuller
 * @copyright 2010
 * Displays a mesage type list for the studieslist menu item
 */

//No Direct Access
defined('_JEXEC') or die;

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Message Type List Form Field class for the Joomla Bible Study component
 */
class JFormFieldSharelist extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 */
	protected $type = 'Sharelist';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 */
	protected function getOptions()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id,media_text');
		$query->from('#__bsms_share');
		$query->where('published = 1');
		$db->setQuery((string)$query);
		$messages = $db->loadObjectList();
		$options = array();
		if ($messages)
		{
			foreach($messages as $message)
			{
				$options[] = JHtml::_('select.option', $message->id, $message->media_text);
			}
		}
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}