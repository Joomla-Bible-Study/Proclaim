<?php

/**
 * _Study
 *
 * @package     BibleStudy
 * @subpackage  Model.BibleStudy
 * @copyright   2010-2011 Joomla Bible Study
 * @license     GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
$row = $study;
//FIXME not working;

JLoader::register('JBSMListing', BIBLESTUDY_PATH_LIB . '/biblestudy.listing.class.php');
$JBSMListing = new JBSMListing;
$listing     = $JBSMListing->getListing($row, $params, $oddeven, $admin_params, $template, $ismodule);
echo $listing;
