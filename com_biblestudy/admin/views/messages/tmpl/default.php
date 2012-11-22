<?php
/**
 * Default
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2012 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

if (BIBLESTUDY_CHECKREL)
{
    echo $this->loadTemplate('30'); 
}
else
{
    echo $this->loadTemplate('25');
}
