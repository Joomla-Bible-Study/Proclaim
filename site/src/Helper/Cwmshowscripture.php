<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
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
     * @return string|boolean
     *
     * @since    7.1
     */
    public function buildPassage($row, Registry $params)
    {
        if (!$row->bookname) {
            return false;
        }

        $reference  = $this->formReference($row);
        $version    = $params->get('bible_version', '77');
        $this->link = $this->getBiblegateway($reference, $version, $params);
        $choice     = $params->get('show_passage_view');
        $passage    = null;
        $css        = false;

        switch ($choice) {
            case 0:
                $passage = '';

                break;

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
            HtmlHelper::_('stylesheet', Uri::base() . 'media/com_proclaim/css/biblegateway-print.css');
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
        $book      = Text::_($row->bookname);
        $book      = str_replace(' ', '+', $book);
        $book      .= '+';
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
        return "https://classic.biblegateway.com/passage/index.php?search=" . $reference . ";&version=" . $version . ";&interface=print";
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
        $contents = '<iframe id="scripture" src="' . $this->link . '" width="100%" height="400px;"></iframe>';
        $passage  = '<div class = "fluid-row"><div class="span12"></div>';
        $passage  .= '<a class="heading" href="javascript:ReverseDisplay(\'scripture\')">>>' . Text::_(
                'JBS_CMN_SHOW_HIDE_SCRIPTURE'
            ) . '<<</a>';
        $passage  .= '<div id="scripture" style="display: none;">';
        $passage  .= $contents;
        $passage  .= '</div>';
        $passage  .= '</div>';

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
        $contents = '<iframe id = "scripture" src = "' . $this->link . '" width = "100%" height = "400px;" ></iframe >';

        return '<div class = "passage">' . $contents . '</div>';
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
        $passage = '<div class = passage>';

        // $passage .= '<a href="#" onclick="';
        $passage .= '<a href="' . $this->link . '" ';
        $passage .= "onclick=\"window.open(this.href,'mywindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,";
        $passage .= "resizable=yes,width=800,height=500');";
        $passage .= "return false;";

        // $rel = "{handler: 'iframe', size: {x: 800, y: 500}}";
        $passage .= '" title="' . Text::_('JBS_STY_CLICK_TO_OPEN_PASSAGE') . '">'; ?>
        <?php
        if ($params->get('showpassage_icon') > 0) {
            if ((int)$params->get('showpassage_icon') === 1) {
                $passage .= '<i class="fas fa-bible fa-3x" style="display: flex; margin-right: 10px;"></i>';
            } else {
                $passage .= Text::_('JBS_STY_CLICK_TO_OPEN_PASSAGE');
            }
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
    public function body_only($html): string
    {
        return '<iframe id = "scripture" src = "' . $this->link . '" width = "100%" height = "400px;" ></iframe >';
    }
}
