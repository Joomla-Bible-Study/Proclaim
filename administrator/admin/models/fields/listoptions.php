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
class JFormFieldListoptions extends JFormFieldList
{
        /**
         * The field type.
         *
         * @var         string
         */
        protected $type = 'Listoptions';
 
        /**
         * Method to get a list of options for a list input.
         *
         * @return      array           An array of JHtml options.
         */
        protected function getOptions() 
        {
                
                $options[] = JHtml::_('select.option', '0', 'JBS_CMN_NOTHING');
                $options[] = JHtml::_('select.option', '1', 'JBS_TPL_SCRIPTURE1_BRACE');
                $options[] = JHtml::_('select.option', '2', 'JBS_TPL_SCRIPTURE2_BRACE');
                $options[] = JHtml::_('select.option', '3', 'JBS_TPL_SECONDARY_REFERENCES_BRACE');        
                $options[] = JHtml::_('select.option', '4', 'JBS_TPL_DURATION_BRACE');
                $options[] = JHtml::_('select.option', '5', 'JBS_TPL_TITLE_BRACE');
                $options[] = JHtml::_('select.option', '6', 'JBS_TPL_INTRODUCTION_BRACE');
                $options[] = JHtml::_('select.option', '7', 'JBS_TPL_TEACHER_BRACE');
                $options[] = JHtml::_('select.option', '8', 'JBS_TPL_TITLE_TEACHER_BRACE');
                $options[] = JHtml::_('select.option', '9', 'JBS_TPL_SERIES_BRACE');
                $options[] = JHtml::_('select.option', '26', 'JBS_TPL_SERIES_THUMBNAIL_BRACE');
                $options[] = JHtml::_('select.option', '27', 'JBS_TPL_SERIES_DESCRIPTION_BRACE');
                $options[] = JHtml::_('select.option', '10', 'JBS_TPL_DATE_BRACE');
                $options[] = JHtml::_('select.option', '11', 'JBS_TPL_SUBMITTED_BRACE');
                $options[] = JHtml::_('select.option', '12', 'JBS_TPL_HITS_BRACE');
                $options[] = JHtml::_('select.option', '13', 'JBS_TPL_STUDYNUMBER_BRACE');
                $options[] = JHtml::_('select.option', '14', 'JBS_TPL_TOPIC_BRACE');
                $options[] = JHtml::_('select.option', '15', 'JBS_TPL_LOCATION_BRACE');
                $options[] = JHtml::_('select.option', '16', 'JBS_TPL_MESSAGE_TYPE_BRACE');
                $options[] = JHtml::_('select.option', '17', 'JBS_TPL_DETAILS_TEXT_BRACE');
                $options[] = JHtml::_('select.option', '18', 'JBS_TPL_DETAILS_TEXT_PDF_BRACE');
                $options[] = JHtml::_('select.option', '19', 'JBS_TPL_DETAILS_PDF_BRACE');
                $options[] = JHtml::_('select.option', '20', 'JBS_TPL_MEDIA_BRACE');
                $options[] = JHtml::_('select.option', '22', 'JBS_TPL_STORE_BRACE');
                $options[] = JHtml::_('select.option', '23', 'JBS_TPL_FILESIZE_BRACE');
                $options[] = JHtml::_('select.option', '25', 'JBS_TPL_THUMBNAIL_BRACE');
                $options[] = JHtml::_('select.option', '28', 'JBS_TPL_MEDIA_PLAYS');
                $options[] = JHtml::_('select.option', '29', 'JBS_TPL_MEDIA_DOWNLOADS');
                $options[] = JHtml::_('select.option', '24', 'JBS_CMN_CUSTOM');
                $options = array_merge(parent::getOptions(), $options);
                return $options;
        }
}


?>