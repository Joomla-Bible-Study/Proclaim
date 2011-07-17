<?php

/**
 * @version		$Id: docman.php 1284 2011-01-04 07:57:59Z genu $
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldTopics extends JFormField {

    public $type = 'Topics';

    protected function getInput() {

        return '
            <input type="hidden" id="topics" name="jform[topics]"/>
            ';
    }

}

