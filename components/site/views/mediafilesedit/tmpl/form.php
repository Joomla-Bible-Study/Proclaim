<?php
/**
 * @version     $Id: form.php 1297 2011-01-04 22:27:12Z genu $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

require_once (JPATH_ROOT . DS . 'components' . DS . 'com_biblestudy' . DS . 'lib' . DS . 'biblestudy.defines.php');
if (JOOMLA_VERSION == '5') {
    echo $this->loadTemplate('15');
} else {
    echo $this->loadTemplate('16');
}
?>