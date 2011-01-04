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
class JFormFieldBookslist extends JFormFieldList
{
        /**
         * The field type.
         *
         * @var         string
         */
        protected $type = 'Bookslist';
 
        /**
         * Method to get a list of options for a list input.
         *
         * @return      array           An array of JHtml options.
         */
        protected function getOptions() 
        {
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select('id,bookname');
                $query->from('#__bsms_books');
                $db->setQuery((string)$query);
                $messages = $db->loadObjectList();
                $options = array();
                if ($messages)
                {
                        foreach($messages as $message) 
                        {
                                $options[] = JHtml::_('select.option', $message->id, $message->bookname);
                        }
                }
                $options = array_merge(parent::getOptions(), $options);
                return $options;
        }
}


?>