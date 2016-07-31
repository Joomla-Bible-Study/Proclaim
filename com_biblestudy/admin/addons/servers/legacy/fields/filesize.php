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

jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

/**
 * Form Field class for the FileSize
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JFormFieldFilesize extends JFormField
{
	/**
	 *  Set Naming of type
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $type = 'Filesize';

	/**
	 * Get impute of form
	 *
	 * @return string
	 *
	 * @since 1.5
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$size      = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class     = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly  = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled  = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' .
		' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' .
		$class . $size . $disabled . $readonly . $onchange . $maxLength . '/>' . $this->sizeConverter();
	}

	/**
	 * Returns converted size
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	private function sizeConverter()
	{
		JHtml::script(JUri::root() . '/media/com_biblestudy/js/filesize.js');

		return '<a style="float: left; margin-top: 6px;" href="javascript:openConverter1();">' . JText::_('JBS_MED_FILESIZE_CONVERTER') . '</a>';
	}
}
