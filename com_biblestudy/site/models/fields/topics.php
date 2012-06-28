<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		BibleStudy.Site
 * @subpackage	Form
 * @since		7.0.0
 */
class JFormFieldTopics extends JFormField {

    /**
     *
     * @var type
     */
    public $type = 'Topics';

    /**
     *
     * @return string
     */
    protected function getInput() {

        return '
            <input type="hidden" id="topics" name="jform[topics]"/>
            ';
    }

}

