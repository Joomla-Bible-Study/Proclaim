<?php
/**
 * Series Detail Controller for Bible Study Component
 * 
 
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted Access');
//jimport('joomla.application.componet.controller');
/**
 *
 */
class biblestudyControllerseriesdetail extends JController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

	}

	/**
	 * display the edit form
	 * @return void
	 */
	function view()
	{
		JRequest::setVar( 'view', 'seriesdetail' );
		JRequest::setVar( 'layout', 'default'  );
		
		parent::display();
	}
	

	

?>
