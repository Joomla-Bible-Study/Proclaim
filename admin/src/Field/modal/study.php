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

/**
 * Field class for Modal_Study
 *
 * @package     Proclaim.Admin
 * @since       7.0.0
 * @deprecated  since version 7.1.0
 */
class JFormFieldModal_Study extends JFormField
{
	/**
	 * Set Modal_Study
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	protected $type = 'Modal_Study';

	/**
	 * Get input form form
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 9.0.0
	 */
	protected function getInput()
	{
		// Load the modal behavior script.

		$view = Factory::getApplication()->input->get('view');

		// Build the script.
		$script   = array();
		$script[] = '   function jSelectStudy_' . $this->id . '(id, title, book, teacher, series, type, year, topic, state, object) {';
		$script[] = '       document.id("' . $this->id . '_id").value = id;';
		$script[] = '       document.id("' . $this->id . '_name").value = title;';
		$script[] = '       SqueezeBox.close();';
		$script[] = '   }';

		// Add the script to the document head.
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display.
		$html = array();

		if ($view === 'mediafileform')
		{
			$sview = 'messagelist';
		}
		else
		{
			$sview = 'messages';
		}

		$link = 'index.php?option=com_biblestudy&amp;view=' . $sview . '&amp;layout=modal&amp;tmpl=component&amp;function=jSelectStudy_' . $this->id;

		$db = Factory::getDbo();
		$db->setQuery(
			'SELECT studytitle AS title' .
			' FROM #__bsms_studies' .
			' WHERE id = ' . (int) $this->value
		);
		$title = $db->loadResult();

		if (empty($title))
		{
			$title = JText::_('JBS_CMN_STUDY_SELECT');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current user display field.
		$html[] = '<div class="input-append">';
		$html[] = '  <input type="text" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';

		// The user select button.
		$html[] = ' <a class="btn modal" title="' . JText::_('JBS_CMN_STUDY_CHANGE') . '"  href="' .
			$link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-list"></i></a>';
		$html[] = '</div>';

		// The active article id field.
		if (0 === (int) $this->value)
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

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}
}
