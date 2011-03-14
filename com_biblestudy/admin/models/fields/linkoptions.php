<?php

/**
 * @author Tom Fuller
 * @copyright 2010
 * Displays a books list for the studieslist menu item
 */

// No direct access to this file
defined('_JEXEC') or die;
 
// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
 
/**
 * Books List Form Field class for the Joomla Bible Study component
 */
class JFormFieldLinkoptions extends JFormFieldList
{
        /**
         * The field type.
         *
         * @var         string
         */
        protected $type = 'Linkoptions';
 
        /**
         * Method to get a list of options for a list input.
         *
         * @return      array           An array of JHtml options.
         */
        protected function getOptions() 
        {
                $options[] = JHtml::_('select.option', '0', JText::_('JBS_TPL_NO_LINK'));
                $options[] = JHtml::_('select.option', '1', JText::_('JBS_TPL_LINK_TO_DETAILS'));
                $options[] = JHtml::_('select.option', '4', JText::_('JBS_TPL_LINK_TO_DETAILS_TOOLTIP'));
                $options[] = JHtml::_('select.option', '2', JText::_('JBS_TPL_LINK_TO_MEDIA'));        
                $options[] = JHtml::_('select.option', '5', JText::_('JBS_TPL_LINK_TO_MEDIA_TOOLTIP'));
                $options[] = JHtml::_('select.option', '3', JText::_('JBS_TPL_LINK_TO_TEACHERS_PROFILE'));
                $options[] = JHtml::_('select.option', '6', JText::_('JBS_TPL_LINK_TO_FIRST_ARTICLE'));
                $options[] = JHtml::_('select.option', '7', JText::_('JBS_TPL_LINK_TO_VIRTUEMART'));
                $options[] = JHtml::_('select.option', '8', JText::_('JBS_TPL_LINK_TO_DOCMAN'));
                $options = array_merge(parent::getOptions(), $options);
                return $options;
        }
}


?>