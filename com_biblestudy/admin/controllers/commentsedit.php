<?php

/**
 * @version     $Id: commentsedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.controllerform');

    abstract class controllerClass extends JControllerForm {

    }

class biblestudyControllercommentsedit extends controllerClass {

    protected $view_list = 'commentslist';
    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    function __construct() {
     
    
     
        parent::__construct();

    }
    
   

}

?>