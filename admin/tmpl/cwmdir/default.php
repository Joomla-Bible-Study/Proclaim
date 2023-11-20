<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// No direct access
defined('_JEXEC') or die();

echo $this->loadTemplate('header');
echo $this->loadTemplate('dirs');
echo $this->loadTemplate('files');
echo $this->loadTemplate('footer');
