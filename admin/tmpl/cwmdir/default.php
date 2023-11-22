<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// No direct access
defined('_JEXEC') or die();

echo $this->loadTemplate('header');
echo $this->loadTemplate('dirs');
echo $this->loadTemplate('files');
echo $this->loadTemplate('footer');
