<?php

/**
 * BibleGateway Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once BIBLESTUDY_PATH_ADMIN_HELPERS . DIRECTORY_SEPARATOR . 'cleanurl.php';

/**
 * Scripture Show class.
 *
 * @package BibleStudy.Site
 * @since 7.1.0
 */
class showScripture {

    /**
     * Passage Build system
     *
     * @param string $row
     * @param array $params
     * @return boolean
     */
    function buildPassage($row, $params) {
        if (!$row->bname) {
            return false;
        }
        $reference = $this->formReference($row);
        $version = $params->get('bible_version', '77');
        $this->link = $this->getBiblegateway($reference, $version);
        $choice = $params->get('show_passage_view');
        switch ($choice) {
            case 0:
                $passage = '';

                break;

            case 1:
                $passage = $this->getHideShow($row, $reference);

                break;

            case 2:
                $passage = $this->getShow($row, $reference);

                break;

            case 3:
                $passage = $this->getLink($row, $reference);

                break;
        }
        return $passage;
    }

    /**
     * Get HideShow
     *
     * @param string $row
     * @param string $reference
     * @return string
     */
    function getHideShow($row, $reference) {
        $webpage = Filter::strip_only(file_get_contents($this->link));
        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblegateway-print.css');
        $passage = '<div class="passage">';
        $passage .= '<a class="heading" href="javascript:ReverseDisplay(\'scripture\')">>>' . JText::_('JBS_CMN_SHOW_HIDE_SCRIPTURE') . '<<</a>';
        $passage .= '<div id="scripture" style="display: none;">';
        $passage .= $webpage;
        $passage .= '</div>';
        $passage .= '</div>';
        return $passage;
    }

    /**
     * Get Show
     *
     * @param string $row
     * @param string $reference
     * @return string
     */
    function getShow($row, $reference) {
        $webpage = Filter::strip_only(file_get_contents($this->link));
        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblegateway-print.css');
        $passage = '<div class = "passage">' . $webpage . '</div>';
        return $passage;
    }

    /**
     * Get Link
     *
     * @param type $row
     * @param type $reference
     * @return string
     */
    function getLink($row, $reference) {
        $passage = '<div class = passage>';
        //$passage .= '<a href="#" onclick="';
        $passage .= '<a href="' . $this->link . '"';
        $passage .=" onclick=\"window.open(this.href,'mywindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=800,height=500'); return false;";
        // $rel = "{handler: 'iframe', size: {x: 800, y: 500}}";
        $passage .= '">' . JText::_('JBS_STY_CLICK_TO_OPEN_PASSAGE') . '</a>';
        $passage .= '</div>';
        return $passage;
    }

    /**
     * Create Form of Reference
     *
     * @param string $row
     * @return string
     */
    function formReference($row) {
        $book = JText::_($row->bname);
        $book = str_replace(' ', '+', $book);
        $book = $book . '+';
        $reference = $book . $row->chapter_begin;
        if ($row->verse_begin) {
            $reference .= ':' . $row->verse_begin;
        }
        if ($row->chapter_end && $row->verse_end) {
            $reference .= '-' . $row->chapter_end . ':' . $row->verse_end;
        }
        if ($row->verse_end && !$row->chapter_end) {
            $reference .= '-' . $row->verse_end;
        }
        return $reference;
    }

    /**
     * Get Bible Gateway References
     *
     * @param string $reference
     * @param string $version
     * @return string
     */
    function getBiblegateway($reference, $version) {
        $link = "http://classic.biblegateway.com/passage/index.php?search=" . $reference . ";&version=" . $version . ";&interface=print";
        return $link;
    }

}