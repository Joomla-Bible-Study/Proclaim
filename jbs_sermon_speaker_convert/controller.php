<?php

/**
 * @version $Id: controller.php 1 $
 * @package BibleStudy SermonSpeaker Converter
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die();
jimport('joomla.application.component.controller');


class ss2jbsController extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display()
	{

		function convert()
        {
            //do the conversion
        }
		parent::display();
	}
}
