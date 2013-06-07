<?php

/**
 * TeacherOrdering Field
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Form Ordering for Teacher
 * @package BibleStudy.Admin
 * @since 7.0.4
 * @todo need to test and see if this is still needed?
 */
class JFormFieldTeacherOrdering extends JFormField {

    /**
     * The form field type.
     *
     * @var		string
     * @since	1.6
     */
    protected $type = 'TeacherOrdering';

    /**
     * Method to get the field input markup.
     *
     * @return	string	The field input markup.
     * @since	1.6
     */
    protected function getInput() {
        // Initialize variables.
        $html = array();
        $attr = '';

        // Initialize some field attributes.
        $attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
        $attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

        // Initialize JavaScript field attributes.
        $attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

        // Get some field values from the form.
        $teacherId = (int) $this->form->getValue('id');

        // Build the query for the ordering list.
        $query = 'SELECT ordering AS value, teachername AS text' .
                ' FROM #__bsms_teachers' .
                ' WHERE catid = 1' .
                ' ORDER BY ordering';

        // Create a read-only list (no name) with a hidden input to store the value.

        if ((string) $this->element['readonly'] == 'true') {
            $html[] = JHtml::_('list.ordering', '', $query, trim($attr), $this->value, $teacherId ? 0 : 1);
            $html[] = '<input type="hidden" name="' . $this->teachername . '" value="' . $this->value . '"/>';
        }
        // Create a regular list.
        else {

            $html[] = JHtml::_('list.ordering', $this->teachername, $query, trim($attr), $this->value, $teacherId ? 0 : 1);
        }

        return implode($html);
    }

}