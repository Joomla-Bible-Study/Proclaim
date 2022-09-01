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
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;



/**
 * Form Field class for the FileSize
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class filesizeField extends ListField
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
		$readonly  = ((string) $this->element['readonly'] === 'true') ? ' readonly="readonly"' : '';
		$disabled  = ((string) $this->element['disabled'] === 'true') ? ' disabled="disabled"' : '';

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
		return "<button data-toggle=\"modal\" onclick=\"jQuery( '#collapseModal' ).modal('show'); return true;\" class=\"btn btn-primary\">
	<span class=\"icon-checkbox-partial\" aria-hidden=\"true\"></span>" . Text::_('JBS_MED_FILESIZE_CONVERTER') .
			"</button>";
	}
}
