<?php


/**
 * @author Tom Fuller
 * @copyright 2010
 * Displays a location list for the studieslist menu item
 */

// No direct access to this file
defined('_JEXEC') or die;
 
// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
 
/**
 * Lodation List Form Field class for the Joomla Bible Study component
 */
class JFormFieldLocationslist extends JFormFieldList
{
        /**
         * The field type.
         *
         * @var         string
         */
        protected $type = 'Locationslist';
 
        /**
         * Method to get a list of options for a list input.
         *
         * @return      array           An array of JHtml options.
         */
        protected function getOptions() 
        {
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select('id,location_text');
                $query->from('#__bsms_locations');
                $db->setQuery((string)$query);
                $messages = $db->loadObjectList();
                
                $options = array();
                
                if ($messages)
                {
                        foreach($messages as $message) 
                        {
                                $options[] = JHtml::_('select.option', $message->id, $message->location_text);
                        }
                }
               
                
                $options = array_merge(parent::getOptions(), $options);
                return $options;
        }
}


?>

