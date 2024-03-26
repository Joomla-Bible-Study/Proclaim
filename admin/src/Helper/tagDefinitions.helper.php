<?php

/**
 * Set Definition for tags
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// No Direct Access
defined('_JEXEC') or die();

$BsmTmplTags = array(
    '[studyDate]'  => array(
        'method' => 'studyDate',
        'type'   => 'data',
        'db'     => 'studydate'
    ),
    '[filterBook]' => array(
        'method' => 'filterBook',
        'type'   => 'generic',
        'db'     => null
    )
);
