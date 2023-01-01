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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Teachers List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class TeacherListField extends ListField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since 9.0.0
	 */
	protected $type = 'TeachersList';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 *
	 * @since 9.0.0
	 */
	protected function getOptions(): array
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('id,teachername');
		$query->from('#__bsms_teachers');
		$db->setQuery((string) $query);
		$teachers = $db->loadObjectList();
		$options  = array();

		if ($teachers)
		{
			foreach ($teachers as $teacher)
			{
				$options[] = HtmlHelper::_('select.option', $teacher->id, $teacher->teachername);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
