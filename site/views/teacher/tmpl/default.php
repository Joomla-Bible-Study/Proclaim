<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

if ($this->params->get('teachertemplate'))
{
	echo $this->loadTemplate($this->params->get('teachertemplate'));
}
else
{
	echo $this->loadTemplate('main');
}
