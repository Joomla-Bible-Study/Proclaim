<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Administrator\Field;
// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;


/**
 * Location List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class mediafileField extends ListField
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
			$db = Factory::getContainer()->get('DatabaseDriver');
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
				$options[]       = HTMLHelper::_('select.option', $message->id, $message->params->get('filename') ? $message->id . ' - ' .
						$message->params->get('mimetext') : $message->params->get('filename')
				);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
