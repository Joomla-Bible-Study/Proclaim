<?php

/**
 * @version     $Id: mediaedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.controllerform');

    abstract class controllerClass extends JControllerForm {

    }

class biblestudyControllermediaedit extends controllerClass
{
    /*
     * NOTE: This is needed to prevent Joomla 1.6's pluralization mechanisim from kicking in
     *
     * @todo  BCC  We should rename this controler to "mediafile" and the list view controller
     * to "mediafiles" so that the pluralization in 1.6 would work properly
     *
     * @since 7.0
     */
    protected $view_list = 'medialist';
	
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
	//	$this->registerTask( 'add'  , 	'edit' );
	}


	
}