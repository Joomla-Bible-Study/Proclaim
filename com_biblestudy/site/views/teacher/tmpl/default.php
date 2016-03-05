<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

if ($this->params->get('useexpert_teacherdetail') > 0)
{
	echo $this->loadTemplate('custom');
}
elseif ($this->params->get('teachertemplate'))
{
	echo $this->loadTemplate($this->params->get('teachertemplate'));
}
else
{
	echo $this->loadTemplate('main');
}
