<?php
/**
 * Default FormHeader
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// No Direct Access
defined('_JEXEC') or die;
$input = new JInput;
?>
<form
	action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=sermons&t=' . $input->get('t', '1', 'int')); ?>"
	method="post" class="form-inline">
