<?php
/**
 * Default for sermons
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
JHTML::_('behavior.modal');

echo $this->loadTemplate('formheader');

if ($this->params->get('useexpert_list') > 0)
{
	echo $this->loadTemplate('custom');
}
elseif ($this->params->get('sermonstemplate'))
{
	echo $this->loadTemplate($this->params->get('sermonstemplate'));
}
else
{
	echo $this->loadTemplate('main');
}
echo $this->loadTemplate('formfooter');
