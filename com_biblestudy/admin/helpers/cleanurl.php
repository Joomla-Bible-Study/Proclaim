<?php

/**
 * Strip Filter Helper
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * Filter Class
 * @package BibleStudy.admin
 * @since 7.1.0
 */
class Filter {

    const BadTagshtml = '<title><link><mata>';

    /**
     * Strip HTML Tags out.
     * @param string $str
     * @param string $tags
     * @param boolean $stripContent
     * @return string
     * @since 7.1.0
     * @todo need to work this some more but work now.
     */
    public static function strip_only($str, $tags = self::BadTagshtml, $stripContent = TRUE) {
        $content = '';
        if (!is_array($tags)) {
            $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
            if (end($tags) == '')
                array_pop($tags);
        }
        foreach ($tags as $tag) {
            if ($stripContent)
                $content = '(.+</' . $tag . '[^>]*>|)';
            $str = preg_replace('#</?' . $tag . '[^>]*>' . $content . '#is', '', $str);
        }
        return $str;
    }

}