<?php
/**
 * @version $Id: cssedit.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');


class biblestudyModelcssedit extends JModel
{
function __construct()
	{
		parent::__construct();

	}

function &getData()
	{
		$filename = JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
		$csscontents=fopen($filename,"rb");
		$this->_data->filecontent = fread($csscontents,filesize($filename));
		fclose($csscontents);
		return $this->_data;
	}


}