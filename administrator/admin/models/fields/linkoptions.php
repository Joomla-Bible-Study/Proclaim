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
                $options[] = JHtml::_('select.option', '0', 'JBS_TPL_NO_LINK');
                $options[] = JHtml::_('select.option', '1', 'JBS_TPL_LINK_TO_DETAILS');
                $options[] = JHtml::_('select.option', '4', 'JBS_TPL_LINK_TO_DETAILS_TOOLTIP');
                $options[] = JHtml::_('select.option', '2', 'JBS_TPL_LINK_TO_MEDIA');        
                $options[] = JHtml::_('select.option', '5', 'JBS_TPL_LINK_TO_MEDIA_TOOLTIP');
                $options[] = JHtml::_('select.option', '3', 'JBS_TPL_LINK_TO_TEACHERS_PROFILE');
                $options[] = JHtml::_('select.option', '6', 'JBS_TPL_LINK_TO_TEACHERS_PROFILE');
                $options[] = JHtml::_('select.option', '7', 'JBS_TPL_LINK_TO_TEACHERS_PROFILE');
                $options[] = JHtml::_('select.option', '8', 'JBS_TPL_LINK_TO_TEACHERS_PROFILE');
                $options = array_merge(parent::getOptions(), $options);
                return $options;
        }
}


?>