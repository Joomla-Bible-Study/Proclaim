<?php
/**
 * @version $Id: teacherdisplay.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * studies Edit Controller
 *
 */
class biblestudyControllerteacherdisplay extends JController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks

	}

	/**
	 * display the edit form
	 * @return void
	 */
	function view()
	{
		JRequest::setVar( 'view', 'teacherdisplay' );
		JRequest::setVar( 'layout', 'default'  );

		parent::display();
	}

}