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

jimport('joomla.form.field');

/**
 * Field class for Server
 *
 * @package BibleStudy.Admin
 * @since   8.1.0
 */
class JFormFieldServer extends JFormField {
    protected $type = 'Server';

    /**
     * Get input form form
     * @return array
     */
    protected function getInput() {
        // Build the script.
        $html = array();
        $media_id	= (int) $this->form->getValue('id');
        $size		= ($v = $this->element['size']) ? ' size="'.$v.'"' : '';
        $class		= ($v = $this->element['class']) ? ' class="'.$v.'"' : 'class="text_area"';

        // Get a reverse lookup of the server id to server name
        $model = JModelLegacy::getInstance('servers', 'BibleStudyModel');
        $rlu_type = $model->getIdToNameReverseLookup();

        $value = JArrayHelper::getValue($rlu_type, $this->value);

        JHtml::_('behavior.framework');
        JHtml::_('behavior.modal');

        $html[] = '<input type="text" readonly="readonly" disabled="disabled" value="'.$value.'"'.$size.$class.' />';
        $html[] = '<input type="button" value="'.JText::_('JSELECT').'" onclick="SqueezeBox.fromElement(this, {handler:\'iframe\', size: {x: 600, y: 450}, url:\''.JRoute::_('index.php?option=com_biblestudy&view=servers&layout=servers&tmpl=component&media_id='.$media_id).'\'})" />';
        $html[] = '<input type="hidden" name="'.$this->name.'" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';

        return implode("\n", $html);
    }

}