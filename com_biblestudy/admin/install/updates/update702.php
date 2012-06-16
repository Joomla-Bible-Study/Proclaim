<?php

/**
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
defined('_JEXEC') or die;

/**
 * Inserts some css code to fix pagination problem and add a tag for the captcha of comments
 * @package BibleStudy.Admin
 * @since 7.0.2
 */
class JBS702Update {

    /**
     * Update CSS for 7.0.2
     * @return boolean
     */
    function css702() {
        $newcss = '#main ul, #main li
{
display: inline;
}

.component-content ul
{
text-align: center;
}

.component-content li
{
display: inline;
}

.pagenav
{
margin-left: 10px;
margin-right: 10px;
}

#recaptcha_widget_div {
position:static !important;}';

        $csscheck = '#main ul, #main li';

        $dest = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'biblestudy.css';
        $cssexists = JFile::exists($dest);
        if ($cssexists) {
            $cssread = JFile::read($dest);

            $csstest = substr_count($cssread, $csscheck);
            if (!$csstest) {
                $cssread = $cssread . $newcss;
            }

            if (!JFile::write($dest, $cssread)) {
                return false;
            } else {
                return true;
            }
        }
    }

}
