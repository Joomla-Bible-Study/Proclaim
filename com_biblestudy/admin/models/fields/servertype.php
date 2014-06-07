<?php
//@todo: Add propper header

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldServerType extends JFormFieldList {
    protected function getInput() {
        // Initialize variables.
        $html = array();
        $recordId	= (int) $this->form->getValue('id');
        $size		= ($v = $this->element['size']) ? ' size="'.$v.'"' : '';
        $class		= ($v = $this->element['class']) ? ' class="'.$v.'"' : 'class="text_area"';

        //Get a reverse lookup of the endpoint type to endpoint name
        $model = JModelLegacy::getInstance('servers', 'BibleStudyModel');
        $rlu_type = $model->getTypeReverseLookup();

        $value = JArrayHelper::getValue($rlu_type, $this->value);
        JHtml::_('behavior.framework');
        JHtml::_('behavior.modal');

        $html[] = '<input type="text" readonly="readonly" disabled="disabled" value="'.$value.'"'.$size.$class.' />';
        $html[] = '<input type="button" value="'.JText::_('JSELECT').'" onclick="SqueezeBox.fromElement(this, {handler:\'iframe\', size: {x: 600, y: 450}, url:\''.JRoute::_('index.php?option=com_biblestudy&view=servers&layout=types&tmpl=component&recordId='.$recordId).'\'})" />';
        $html[] = '<input type="hidden" name="'.$this->name.'" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';

        return implode("\n", $html);
    }
}