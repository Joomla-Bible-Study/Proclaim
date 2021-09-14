<?php
/**
 * Study field modal
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Field class for Server
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class JFormFieldServer extends JFormField
{
	protected $type = 'Server';

	/**
	 * Get input form form
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	protected function getInput()
	{
		$allowEdit  = (string) $this->element['edit'] === 'true';
		$allowClear = (string) $this->element['clear'] !== 'false';

		// Build the script.
		$view = Factory::getApplication()->input->get('view');
		$size = ($v = $this->element['size']) ? ' size="' . $v . '"' : '';

		// Get a reverse lookup of the server id to server name
		$model  = JModelLegacy::getInstance('servers', 'BibleStudyModel');
		$rlu    = $model->getIdToNameReverseLookup();
		$server = ArrayHelper::getValue($rlu, $this->value);

		// Build the script.
		$script = array();

		if ($view === 'mediafileform')
		{
			$sview = 'mediafileform.setServer';
		}
		else
		{
			$sview = 'mediafile.setServer';
		}

		// Select button script
		$script[] = '       jSelectServer_jform_server_id = function(server_id) {
        window.parent.Joomla.submitbutton(\'' . $sview . '\', server_id);
        window.parent.SqueezeBox.close();
        }';
		$script[] = '	function jSelectServer_' . $this->id . '(id, name, object) {';
		$script[] = '		document.getElementById("' . $this->id . '").value = id;';
		$script[] = '		document.getElementById("' . $this->id . '_name").value = name;';

		if ($allowEdit)
		{
			$script[] = '		jQuery("#' . $this->id . '_edit").removeClass("hidden");';
		}

		if ($allowClear)
		{
			$script[] = '		jQuery("#' . $this->id . '_clear").removeClass("hidden");';
		}

		$script[] = '		jModalClose();';
		$script[] = '	}';

		// Clear button script
		static $scriptClear;

		if ($allowClear && !$scriptClear)
		{
			$scriptClear = true;

			$script[] = '	function jClearServer(id) {';
			$script[] = '		document.getElementById(id).value = "";';
			$script[] = '		document.getElementById(id + "_name").value = "'
				. htmlspecialchars(JText::_('JBS_SVR_SERVER_NAME', true), ENT_COMPAT, 'UTF-8') . '";';
			$script[] = '		jQuery("#"+id + "_clear").addClass("hidden");';
			$script[] = '		if (document.getElementById(id + "_edit")) {';
			$script[] = '			jQuery("#"+id + "_edit").addClass("hidden");';
			$script[] = '		}';
			$script[] = '		return false;';
			$script[] = '	}';
		}

		// Add the script to the document head.
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display.
		$html = array();

		if ($view === 'mediafileform')
		{
			$sview = 'serverslist';
		}
		else
		{
			$sview = 'servers';
		}

		$link = 'index.php?option=com_biblestudy&amp;view=' . $sview . '&amp;layout=modal&amp;tmpl=component&amp;function=jSelectServer_' . $this->id;

		if (isset($this->element['language']))
		{
			$link .= '&amp;forcedLanguage=' . $this->element['language'];
		}

		// The active server id field.
		if (0 === (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		// The current server display field.
		$html[] = '<span class="input-append">';
		$html[] = '<input type="text" class="input-large" id="' . $this->id . '_name" value="' . $server['name'] . '"' .
			$size . ' disabled="disabled" size="55" />';
		$html[] = '<a'
			. ' class="modal btn hasTooltip"'
			. ' title="' . JHtml::tooltipText('JBS_SVR_SERVER_NAME') . '"'
			. ' href="' . $link . '&amp;' . JSession::getFormToken() . '=1"'
			. ' rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'
			. '<i class="icon-file"></i> ' . JText::_('JSELECT')
			. '</a>';

		// Edit Server button.
		if ($allowEdit)
		{
			$html[] = '<a'
				. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
				. ' href="index.php?option=com_biblestudy&layout=modal&tmpl=component&task=server.edit&id=' . $value . '"'
				. ' target="_blank"'
				. ' title="' . JHtml::tooltipText('JBS_SVR_SERVER_NAME') . '" >'
				. '<span class="icon-edit"></span>' . JText::_('JACTION_EDIT')
				. '</a>';
		}

		// Clear Server button
		if ($allowClear)
		{
			$html[] = '<button'
				. ' id="' . $this->id . '_clear"'
				. ' class="btn' . ($value ? '' : ' hidden') . '"'
				. ' onclick="return jClearServer(\'' . $this->id . '\')">'
				. '<span class="icon-remove"></span>' . JText::_('JCLEAR')
				. '</button>';
		}

		$html[] = '</span>';

		// Note: class='required' for client side validation.
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '" ' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}
}
