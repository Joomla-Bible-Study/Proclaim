<?php
/**
 * Default FormHeader
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
// No Direct Access
defined('_JEXEC') or die;
$input = Factory::getApplication();
?>
<form
	action="<?php echo Route::_('index.php?option=com_proclaim&view=CWMSermons&t=' . $input->get('t', '1', 'int')); ?>"
	method="post" name="adminForm" id="adminForm">
