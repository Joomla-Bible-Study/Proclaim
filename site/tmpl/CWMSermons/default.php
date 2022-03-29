<?php
/**
 * Default for sermons
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
use Joomla\CMS\HTML\HTMLHelper;
// No Direct Access
defined('_JEXEC') or die;
HTMLHelper::_('bootstrap.modal');
	echo $this->loadTemplate('formheader');
//var_dump($this->params->get('sermonstemplate'));
	if ($this->params->get('sermonstemplate'))
	{
		echo $this->loadTemplate($this->params->get('sermonstemplate'));
	}
	elseif ($this->params->get('simple_mode') == 1)
	{
		echo $this->loadTemplate('simple');
	}
	else
	{
		echo $this->loadTemplate('main');
	}
	echo $this->loadTemplate('formfooter');
?>

