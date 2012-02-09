<?php

/**
 * @version     $Id: foldersedit.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

abstract class controllerClass extends JControllerForm {

}

class biblestudyControllercpanel extends controllerClass
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	protected $view_list = 'cpanel';

	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
	}

function fixAssets()
{
   /*
    $task = JRequest::getWord('task','','get');
    if ($task == 'fixAssets')
    {
        //nothing
    }
    */
    require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' .DIRECTORY_SEPARATOR. 'biblestudy.assets.php');
    $assetfix = new fixJBSAssets();
    $assetdofix = $assetfix->AssetEntry();
    $this->setRedirect( 'index.php?option=com_biblestudy&view=cpanel', $assetdofix );
}
}