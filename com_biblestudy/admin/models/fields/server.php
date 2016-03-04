<?php
/**
 * Study field modal
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;


/**
 * Field class for Server
 *
 * @package  BibleStudy.Admin
 * @since    9.0.0
 */
class JFormFieldServer extends JFormField
{
	protected $type = 'Server';

	/**
	 * Get input form form
	 *
	 * @return array
	 */
	protected function getInput()
	{
		$allowEdit  = ((string) $this->element['edit'] == 'true') ? true : false;
		$allowClear = ((string) $this->element['clear'] != 'false') ? true : false;

		// Build the script.
		$view = JFactory::getApplication()->input->get('view');
		$size = ($v = $this->element['size']) ? ' size="' . $v . '"' : '';

		// Get a reverse lookup of the server id to server name
		$model  = JModelLegacy::getInstance('servers', 'BibleStudyModel');
		$rlu    = $model->getIdToNameReverseLookup();
		$server = JArrayHelper::getValue($rlu, $this->value);

		// Load the javascript
		JHtml::_('behavior.framework');
		JHtml::_('behavior.modal', 'a.modal');
		JHtml::_('bootstrap.tooltip');

		// Build the script.
		$script = array();

		// Setup variables for display.
		$html = array();
		if ($view == 'mediafileform')
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
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display.
		$html = array();
		if ($view == 'mediafileform')
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

		// Get the title of the linked chart
		if ((int) $this->value > 0)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('server_name'))
				->from($db->quoteName('#__bsms_servers'))
				->where('id = ' . (int) $this->value)
				->where('published = ' . 1);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}
		}

		if (empty($title))
		{
			$title = JText::_('JBS_SVR_SERVER_NAME');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The active server id field.
		if (0 == (int) $this->value)
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
