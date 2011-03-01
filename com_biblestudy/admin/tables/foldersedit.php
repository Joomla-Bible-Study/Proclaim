<?php
/**
 * Bible Study Series table class
 * 
 * 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



class Tablefoldersedit extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;
	var $published = null;
	/**
	 * @var string
	 */
	var $foldername = null;
	var $folderpath = null;
	
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablefoldersedit(& $db) {
		parent::__construct('#__bsms_folders', 'id', $db);
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
                return 'com_biblestudy.foldersedit.'.(int) $this->$k;
        }
 
        /**
         * Method to return the title to use for the asset table.
         *
         * @return      string
         * @since       1.6
         */
        protected function _getAssetTitle()
        {
                return $this->greeting;
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
