<?php
/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

//Joomla 1.6 <-> 1.5 Branch
try {
	jimport('joomla.application.component.modeladmin');
	abstract class modelClass extends JModelAdmin{}
}catch(Exception $e){
	jimport('joomla.application.component.model');
	abstract class modelClass extends JModel{}
}


class biblestudyModelcssedit extends modelClass
{
function __construct()
	{
		parent::__construct();

		//$array = JRequest::getVar('cid',  0, '', 'array');
		//$this->setId((int)$array[0]);
	}

function &getData()
	{
		$filename = JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
		$csscontents=fopen($filename,"rb");
		$this->_data->filecontent = fread($csscontents,filesize($filename));
		fclose($csscontents);
		//$this->assignRef('lists',		$lists);
		
		
		return $this->_data;
	}
}