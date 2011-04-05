<?php


// no direct access
defined('_JEXEC') or die('Restricted access');



class Tableteacheredit extends JTable
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
	var $teachername = null;
	var $title = null;
	var $phone = null;
	var $email = null;
	var $website = null;
	var $information = null;
	var $image = null;
	var $imageh = null;
	var $imagew = null;
	var $thumb = null;
	var $thumbw = null;
	var $thumbh = null;
	var $short = null;
	var $ordering = null;
	var $catid = null;
	var $list_show = 1;
	var $teacher_thumbnail = null;
	var $teacher_image = null;
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tableteacheredit(& $db) {
		parent::__construct('#__bsms_teachers', 'id', $db);
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
                return 'com_biblestudy.teacheredit.'.(int) $this->$k;
        }
 
        /**
         * Method to return the title to use for the asset table.
         *
         * @return      string
         * @since       1.6
         */
        protected function _getAssetTitle()
        {
                $title = 'JBS Teacher: '.$this->teachername;
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
