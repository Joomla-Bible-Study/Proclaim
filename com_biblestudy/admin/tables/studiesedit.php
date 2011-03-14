<?php


// no direct access
defined('_JEXEC') or die('Restricted access');



class Tablestudiesedit extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;

	/**
	 * @var string
	 */
	var $published = 1;
	var $teacher_id = null;
	var	$studydate = null;
	var $studynumber = null;
	var	$booknumber = null;
	var $scripture = null;
	var $chapter_begin = null;
	var $chapter_end = null;
	var $verse_begin = null;
	var $verse_end = null;
	var $studytitle = null;
	var $studyintro = null;
	var $messagetype = null;
	var $series_id = null;
	var $studytext = null;
	var $topics_id = null;
	var $secondary_reference = null;
	var $media_hours = null;
	var $media_minutes = null;
	var $media_seconds = null;
	var $prod_cd = null;
	var $prod_dvd = null;
	var $server_cd = null;
	var $server_dvd = null;
	var $image_cd = null;
	var $image_dvd = null;
	var	$booknumber2 = null;
	var $chapter_begin2 = null;
	var $chapter_end2 = null;
	var $verse_begin2 = null;
	var $verse_end2 = null;	
	var $comments = 1;
	var $hits = 0;
	var $user_id = null;
	var $user_name = null;
	var $show_level = null;	
	var $location_id = null;
	var $thumbnailm = null;
	var $thumbhm = null;
	var $thumbwm = null;
	var $params = null;
    
    /**
	 * The rules associated with this record.
	 *
	 * @var	JRules	A JRules object.
	 */
	protected $_rules;
    
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablestudiesedit(& $db) {
		parent::__construct('#__bsms_studies', 'id', $db);
	}
	
	function bind($array, $ignore = '')
{ 
        if (key_exists( 'params', $array ) && is_array( $array['params'] ))
        {
                $registry = new JRegistry();
                $registry->loadArray($array['params']);
                $array['params'] = $registry->toString();
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
                return 'com_biblestudy.studiesedit.'.(int) $this->$k;
        }
 
        /**
         * Method to return the title to use for the asset table.
         *
         * @return      string
         * @since       1.6
         */
        protected function _getAssetTitle()
        {
                $title = 'JBS Study: '.$this->studytitle;
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
