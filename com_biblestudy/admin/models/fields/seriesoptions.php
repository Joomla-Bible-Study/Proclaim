<?php

/**
 * @author Tom Fuller
 * @copyright 2010
 * Displays options
 */

// No direct access to this file
defined('_JEXEC') or die;
 
// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
 
/**
 * Books List Form Field class for the Joomla Bible Study component
 */
class JFormFieldSeriesoptions extends JFormFieldList
{
        /**
         * The field type.
         *
         * @var         string
         */
        protected $type = 'Seriesoptions';
 
        /**
         * Method to get a list of options for a list input.
         *
         * @return      array           An array of JHtml options.
         */
        protected function getOptions() 
        {
                $options[] = JHtml::_('select.option', '5', JText::_('JBS_CMN_STUDY_TITLE'));
                $options[] = JHtml::_('select.option', '10', JText::_('JBS_CMN_STUDY_DATE'));
                $options[] = JHtml::_('select.option', '7', JText::_('JBS_CMN_TEACHER_NAME'));
                $options[] = JHtml::_('select.option', '8', JText::_('JBS_TPL_TEACHER_TITLE'));        
                $options[] = JHtml::_('select.option', '1', JText::_('JBS_CMN_SCRIPTURE'));
                $options[] = JHtml::_('select.option', '2', JText::_('JBS_CMN_SCRIPTURE2'));
                $options[] = JHtml::_('select.option', '3', JText::_('JBS_TPL_SECONDARY_REFERENCE'));
                $options[] = JHtml::_('select.option', '4', JText::_('JBS_CMN_DURATION'));
                $options[] = JHtml::_('select.option', '25', JText::_('JBS_CMN_THUMBNAIL'));
                $options[] = JHtml::_('select.option', '6', JText::_('JBS_TPL_INTRODUCTION'));
                $options[] = JHtml::_('select.option', '12', JText::_('JBS_CMN_HITS'));
                $options[] = JHtml::_('select.option', '13', JText::_('JBS_CMN_STUDYNUMBER'));
                $options[] = JHtml::_('select.option', '14', JText::_('JBS_CMN_TOPIC'));
                $options[] = JHtml::_('select.option', '15', JText::_('JBS_CMN_LOCATION'));
                $options[] = JHtml::_('select.option', '16', JText::_('JBS_CMN_MESSAGE_TYPE'));
                $options[] = JHtml::_('select.option', '17', JText::_('JBS_TPL_DETAILS_TEXT'));
                $options[] = JHtml::_('select.option', '18', JText::_('JBS_TPL_DETAILS_TEXT_PDF'));
                $options[] = JHtml::_('select.option', '19', JText::_('JBS_TPL_DETAILS_PDF'));
                $options[] = JHtml::_('select.option', '20', JText::_('JBS_CMN_MEDIA'));
                $options[] = JHtml::_('select.option', '22', JText::_('JBS_CMN_STORE'));
                $options[] = JHtml::_('select.option', '23', JText::_('JBS_CMN_FILESIZE'));
                $options[] = JHtml::_('select.option', '28', JText::_('JBS_TPL_MEDIA_PLAYS'));
                $options[] = JHtml::_('select.option', '29', JText::_('JBS_TPL_MEDIA_DOWNLOADS'));
                $options = array_merge(parent::getOptions(), $options);
                return $options;
        }
}


?>