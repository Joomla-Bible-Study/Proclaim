<?php
/**
 * Default View
 *
 * @package     BibleStudy
 * @subpackage  mod_biblestudy
 * @copyright   (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

if ($this->params->get('useexpert_module') > 0)
{
	echo $this->loadTemplate('custom');
}
elseif ($this->params->get('moduletemplate'))
{
	echo $this->loadTemplate($this->params->get('moduletemplate'));
}
else
{
	echo $this->loadTemplate('main');
}
