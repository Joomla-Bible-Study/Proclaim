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
 * Books List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.4
 */
class JFormFieldLinkoptions extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 *
	 * @since 7.0
	 */
	protected $type = 'Linkoptions';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 *
	 * @since 7.0
	 */
	protected function getOptions()
	{
		$options[] = JHtml::_('select.option', '0', JText::_('JBS_TPL_NO_LINK'));
		$options[] = JHtml::_('select.option', '1', JText::_('JBS_TPL_LINK_TO_DETAILS'));
		$options[] = JHtml::_('select.option', '4', JText::_('JBS_TPL_LINK_TO_DETAILS_TOOLTIP'));
		$options[] = JHtml::_('select.option', '2', JText::_('JBS_TPL_LINK_TO_MEDIA'));
		$options[] = JHtml::_('select.option', '9', JText::_('JBS_TPL_LINK_TO_DOWNLOAD'));
		$options[] = JHtml::_('select.option', '5', JText::_('JBS_TPL_LINK_TO_MEDIA_TOOLTIP'));
		$options[] = JHtml::_('select.option', '3', JText::_('JBS_TPL_LINK_TO_TEACHERS_PROFILE'));
		$options[] = JHtml::_('select.option', '6', JText::_('JBS_TPL_LINK_TO_FIRST_ARTICLE'));
		$options[] = JHtml::_('select.option', '7', JText::_('JBS_TPL_LINK_TO_VIRTUEMART'));
		$options[] = JHtml::_('select.option', '8', JText::_('JBS_TPL_LINK_TO_DOCMAN'));
		$options   = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
