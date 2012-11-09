<?php

/**
 * Default
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
?><div id="biblestudy" class="noRefTagger"> <!-- This div is the container for the whole page --><?php
//echo $this->loadTemplate('header');
if ($this->params->get('useexpert_details') > 0) {
    echo $this->loadTemplate('custom');
} elseif ($this->params->get('sermontemplate')) {
    echo $this->loadTemplate($this->params->get('sermontemplate'));
} else {
    echo $this->loadTemplate('main');
}
$show_comments = $this->params->get('show_comments');
if ($show_comments > 1)
        {
            $user = JFactory::getUser();
            $groups = $user->getAuthorisedViewLevels();
            $comment_access = $this->params->get('comment_access');
            if (in_array($show_comments, $groups)) {
                echo $this->loadTemplate('commentsform');
            }
}
echo $this->loadTemplate('footerlink');

?></div>