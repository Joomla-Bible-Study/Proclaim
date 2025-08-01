<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmpopup;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Table\CwmtemplateTable;
use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Html\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

// This is the popup window for the teachings.  We could put anything in this window.

/**
 * View class for Popup
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /** @var  int Player
     *
     * @since 7.0
     */
    public int $player;

    /** @var  string Media
     *
     * @since 7.0
     */
    public $media;

    /** @var  array Media info
     *
     * @since 7.0
     */
    public $getMedia;

    /** @var  string Scripture Text
     *
     * @since 7.0
     */
    public string $scripture;

    /** @var  string Date
     *
     * @since 7.0
     */
    public $date;

    /** @var  string Series Thumbnail
     *
     * @since 7.0
     */
    public $series_thumbnail;

    /** @var  string Teacher Image
     *
     * @since 7.0
     */
    public $teacherimage;

    /** @var  string Path 1
     *
     * @since 7.0
     */
    public $path1;

    /** @var  string Width
     *
     * @since 7.0
     */
    public $playerwidth;

    /** @var  string Player Height
     *
     * @since 7.0
     */
    public $playerheight;

    /** @var  string Flash Vars
     *
     * @since 7.0
     */
    public $flashvars;

    /** @var  string Back Color
     *
     * @since 7.0
     */
    public $backcolor;

    /** @var  string Front Color
     *
     * @since 7.0
     */
    public $frontcolor;

    /** @var  string Light Color
     *
     * @since 7.0
     */
    public $lightcolor;

    /** @var  string Screen Color
     *
     * @since 7.0
     */
    public $screencolor;

    /** @var  string Auto Start
     *
     * @since 7.0
     */
    public $autostart;

    /** @var  string Player Idle Hide
     *
     * @since 7.0
     */
    public $playeridlehide;

    /** @var  string Header Text
     *
     * @since 7.0
     */
    public $headertext;

    /** @var  string Footer Text
     *
     * @since 7.0
     */
    public $footertext;

    /** @var  Registry Params
     *
     * @since 7.0
     */
    protected $params;

    /** @var  Registry Params
     *
     * @since 7.0
     */
    protected $state;

    /** @var  Registry Extra Params
     *
     * @since 7.0
     */
    protected $extraparams;

    /** @var  CwmtemplateTable Template
     *
     * @since 7.0
     */
    protected $template;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws Exception
     * @since 7.0
     */
    public function display($tpl = null): void
    {
        $input = Factory::getApplication()->input;
        $input->get('tmpl', 'component', 'string');
        $mediaid      = $input->get('mediaid', '', 'int');
        $close        = $input->get('close', '0', 'int');
        $this->player = $input->get('player', '1', 'int');

        /*
         *  If this is a direct new window, then all we need to do is perform hitPlay and close this window
         */
        if ($close === 1) {
            echo HtmlHelper::_(
                'content.prepare',
                '<script language="javascript" type="text/javascript">window.close();</script>'
            );
        }

        $this->getMedia = new Cwmmedia();
        $this->media    = $this->getMedia->getMediaRows2($mediaid);
        $this->state    = $this->get('state');
        $this->template = $this->state->get('template');

        /*
         *  Convert parameter fields to objects.
         */
        $registry = new Registry();
        $registry->loadString($this->template->params);
        $this->params = $registry;

        $registry = new Registry();
        $registry->loadString($this->media->sparams);
        $this->params->merge($registry);
        $this->media->sparams = $registry;
        $registry             = new Registry();
        $registry->loadString($this->media->params);
        $this->params->merge($registry);
        $saveid          = $this->media->id;
        $this->media->id = $this->media->study_id;
        $JBSMListing     = new Cwmlisting();
        $this->scripture = $JBSMListing->getScripture($this->params, $this->media, 0, 1);
        $this->media->id = $saveid;
        $this->date      = $JBSMListing->getStudyDate($this->params, $this->media->studydate);

        /*
         *  The popup window calls the counter function
         */
        $this->getMedia->hitPlay((int)$mediaid);

        $seriesImage            = Cwmimages::getSeriesThumbnail($this->media->series_thumbnail);
        if ($seriesImage->path) {
            $this->series_thumbnail = '<img src="' . Uri::base() . $seriesImage->path . '" width="' . $seriesImage->width . '" height="'
                . $seriesImage->height . '" alt="' . $this->media->series_text . '" />';
        }
        $image                  = Cwmimages::getTeacherThumbnail($this->media->teacher_thumbnail, $this->media->thumb);
        if ($image->path) {
            $this->teacherimage = '<img src="' . Uri::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height
                . '" alt="' . $this->media->teachername . '" />';
        }

        $this->path1 = Cwmhelper::mediaBuildUrl(
            $this->media->spath,
            $this->params->get('filename'),
            $this->params,
            true
        );

        $this->playerwidth  = $this->params->get('player_width');
        $this->playerheight = $this->params->get('player_height');

        if ($this->params->get('playerheight') < 55 && $this->params->get('playerheight')) {
            $this->playerheight = 55;
        } elseif ($this->params->get('playerheight')) {
            $this->playerheight = $this->params->get('playerheight');
        }

        if ($this->params->get('playerwidth')) {
            $this->playerwidth = $this->params->get('playerwidth');
        }

        if ($this->params->get('playervars')) {
            $this->extraparams = $this->params->get('playervars');
        }

        if ($this->params->get('altflashvars')) {
            $this->flashvars = $this->params->get('altflashvars');
        }

        if ($this->player === 100) {
            $this->player = (int) $this->template->params->get('player', "0");
        }

        $this->backcolor   = $this->params->get('backcolor', '0x287585');
        $this->frontcolor  = $this->params->get('frontcolor', '0xFFFFFF');
        $this->lightcolor  = $this->params->get('lightcolor', '0x000000');
        $this->screencolor = $this->params->get('screencolor', '0xFFFFFF');

        if ($this->params->get('autostart', "1") === "1") {
            $this->autostart = 'true';
        } else {
            $this->autostart = 'false';
        }

        if ($this->params->get('playeridlehide')) {
            $this->playeridlehide = 'true';
        } else {
            $this->playeridlehide = 'false';
        }

        if ($this->params->get('autostart') === "1") {
            $this->autostart = 'true';
        } elseif ($this->params->get('autostart') === "2") {
            $this->autostart = 'false';
        }

        $this->headertext = $this->titles(
            $this->params->get('popuptitle'),
            $this->media,
            $this->scripture,
            $this->date
        );

        if ($this->params->get('itempopuptitle')) {
            $this->headertext = $this->titles(
                $this->params->get('itempopuptitle'),
                $this->media,
                $this->scripture,
                $this->date
            );
        }

        $this->footertext = $this->titles(
            $this->params->get('popupfooter'),
            $this->media,
            $this->scripture,
            $this->date
        );

        if ($this->params->get('itempopupfooter')) {
            $this->footertext = $this->titles(
                $this->params->get('itempopupfooter'),
                $this->media,
                $this->scripture,
                $this->date
            );
        }

        parent::display($tpl);
    }

    /**
     * Set Titles
     *
     * @param   string  $text       Text info
     * @param   object  $media      Media info
     * @param   string  $scripture  scripture
     * @param   string  $date       Date
     *
     * @return string
     *
     * @since 7.0
     */
    private function titles(string $text, object $media, string $scripture, string $date): string
    {
        if (isset($media->teachername)) {
            $text = str_replace('{{teacher}}', $media->teachername, $text);
        }


        $text = str_replace('{{studydate}}', $date, $text);


        if ($this->params->get('filename')) {
            $text = str_replace('{{filename}}', $this->params->get('filename'), $text);
        }

        if (isset($media->studyintro)) {
            $text = str_replace('{{description}}', $media->studyintro, $text);
        }

        if (isset($media->studytitle)) {
            $text = str_replace('{{title}}', $media->studytitle, $text);
        }

        if (isset($scripture)) {
            $text = str_replace('{{scripture}}', $scripture, $text);
        }

        if (isset($this->teacherimage)) {
            $text = str_replace('{{teacherimage}}', $this->teacherimage, $text);
        }

        if (isset($media->series_text)) {
            $text = str_replace('{{series}}', $media->series_text, $text);
        }

        if (isset($media->series_thumbnail)) {
            $text = str_replace('{{series_thumbnail}}', $this->series_thumbnail, $text);
        }

        return $text;
    }
}
