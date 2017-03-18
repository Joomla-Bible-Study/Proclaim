<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No direct access
defined('_JEXEC') or die();

echo $this->loadTemplate('header');
echo $this->loadTemplate('dirs');
echo $this->loadTemplate('files');
echo $this->loadTemplate('footer');
