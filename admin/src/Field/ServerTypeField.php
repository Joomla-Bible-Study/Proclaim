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

use CWM\Component\Proclaim\Administrator\Model\CWMServersModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

/**
 * ServerType Field class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class ServerTypeField extends ListField
{
	/**
	 * Get Input
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	protected function getInput()
	{
		// Initialize variables.
		$recordId = (int) $this->form->getValue('id');

		// Create the modal id.
		$modalId = 'Types_' . $this->id;

		$value = (int) $this->value ?: '';

		/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

		// Add the modal field script to the document head.
		$wa->useScript('field.modal-fields');

		$html = '<span class="input-group">';
		$html .= '<input class="form-control" id="' . $this->id . '_name" type="text" value="' . $value . '" readonly size="35">';
		$html .= '<button'
			. ' class="btn btn-primary' . ($value ? ' hidden' : '') . '"'
			. ' id="' . $this->id . '_select"'
			. ' data-bs-toggle="modal"'
			. ' type="button"'
			. ' data-bs-target="#ModalSelect' . $modalId . '">'
			. '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
			. '</button>';
		$html .= HTMLHelper::_(
			'bootstrap.renderModal',
			'ModalSelect' . $modalId,
			array(
				'title'      => Text::_('JBS_CMN_SELECT_STUDY'),
				'url'        => 'index.php?option=com_proclaim&amp;view=cwmservers&amp;layout=types&amp;tmpl=component&amp;recordId=' . $recordId .
					'&amp;function=jSelectTypes_' . $this->id,
				'height'     => '400px',
				'width'      => '800px',
				'bodyHeight' => 70,
				'modalWidth' => 80,
				'footer'     => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
					. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
			)
		);

		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		$html .= '<input type="hidden" name="' . $this->name . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" ' . $class . ' />';

		return $html;
	}
}
