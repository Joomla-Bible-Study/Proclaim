<?php

/**
 * Study field modal
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Field class for Modal_Study
 * @package BibleStudy.Admin
 * @since 7.0.0
 * @deprecated since version 7.1.0
 */
class JFormFieldModal_Study extends JFormField {

    /**
     * Set Modal_Study
     * @var string
     */
    protected $type = 'Modal_Study';

    /**
     * Get input form form
     * @return array
     */
    protected function getInput() {
        // Load the modal behavior script.
        JHtml::_('behavior.modal', 'a.modal');

        // Build the script.
        $script = array();
        $script[] = '	function jSelectStudy_' . $this->id . '(id, title, book, teacher, series, type, year, topic, state, object) {';
        $script[] = '		document.id("' . $this->id . '_id").value = id;';
        $script[] = '		document.id("' . $this->id . '_name").value = title;';
        $script[] = '		SqueezeBox.close();';
        $script[] = '	}';

        // Add the script to the document head.
        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));


        // Setup variables for display.
        $html = array();
        $link = 'index.php?option=com_biblestudy&amp;view=messages&amp;layout=modal&amp;tmpl=component&amp;function=jSelectStudy_' . $this->id;

        $db = JFactory::getDBO();
        $db->setQuery(
                'SELECT studytitle AS title' .
                ' FROM #__bsms_studies' .
                ' WHERE id = ' . (int) $this->value
        );
        $title = $db->loadResult();

        if ($error = $db->getErrorMsg()) {
            JError::raiseWarning(500, $error);
        }

        if (empty($title)) {
            $title = JText::_('JBS_CMN_STUDY_SELECT');
        }
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        // The current user display field.
        $html[] = '<div class="fltlft">';
        $html[] = '  <input type="text" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
        $html[] = '</div>';

        // The user select button.
        $html[] = '<div class="button2-left">';
        $html[] = '  <div class="blank">';
        $html[] = '	<a class="modal" title="' . JText::_('JBS_CMN_STUDY_CHANGE') . '"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">' . JText::_('JBS_CMN_STUDY_CHANGE') . '</a>';
        $html[] = '  </div>';
        $html[] = '</div>';

        // The active article id field.
        if (0 == (int) $this->value) {
            $value = '';
        } else {
            $value = (int) $this->value;
        }

        // class='required' for client side validation
        $class = '';
        if ($this->required) {
            $class = ' class="required modal-value"';
        }

        $html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

        return implode("\n", $html);
    }

}