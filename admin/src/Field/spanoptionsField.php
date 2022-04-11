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

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;



/**
 * Books List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.4
 */
class spanoptionsField extends ListField
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since 7.0
	 */
	protected $type = 'Spanoptions';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of HTMLHelper options.
	 *
	 * @since 7.0
	 */
	protected function getOptions()
	{
		$options[] = HTMLHelper::_('select.option', 'None', 0);
		$options[] = HTMLHelper::_('select.option', '1', 1);
		$options[] = HTMLHelper::_('select.option', '2', 2);
		$options[] = HTMLHelper::_('select.option', '3', 3);
		$options[] = HTMLHelper::_('select.option', '4', 4);
		$options[] = HTMLHelper::_('select.option', '5', 5);
		$options[] = HTMLHelper::_('select.option', '6', 6);
		$options[] = HTMLHelper::_('select.option', '7', 7);
		$options[] = HTMLHelper::_('select.option', '8', 8);
		$options[] = HTMLHelper::_('select.option', '9', 9);
		$options[] = HTMLHelper::_('select.option', '10', 10);
		$options[] = HTMLHelper::_('select.option', '11', 11);
		$options[] = HTMLHelper::_('select.option', '12', 12);
		$options   = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
