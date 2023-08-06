<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field\Modal;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CWMServersModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

/**
 * ServerType Field class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class ServerTypeField extends FormField
{
	/**
	 * Set Modal_Study
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	protected $type = 'Modal_ServerType';

	/**
	 * Get Input
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	protected function getInput(): string
	{
		$allowNew       = ((string) $this->element['new'] === 'true');
		$allowEdit      = ((string) $this->element['edit'] === 'true');
		$allowClear     = ((string) $this->element['clear'] !== 'false');
		$allowSelect    = ((string) $this->element['select'] !== 'false');
		$allowPropagate = ((string) $this->element['propagate'] === 'true');

		$recordId = (int) $this->form->getValue('id');

		// Load language
		Factory::getApplication()->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);

		// The active server id field making sure it uses the lower case naming for namespace corrections.
		$value       = (string) strtolower($this->value) ?: '';
		$this->value = strtolower($this->value);

		// Create the modal id.
		$modalId = 'types_' . $this->id;

		/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

		// Add the modal field script to the document head.
		$wa->useScript('field.modal-fields');

		// Script to proxy the select modal function to the modal-fields.js file.
		if ($allowSelect)
		{
			static $scriptSelect = null;

			if (is_null($scriptSelect))
			{
				$scriptSelect = array();
			}

			if (!isset($scriptSelect[$this->id]))
			{
				$wa->addInlineScript(
					"
				window.jSelectType_" . $this->id . " = function (id, title, object) {
					window.processModalSelect('Type', '" . $this->id . "', id, title, '', object);
				}",
					[],
					['type' => 'module']
				);

				Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

				$scriptSelect[$this->id] = true;
			}
		}

		// Setup variables for display.
		$linkSeries = 'index.php?option=com_proclaim&amp;view=cwmservers&amp;layout=types&amp;tmpl=component&amp;recordId=' .
			$recordId . '&amp;' . Session::getFormToken() . '=1';
		$linkSerie  = 'index.php?option=com_proclaim&amp;view=cwmserver&amp;layout=modal&amp;tmpl=component&amp;recordId=' .
			$recordId . '&amp;' . Session::getFormToken() . '=1';

		if (isset($this->element['language']))
		{
			$linkSeries .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkSerie  .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle = Text::_('JBS_CMN_SELECT_SERVERTYPE') . ' &#8212; ' . $this->element['label'];
		}
		else
		{
			$modalTitle = Text::_('JBS_CMN_SELECT_STUDY');
		}

		$urlSelect = $linkSeries . '&amp;function=jSelectType_' . $this->id;
		$urlEdit   = $linkSerie . '&amp;task=cwmserver.edit&amp;id=\' + document.getElementById(&quot;' . $this->id . '_id&quot;).value + \'';
		$urlNew    = $linkSerie . '&amp;task=cwmserver.add';

		// The current series display field.
		$html = '';

		if ($allowSelect || $allowNew || $allowEdit || $allowClear)
		{
			$html .= '<span class="input-group">';
		}

		// Get a reverse lookup of the endpoint type to endpoint name
		$model    = new CWMServersModel;
		$rlu_type = $model->getTypeReverseLookup();

		$text = (string) ArrayHelper::getValue($rlu_type, $this->value);

		$html .= '<input class="form-control" id="' . $this->id . '_name" type="text" value="' . $text . '" readonly size="35">';

		// Select Message button
		if ($allowSelect)
		{
			$html .= '<button'
				. ' class="btn btn-primary' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_select"'
				. ' data-bs-toggle="modal"'
				. ' type="button"'
				. ' data-bs-target="#ModalSelect' . $modalId . '">'
				. '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
				. '</button>';
		}

		// New Message button
		if ($allowNew)
		{
			$html .= '<button'
				. ' class="btn btn-secondary' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_new"'
				. ' data-bs-toggle="modal"'
				. ' type="button"'
				. ' data-bs-target="#ModalNew' . $modalId . '">'
				. '<span class="icon-plus" aria-hidden="true"></span> ' . Text::_('JACTION_CREATE')
				. '</button>';
		}

		// Edit message button
		if ($allowEdit)
		{
			$html .= '<button'
				. ' class="btn btn-primary' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-bs-toggle="modal"'
				. ' type="button"'
				. ' data-bs-target="#ModalEdit' . $modalId . '">'
				. '<span class="icon-pen-square" aria-hidden="true"></span> ' . Text::_('JACTION_EDIT')
				. '</button>';
		}

		// Clear message button
		if ($allowClear)
		{
			$html .= '<button'
				. ' class="btn btn-secondary' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' type="button"'
				. ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
				. '<span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR')
				. '</button>';
		}

		if ($allowSelect || $allowNew || $allowEdit || $allowClear)
		{
			$html .= '</span>';
		}

		// Select message modal
		if ($allowSelect)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalSelect' . $modalId,
				array(
					'title'      => $modalTitle,
					'url'        => $urlSelect,
					'height'     => '400px',
					'width'      => '800px',
					'bodyHeight' => 70,
					'modalWidth' => 80,
					'footer'     => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
						. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
				)
			);
		}

		// New message modal
		if ($allowNew)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalNew' . $modalId,
				array(
					'title'       => Text::_('JBS_STY_NEW_SERVERTYPE'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlNew,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => 70,
					'modalWidth'  => 80,
					'footer'      => '<button type="button" class="btn btn-secondary"'
						. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'cwmmessage\', \'cancel\', \'item-form\'); return false;">'
						. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
						. '<button type="button" class="btn btn-primary"'
						. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'cwmmessage\', \'save\', \'item-form\'); return false;">'
						. Text::_('JSAVE') . '</button>'
						. '<button type="button" class="btn btn-success"'
						. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'cwmmessage\', \'apply\', \'item-form\'); return false;">'
						. Text::_('JAPPLY') . '</button>',
				)
			);
		}

		// Edit message modal
		if ($allowEdit)
		{
			$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalEdit' . $modalId,
				array(
					'title'       => Text::_('JBS_STY_EDIT_SERVERTYPE'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlEdit,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => 70,
					'modalWidth'  => 80,
					'footer'      => '<button type="button" class="btn btn-secondary"'
						. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'cwmmessage\', \'cancel\', \'item-form\'); return false;">'
						. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
						. '<button type="button" class="btn btn-primary"'
						. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'cwmmessage\', \'save\', \'item-form\'); return false;">'
						. Text::_('JSAVE') . '</button>'
						. '<button type="button" class="btn btn-success"'
						. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'cwmmessage\', \'apply\', \'item-form\'); return false;">'
						. Text::_('JAPPLY') . '</button>',
				)
			);
		}

		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		$html .= '<input type="hidden" id="' . $this->id . '_id"' . $class . ' data-required="' . (int) $this->required
			. '" name="' . $this->name . '" data-text="' .
			htmlspecialchars(Text::_('JBS_CMN_SELECT_SERVERTYPE'), ENT_COMPAT, 'UTF-8') . '" value="' . $value . '">';

		return $html;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.4
	 */
	protected function getLabel(): string
	{
		return str_replace($this->id, $this->id . '_name', parent::getLabel());
	}
}
