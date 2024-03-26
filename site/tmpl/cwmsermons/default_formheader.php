<?php

/**
 * Default FormHeader
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$input = Factory::getApplication()->input;
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&view=cwmsermons&t=' . $input->get('t', '1', 'int')); ?>"
      method="post" name="adminForm" id="adminForm">
