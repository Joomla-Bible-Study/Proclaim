<?php
/**
 * Series Deatail modal
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field\Modal;

// No Direct Access
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * Supports a modal series picker.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class SeriesDetailField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Modal_SeriesDetail';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string  The field input markup.
	 *
	 * @since    1.6
	 */
	protected function getInput()
	{
		// Load the modal behavior script.
		HtmlHelper::_('behavior.modal', 'a.modal');

		// Load the javascript and css
		HtmlHelper::_('behavior.framework');
		HtmlHelper::_('script', 'system/modal.js', false, true);
		HtmlHelper::_('stylesheet', 'system/modal.css', array(), true);

		// Build the script.
		$script   = array();
		$script[] = '	function jSelectChart_' . $this->id . '(id, name, object) {';
		$script[] = '		document.id("' . $this->id . '_id").value = id;';
		$script[] = '		document.id("' . $this->id . '_name").value = name;';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Build the script.
		$script   = array();
		$script[] = '	window.addEvent("domready", function() {';
		$script[] = '		var div = new Element("div").setStyle("display", "none").injectBefore(document.id("menu-types"));';
		$script[] = '		document.id("menu-types").injectInside(div);';
		$script[] = '		SqueezeBox.initialize();';
		$script[] = '		SqueezeBox.assign($$("input.modal"), {parse:"rel"});';
		$script[] = '	});';

		// Add the script to the document head.
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Get the title of the linked chart
		$db = Factory::getDbo();
		$db->setQuery(
			'SELECT series_text AS name' .
			' FROM #__bsms_series' .
			' WHERE id = ' . (int) $this->value
		);
		$title = $db->loadResult();

		if (empty($title))
		{
			$title = Text::_('JBS_CMN_SELECT_SERIES');
		}

		$link = 'index.php?option=com_proclaim&amp;view=CWMSeriesDisplay&amp;layout=modal&amp;tmpl=component&amp;function=jSelectChart_' . $this->id;

		HtmlHelper::_('behavior.modal', 'a.modal');
		$html = "\n" . '<div class="fltlft"><input type="text" id="' . $this->id . '_name" value="' .
			htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '" disabled="disabled" /></div>';
		$html .= '<div class="button2-left"><div class="blank"><a class="modal" title="' . Text::_('JBS_CMN_SELECT_SERIES') .
			'"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">' . Text::_('JBS_CMN_SELECT_SERIES') . '</a></div></div>' . "\n";

		// The active study id field.
		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		// Class='required' for client side validation
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html .= '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return $html;
	}
}
