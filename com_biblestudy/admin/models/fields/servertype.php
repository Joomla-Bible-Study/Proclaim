<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');
use Joomla\Utilities\ArrayHelper;

/**
 * ServerType Field class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class JFormFieldServerType extends JFormFieldList
{
	/**
	 * Get Input
	 *
	 * @return string
	 *
	 * @since 7.0
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html     = array();
		$recordId = (int) $this->form->getValue('id');
		$size     = ($v = $this->element['size']) ? ' size="' . $v . '"' : '';
		$class    = ($v = $this->element['class']) ? ' class="' . $v . '"' : 'class="text_area"';

		// Get a reverse lookup of the endpoint type to endpoint name
		/** @var BiblestudyModelServers $model */
		$model    = JModelLegacy::getInstance('servers', 'BibleStudyModel');
		$rlu_type = $model->getTypeReverseLookup();

		$value = ArrayHelper::getValue($rlu_type, $this->value);
		JHtml::_('behavior.framework');
		JHtml::_('behavior.modal');

		$html[] = '<div class="input-append">';
		$html[] = '    <input type="text" readonly="readonly" disabled="disabled" value="' . $value . '"' . $size . $class . ' />';
		$html[] = '    <a class="btn" onclick="SqueezeBox.fromElement(this, {handler:\'iframe\', size: {x: 600, y: 450}, url:\'' .
			JRoute::_('index.php?option=com_biblestudy&view=servers&layout=types&tmpl=component&recordId=' . $recordId) . '\'})"><i class="icon-list"></i> ' .
			JText::_('JSELECT') . '</a>';
		$html[] = '    <input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}
