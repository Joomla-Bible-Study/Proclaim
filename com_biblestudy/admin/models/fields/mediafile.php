<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Import the list field type
jimport('joomla.form.helper');
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
	 */
	protected $type = 'Mediafile';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 */
	protected function getOptions()
	{
		if ($this->form->getValue('id'))
		{
			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('a.id, a.filename, b.mimetext');
			$query->from('#__bsms_mediafiles as a');
			$query->join('LEFT', '#__bsms_mimetype as b on a.mime_type = b.id');
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
				$options[] = JHtml::_('select.option', $message->id, empty($message->filename) ? $message->id . ' - ' . $message->mimetext : $message->filename);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
