<?php

/**
 * FileSize Field
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package	BibleStudy.Site
 * @subpackage	Form
 * @since	7.1.0
 */
class JFormFieldFilesize extends JFormField {

    /**
     * Type
     * @var string
     */
    public $type = 'Filesize';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput() {
        // Initialize some field attributes.
        $size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
        $maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
        $class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
        $readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
        $disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

        // Initialize JavaScript field attributes.
        $onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

        return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' .
                ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' .
                $class . $size . $disabled . $readonly . $onchange . $maxLength . '/>' . $this->sizeConverter();
    }

    /**
     * Size Converter
     * @return string
     */
    private function sizeConverter() {
        $document = JFactory::getDocument();
        $document->addScriptDeclaration('function openConverter1()
		{
			var Wheight=125;
			var Wwidth=300;
			var winl = (screen.width - Wwidth) / 2;
			var wint = (screen.height - Wheight) / 2;

			var msg1=window.open("' . JURI::base() . 'components/com_biblestudy/convert1.htm","Window","scrollbars=1,width="+Wwidth+",height="+Wheight+",top="+wint+",left="+winl);
			if (!msg1.closed) {
				msg1.focus();
			}
		}');
        return '<a href="javascript:openConverter1();">' . JText::_('JBS_MED_FILESIZE_CONVERTER') . '</a>';
    }

}