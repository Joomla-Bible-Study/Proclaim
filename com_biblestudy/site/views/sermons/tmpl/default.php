<?php
/**
 * Default for sermons
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
JHtml::_('behavior.modal');

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
