<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Scripture Show class.
 *
 * @package  Proclaim.Site
 * @since    7.1.0
 */
class Cwmshowscripture
{
    /** @var  string Link
     * @since 7.1
     */
    public string $link;

    /**
     * Passage Build system
     *
     * @param   object    $row     Item Info
     * @param   Registry  $params  Item Params
     *
     * @return string|bool
     *
     * @since    7.1
     */
    public function buildPassage($row, Registry $params): string|bool
    {
        if (empty($row->bookname)) {
            return false;
        }

        $reference  = $this->formReference($row);
        $version    = $params->get('bible_version', '77');
        $this->link = $this->getBiblegateway($reference, $version, $params);
        $choice     = (int) $params->get('show_passage_view');
        $passage    = '';
        $css        = false;

        switch ($choice) {
            case 1:
                $passage = $this->getHideShow();
                $css     = true;
                break;

            case 2:
                $passage = $this->getShow();
                $css     = true;
                break;

            case 3:
                $passage = $this->getLink($params);
                break;
        }

        if ($css) {
            HtmlHelper::_('stylesheet', 'media/com_proclaim/css/biblegateway-print.css', ['relative' => true]);
        }

        return $passage;
    }

    /**
     * Create Form of Reference
     *
     * @param   object  $row  ?
     *
     * @return string
     *
     * @since    7.1
     */
    public function formReference($row): string
    {
        $book = str_replace(' ', '+', Text::_($row->bookname));
        $reference = $book . '+' . $row->chapter_begin;

        if (!empty($row->verse_begin)) {
            $reference .= ':' . $row->verse_begin;
        }

        if (!empty($row->chapter_end) && !empty($row->verse_end)) {
            $reference .= '-' . $row->chapter_end . ':' . $row->verse_end;
        } elseif (!empty($row->verse_end)) {
            $reference .= '-' . $row->verse_end;
        }

        return $reference;
    }

    /**
     * Get Bible Gateway References
     *
     * @param   string    $reference  Search string
     * @param   string    $version    Bible Version
     * @param   Registry  $params     Parameters
     *
     * @return string
     *
     * @since    7.1
     */
    public function getBiblegateway($reference, $version, $params): string
    {
        return "https://www.biblegateway.com/passage/?search=" . $reference . "&version=" . $version . "&interface=print";
    }

    /**
     * Get HideShow
     *
     * @return string
     *
     * @since    7.1
     */
    public function getHideShow(): string
    {
        $id = 'scripture_' . uniqid('', true);
        $passage = '<div class="fluid-row"><div class="col-12"></div>';
        $passage .= '<a class="heading" href="#" onclick="var e = document.getElementById(\'' . $id . '\'); e.style.display = (e.style.display == \'none\' ? \'block\' : \'none\'); return false;">' 
            . Text::_('JBS_CMN_SHOW_HIDE_SCRIPTURE') . '</a>';
        $passage .= '<div id="' . $id . '" style="display: none;">';
        $passage .= '<iframe src="' . $this->link . '" width="100%" height="400" style="border:0;"></iframe>';
        $passage .= '</div>';
        $passage .= '</div>';

        return $passage;
    }

    /**
     * Get Show
     *
     * @return string
     *
     * @since    7.1
     */
    public function getShow(): string
    {
        return '<div class="passage"><iframe src="' . $this->link . '" width="100%" height="400" style="border:0;"></iframe></div>';
    }

    /**
     * Get Link
     *
     * @param   Registry  $params  Parameters
     *
     * @return string
     *
     * @since    7.1
     */
    public function getLink(Registry $params): string
    {
        $passage = '<div class="passage">';
        $passage .= '<a href="' . $this->link . '" ';
        $passage .= "onclick=\"window.open(this.href,'mywindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,";
        $passage .= "resizable=yes,width=800,height=500'); return false;\"";
        $passage .= ' title="' . Text::_('JBS_STY_CLICK_TO_OPEN_PASSAGE') . '">';

        if ((int) $params->get('showpassage_icon') === 1) {
            $passage .= '<i class="fas fa-bible fa-3x" style="display: flex; margin-right: 10px;"></i>';
        } elseif ($params->get('showpassage_icon') > 0) {
            $passage .= Text::_('JBS_STY_CLICK_TO_OPEN_PASSAGE');
        }

        $passage .= '</a></div>';

        return $passage;
    }

    /**
     * Only Return the Body of a html doc.
     *
     * @param   string  $html  Html document
     *
     * @return string
     *
     * @since 8.0.0
     */
    public function bodyOnly(string $html): string
    {
        return '<div class="passage"><iframe src="' . $this->link . '" width="100%" height="400" style="border:0;"></iframe></div>';
    }
}
