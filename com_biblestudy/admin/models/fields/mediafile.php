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

use Joomla\Registry\Registry;

JFormHelper::loadFieldClass('list');

/**
 * Location List Form Field class for the Joomla Bible Study component
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JFormFieldMediafile extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since 7.0
	 */
	protected $type = 'Mediafile';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 *
	 * @since 7.0
	 */
	protected function getOptions()
	{
		if ($this->form->getValue('id'))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.id, a.params');
			$query->from('#__bsms_mediafiles as a');
			$query->where('study_id = ' . $this->form->getValue('id'));
			$db->setQuery((string) $query);
			$messages = $db->loadObjectList();
		}
		else
		{
			$messages = null;
		}

		$options = array();

		if ($messages)
		{
			foreach ($messages as $message)
			{
				$reg = new Registry;
				$reg->loadString($message->params);
				$message->params = $reg;
				$options[]       = JHtml::_('select.option', $message->id, $message->params->get('filename') ? $message->id . ' - ' .
						$message->params->get('mimetext') : $message->params->get('filename')
				);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
