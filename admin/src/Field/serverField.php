<?php
/**
 * Study field modal
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;
// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\ListModel;
use CWM\Component\Proclaim\Administrator\Model\CWMServersModel;

/**
 * Field class for Server
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class serverField extends ListField
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
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery('true');
		$query->select('*')
			->from('#__bsms_servers as s')
			->where('s.published = 1');
		$db->setQuery($query);
		$servs = $db->loadObjectList();
		$rlu   = array();

		foreach ($servs as $serv)
		{
			$rlu[$serv->id] = array(
				'name' => $serv->server_name,
				'type' => $serv->type
			);
		}

		//$model  = ListModel::getInstance('CWMServers', 'Model');
		//$rlu = CWMServersModel::getInstance('List')->getIdToNameReverseLookup();
		//$rlu    = $model->get->IdToNameReverseLookup();
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
				. htmlspecialchars(Text::_('JBS_SVR_SERVER_NAME', true), ENT_COMPAT, 'UTF-8') . '";';
			$script[] = '		jQuery("#"+id + "_clear").addClass("hidden");';
			$script[] = '		if (document.getElementById(id + "_edit")) {';
			$script[] = '			jQuery("#"+id + "_edit").addClass("hidden");';
			$script[] = '		}';
			$script[] = '		return false;';
			$script[] = '	}';
		}

		// Add the script to the document head.
		Factory::getApplication()->getDocument()->addScriptDeclaration(implode("\n", $script));

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

		$link = 'index.php?option=com_proclaim&amp;view=' . $sview . '&amp;layout=modal&amp;tmpl=component&amp;function=jSelectServer_' . $this->id;

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
			. ' title="' . HTMLHelper::tooltipText('JBS_SVR_SERVER_NAME') . '"'
			. ' href="' . $link . '&amp;' . Session::getFormToken() . '=1"'
			. ' rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'
			. '<i class="icon-file"></i> ' . Text::_('JSELECT')
			. '</a>';

		// Edit Server button.
		if ($allowEdit)
		{
			$html[] = '<a'
				. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
				. ' href="index.php?option=com_proclaim&layout=modal&tmpl=component&task=server.edit&id=' . $value . '"'
				. ' target="_blank"'
				. ' title="' . HTMLHelper::tooltipText('JBS_SVR_SERVER_NAME') . '" >'
				. '<span class="icon-edit"></span>' . Text::_('JACTION_EDIT')
				. '</a>';
		}

		// Clear Server button
		if ($allowClear)
		{
			$html[] = '<button'
				. ' id="' . $this->id . '_clear"'
				. ' class="btn' . ($value ? '' : ' hidden') . '"'
				. ' onclick="return jClearServer(\'' . $this->id . '\')">'
				. '<span class="icon-remove"></span>' . Text::_('JCLEAR')
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
