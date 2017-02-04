<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Books List Form Field class for the Joomla Bible Study component
 *
 * @package  BibleStudy.Admin
 * @since    7.0.4
 */
class JFormFieldRowOptions extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since 7.0
	 */
	protected $type = 'Rowoptions';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 *
	 * @since 7.0
	 */
	protected function getOptions()
	{
		$options[] = JHtml::_('select.option', '0', JText::_('JBS_CMN_HIDE'));
		$options[] = JHtml::_('select.option', '1', JText::_('JBS_TPL_ROW1'));
		$options[] = JHtml::_('select.option', '2', JText::_('JBS_TPL_ROW2'));
		$options[] = JHtml::_('select.option', '3', JText::_('JBS_TPL_ROW3'));
		$options[] = JHtml::_('select.option', '4', JText::_('JBS_TPL_ROW4'));
		$options[] = JHtml::_('select.option', '5', JText::_('JBS_TPL_ROW5'));
		$options[] = JHtml::_('select.option', '6', JText::_('JBS_TPL_ROW6'));
		$options   = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
