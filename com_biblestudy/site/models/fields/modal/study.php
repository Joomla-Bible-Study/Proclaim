<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Field Modal class for Sutdy
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class JFormFieldModal_Study extends JFormField
{

	/**
	 * Type of Fiels
	 *
	 * @var string
	 */
	protected $type = 'Modal_Study';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');

		// Build the script.
		$script   = array();
		$script[] = '	function jSelectStudy_' . $this->id . '(id, title, book, teacher, series, type, year, topic, state, object) {';
		$script[] = '		document.id("' . $this->id . '_id").value = id;';
		$script[] = '		document.id("' . $this->id . '_name").value = title;';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display.
		$html = array();
		$link = 'index.php?option=com_biblestudy&amp;view=messagelist&amp;layout=modal&amp;tmpl=component&amp;function=jSelectStudy_' . $this->id;

		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('studytitle AS title')->from('#__bsms_studies')->where('id = ' . (int) $this->value);
		$db->setQuery($query);
		$title = $db->loadResult();

		if (empty($title))
		{
			$title = JText::_('JBS_CMN_STUDY_SELECT');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current user display field.
		$html[] = '<div class="fltrt">';
		$html[] = '  <input type="text" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
		$html[] = '</div>';

		// The user select button.
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		$html[] = '	<a class="modal" title="' . JText::_('JBS_CMN_STUDY_CHANGE') . '"  href="' . $link .
			'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">' . JText::_('JBS_CMN_STUDY_CHANGE') . '</a>';
		$html[] = '  </div>';
		$html[] = '</div>';

		// The active article id field.
		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		// This class='required' for client side validation
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}

}
