<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

if ($this->params->get('useexpert_seriesdetail') > 0)
{
	echo $this->loadTemplate('custom');
}
elseif ($this->params->get('seriesdisplaytemplate'))
{
	echo $this->loadTemplate($this->params->get('seriesdisplaytemplate'));
}
else
{
	echo $this->loadTemplate('main');
}
