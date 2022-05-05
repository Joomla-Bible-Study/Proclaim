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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

/**
 * ServerType Field class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class servertypeField extends ListField
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
		$model    = ListModel::getInstance('CWMServers', 'Model');
		$rlu_type = $model->getTypeReverseLookup();

		$value = ArrayHelper::getValue($rlu_type, $this->value);
		HTMLHelper::_('behavior.framework');
		HTMLHelper::_('behavior.modal');

		$html[] = '<div class="input-append">';
		$html[] = '    <input type="text" readonly="readonly" disabled="disabled" value="' . $value . '"' . $size . $class . ' />';
		$html[] = '    <a class="btn" onclick="SqueezeBox.fromElement(this, {handler:\'iframe\', size: {x: 600, y: 450}, url:\'' .
			Route::_('index.php?option=com_proclaim&view=servers&layout=types&tmpl=component&recordId=' . $recordId) . '\'})"><i class="icon-list"></i> ' .
			Text::_('JSELECT') . '</a>';
		$html[] = '    <input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}
