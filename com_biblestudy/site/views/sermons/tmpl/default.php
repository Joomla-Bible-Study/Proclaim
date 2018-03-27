<?php
/**
 * Default for sermons
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;
JHtml::_('behavior.modal');

if ($this->params->get('simple_mode') == 1)
{
	echo $this->loadTemplate('simple');
}
else
{
	echo $this->loadTemplate('formheader');

	if ($this->params->get('sermonstemplate'))
	{
		echo $this->loadTemplate($this->params->get('sermonstemplate'));
	}
	else
	{
		echo $this->loadTemplate('main');
	}
	echo $this->loadTemplate('formfooter');
}