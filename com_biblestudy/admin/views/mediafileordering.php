<?php
/**
 * @subpackage  com_biblestudy
 *
 * @copyright   Copyright (C) 2007 - 2012 Joomla Bible Study
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Supports an HTML select list of categories
 *
 *
 * @subpackage  com_biblestudy
 * @since       3.0
 */
class JFormFieldOrdering extends JFormField
{
    /**
     * The form field type.
     *
     * @var		string
     * @since	1.6
     */
    protected $type = 'Ordering';

    /**
     * Method to get the field input markup.
     *
     * @return	string	The field input markup.
     * @since	1.6
     */
    protected function getInput()
    {
        $html = array();
        $attr = '';

        // Initialize some field attributes.
        $attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
        $attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

        // Initialize JavaScript field attributes.
        $attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

        // Get some field values from the form.
        $mediafileId	= (int) $this->form->getValue('id');
        $studyId	= (int) $this->form->getValue('study_id');

        // Build the query for the ordering list.
        $query = 'SELECT ordering AS value, filename AS text' .
            ' FROM #__bsms_mediafiles' .
            ' WHERE study_id = ' . (int) $studyId .
            ' ORDER BY ordering';

        // Create a read-only list (no name) with a hidden input to store the value.
        if ((string) $this->element['readonly'] == 'true') {
            $html[] = JHtml::_('list.ordering', '', $query, trim($attr), $this->value, $mediafileId ? 0 : 1);
            $html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
        }
        // Create a regular list.
        else {
            $html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $mediafileId ? 0 : 1);
        }

        return implode($html);
    }
}
