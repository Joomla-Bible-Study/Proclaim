<?php
/**
 * Bible Study Style table class
 * @since 7.1.0
 * @version $Id: foldersedit.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

//No Direct Access
defined('_JEXEC') or die;



class BiblestudyTableStyle extends JTable
{
	

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
        function __construct(&$db) 
	{
		parent::__construct('#__bsms_styles', 'id', $db);
	}
        
	

	public function bind($array, $ignore = '')
	{
		
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
		return 'com_biblestudy.style.'.(int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return      string
	 * @since       1.6
	 */
	protected function _getAssetTitle()
	{
		$title = 'JBS Style: '.$this->filename;
		return $title;
	}

	/**
	 * Get the parent asset id for the record
	 *
	 * @return      int
	 * @since       1.6
	 */
	protected function _getAssetParentId($table=null, $id=null)
	{
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_biblestudy');
		return $asset->id;
	}
        
        /**
	 * Overriden JTable::store to set modified data and user id.
	 *
	 * @param	boolean	True to update fields even if they are null.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
        public function store($updateNulls = false)
        {
            $table = JTable::getInstance('Style', 'BiblestudyTable');
		if ($table->load(array('filename'=>$this->filename)) && ($table->id != $this->id || $this->id==0)) {
			$this->setError(JText::_('JBS_STYLE_FILENAME_NOT_UNIQUE'));
			return false;
		}
                //write the css file
                jimport('joomla.client.helper');
                jimport('joomla.filesystem.file');
                JClientHelper::setCredentialsFromRequest('ftp');
                $ftp = JClientHelper::getCredentials('ftp');
                $filename = $this->filename.'.css';
                $filecontent = $this->stylecode;
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . $filename;
                if (!$return = JFile::write($file, $filecontent))
                {
                    $this->setError(JText::_('JBS_STYLE_FILENAME_NOT_UNIQUE'));
			return false;
                }
                              
                
		return parent::store($updateNulls);
        }
        
        public function delete($pk = null)
        {
            jimport('joomla.client.helper');
                jimport('joomla.filesystem.file');
                JClientHelper::setCredentialsFromRequest('ftp');
                $ftp = JClientHelper::getCredentials('ftp');
                $filename = $this->filename.'.css';
                $file = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . $filename;
                if (!$delete = JFile::delete($file))
                {$this->setError(JText::_('JBS_STYLE_FILENAME_NOT_DELETED'));
			return false;}
                        
                 return parent::delete($pk);
                
        }
        
}