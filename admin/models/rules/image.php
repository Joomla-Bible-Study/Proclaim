<?php
/**
* Part of Proclaim Package
*
* @package    Proclaim.Admin
* @copyright  2007 - 2021 (C) CWM Team All rights reserved
* @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @link       https://www.christianwebministries.org
**/
// No Direct Access
defined('_JEXEC') or die;

class JFormRuleImage extends JFormRule
{

    public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
    {
        if ($value != 'x') {
            return true;
        }
        return false;
    }
}