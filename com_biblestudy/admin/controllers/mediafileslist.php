<?php

/**
 * @version     $Id: mediafileslist.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.controlleradmin');

    abstract class controllerClass extends JControllerAdmin {

    }

class biblestudyControllerMediafileslist extends controllerClass {

    /**
     * Proxy for getModel
     *
     * @param <String> $name    The name of the model
     * @param <String> $prefix  The prefix for the PHP class name
     * @return JModel
     *
     * @since 7.0
     */
    public function &getModel($name = 'mediafilesedit', $prefix = 'biblestudyModel') {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }

}
