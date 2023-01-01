<?php
/**
 * Default for sermons
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

echo $this->loadTemplate('formheader');
if ($this->params->get('sermonstemplate'))
{
	echo $this->loadTemplate($this->params->get('sermonstemplate'));
}
elseif ($this->params->get('simple_mode') == 1)
{
	if ($this->params->get('simple_mode_template') == 'simple_mode1')
	{
		echo $this->loadTemplate('simple');
	}
	if ($this->params->get('simple_mode_template') == 'simple_mode2')
	{
		echo $this->loadTemplate('simple2');
	}
}
else
{
	echo $this->loadTemplate('main');
}

echo $this->loadTemplate('formfooter');
