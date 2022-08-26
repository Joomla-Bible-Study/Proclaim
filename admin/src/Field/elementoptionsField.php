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
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;


/**
 * Books List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.4
 */
class elementoptionsField extends ListField
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since 7.0
	 */
	protected $type = 'Elementoptions';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of HTMLHelper options.
	 *
	 * @since 7.0
	 */
	protected function getOptions()
	{
		$options[] = HTMLHelper::_('select.option', '0', Text::_('JBS_CMN_NONE'));
		$options[] = HTMLHelper::_('select.option', '1', Text::_('JBS_TPL_PARAGRAPH'));
		$options[] = HTMLHelper::_('select.option', '2', Text::_('JBS_TPL_HEADER1'));
		$options[] = HTMLHelper::_('select.option', '3', Text::_('JBS_TPL_HEADER2'));
		$options[] = HTMLHelper::_('select.option', '4', Text::_('JBS_TPL_HEADER3'));
		$options[] = HTMLHelper::_('select.option', '5', Text::_('JBS_TPL_HEADER4'));
		$options[] = HTMLHelper::_('select.option', '6', Text::_('JBS_TPL_HEADER5'));
		$options[] = HTMLHelper::_('select.option', '7', Text::_('JBS_TPL_BLOCKQUOTE'));
		$options   = array_merge(parent::getOptions(), $options);

		return $options;
	}
}