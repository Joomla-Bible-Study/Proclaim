<?php

/**
 * @package BibleStudy
 * @subpackage Model.BibleStudy
 * @copyright            2010-2011
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

$path1 = JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy/helpers/';
$row = $study;
include_once($path1 . 'listing.php');
$listing = getListing($row, $params, $oddeven);
echo $listing;