<?php
defined('_JEXEC') or die('Restricted Access');

class Tabletemplateedit extends JTable {
	
	var $id = null;
	var $type = null;
	var $tmpl = null;
	var $published = 1;
	var $params = null;
	var $title = null;
	var $text = null;
	var $pdf = null;
	
	function Tabletemplateedit(&$db) { //dump ($array, 'array: ');
		parent::__construct('#__bsms_templates', 'id', $db);
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
                return 'com_biblestudy.templateedit.'.(int) $this->$k;
        }
 
        /**
         * Method to return the title to use for the asset table.
         *
         * @return      string
         * @since       1.6
         */
        protected function _getAssetTitle()
        {
                $title = 'JBS Template: '.$this->title;
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

public function store($updateNulls = false)
	{

		// Attempt to store the user data.
        $oldrow = JTable::getInstance('templateedit', 'Table');
			if (!$oldrow->load($this->id) && $oldrow->getError())
			{
				$this->setError($oldrow->getError());
			}
		return parent::store($updateNulls);
	}
}
?>