<?php

/**
 * @version     $Id: podcastlist.php 1362 2011-01-12 08:42:00Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.controlleradmin');

    abstract class controllerClass extends JControllerAdmin {

    }

class biblestudyControllerTeacherList extends controllerClass {
    /**
     * Proxy for getModel
     *
     * @param <String> $name    The name of the model
     * @param <String> $prefix  The prefix for the PHP class name
     * @return JModel
     *
     * @since 7.0
     */
    public function &getModel($name = 'teacheredit', $prefix = 'biblestudyModel') {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }
}
?>