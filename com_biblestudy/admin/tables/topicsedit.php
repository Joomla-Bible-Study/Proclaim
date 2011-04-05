<?php
/**
 * Bible Study Topics table class
 * 
 * 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



class Tabletopicsedit extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	var $published = 1;
	/**
	 * @var string
	 */
	var $topic_text = null;
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tabletopicsedit(& $db) {
		parent::__construct('#__bsms_topics', 'id', $db);
	}
    
     public function bind($array, $ignore = '')
    {
    if (isset($array['params']) && is_array($array['params'])) {
    $registry = new JRegistry();
    $registry->loadArray($array['params']);
    $array['params'] = (string)$registry;
    }
    
      
    // Bind the rules.
    if (isset($array['rules']) && is_array($array['rules'])) {
    $rules = new JRules($array['rules']);
    $this->setRules($rules);
    }
    
    return parent::bind($array, $ignore);
    }
     /**
         * Method to compute the default name of the asset.
         * The default name is in the form `table_name.id`
         * where id is the value of the primary key of the table.
         *
         * @return      string
         * @since       1.6
         */
        protected function _getAssetName()
        {
                $k = $this->_tbl_key;
                return 'com_biblestudy.topicsedit.'.(int) $this->$k;
        }
 
        /**
         * Method to return the title to use for the asset table.
         *
         * @return      string
         * @since       1.6
         */
        protected function _getAssetTitle()
        {
                $title = 'JBS Topic: '.$this->topic_text;
                return $title;
        }
 
        /**
         * Get the parent asset id for the record
         *
         * @return      int
         * @since       1.6
         */
        protected function _getAssetParentId()
        {
                $asset = JTable::getInstance('Asset');
                $asset->loadByName('com_biblestudy');
                return $asset->id;
        }
}
?>
