<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
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
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
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

    /** @var  object|bool Media
     *
     * @since 7.0
     */
    public object|bool $media;

    /** @var  Cwmmedia Media info
     *
     * @since 7.0
     */
    public Cwmmedia $getMedia;

    /** @var  Cwmlisting Listing info
     *
     * @since 7.0
     */
    public Cwmlisting $listing;

    /** @var  string Scripture Text
     *
     * @since 7.0
     */
    public string $scripture = '';

    /** @var  string Date
     *
     * @since 7.0
     */
    public string $date = '';

    /** @var  string Series Thumbnail
     *
     * @since 7.0
     */
    public string $series_thumbnail = '';

    /** @var  string Teacher Image
     *
     * @since 7.0
     */
    public string $teacherimage = '';

    /** @var  string Path 1
     *
     * @since 7.0
     */
    public string $path1 = '';

    /** @var  string Width
     *
     * @since 7.0
     */
    public string $playerwidth = '';

    /** @var  string Player Height
     *
     * @since 7.0
     */
    public string $playerheight = '';

    /** @var  string Header Text
     *
     * @since 7.0
     */
    public string $headertext = '';

    /** @var  string Footer Text
     *
     * @since 7.0
     */
    public string $footertext = '';

    /** @var  Registry Params
     *
     * @since 7.0
     */
    protected Registry $params;

    /** @var  Registry Params
     *
     * @since 7.0
     */
    protected Registry $state;


    /** @var  CwmtemplateTable Template
     *
     * @since 7.0
     */
    protected CwmtemplateTable $template;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws \Exception
     * @since 7.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $mediaid      = $input->get('mediaid', '', 'int');
        $close        = $input->get('close', '0', 'int');
        $this->player = $input->get('player', '1', 'int');

        /*
         *  If this is a direct new window, then all we need to do is perform hitPlay and close this window
         */
        if ($close === 1) {
            $app->getDocument()->getWebAssetManager()
                ->addInlineScript('window.close();');
        }

        $this->getMedia = new Cwmmedia();
        $this->media    = $this->getMedia->getMediaRows2($mediaid);

        $this->state    = $this->getModel()->getState();
        $this->template = $this->state->get('template');

        // Template params already a Registry (merged with app + admin params in model)
        $this->params = clone $this->template->params;

        // Merge server and media params on top
        $this->media->sparams = new Registry($this->media->sparams ?? '');
        $this->params->merge($this->media->sparams);
        $mediaParams = new Registry($this->media->params ?? '');
        $this->params->merge($mediaParams);
        $saveid          = $this->media->id;
        $this->media->id = $this->media->study_id;
        $JBSMListing     = new Cwmlisting();
        $this->listing   = $JBSMListing;
        $this->scripture = $JBSMListing->getScripture($this->params, $this->media, 0, 1);
        $this->media->id = $saveid;
        $this->date      = $JBSMListing->getStudyDate($this->params, $this->media->studydate);

        /*
         *  The popup window calls the counter function
         */
        $this->getMedia->hitPlay((int)$mediaid);

        $seriesImage = Cwmimages::getSeriesThumbnail($this->media->series_thumbnail);
        if ($seriesImage->path) {
            $this->series_thumbnail = Cwmimages::renderPicture($seriesImage, $this->media->series_text ?? '');
        }
        $image = Cwmimages::getTeacherThumbnail($this->media->teacher_thumbnail, $this->media->thumb);
        if ($image->path) {
            $this->teacherimage = Cwmimages::renderPicture($image, $this->media->teachername ?? '');
        }

        $this->path1 = Cwmhelper::mediaBuildUrl(
            $this->media->spath,
            $this->params->get('filename'),
            $this->params,
            true
        );

        $this->playerwidth  = (string) ($this->params->get('player_width') ?? '');
        $this->playerheight = (string) ($this->params->get('player_height') ?? '');

        if ($this->player === 100) {
            $this->player = (int) $this->params->get('player', '0');
        }

        $this->headertext = $this->titles(
            (string) ($this->params->get('popuptitle') ?? ''),
            $this->media,
            $this->scripture,
            $this->date
        );

        if ($this->params->get('itempopuptitle')) {
            $this->headertext = $this->titles(
                (string) $this->params->get('itempopuptitle'),
                $this->media,
                $this->scripture,
                $this->date
            );
        }

        $this->footertext = $this->titles(
            (string) ($this->params->get('popupfooter') ?? ''),
            $this->media,
            $this->scripture,
            $this->date
        );

        if ($this->params->get('itempopupfooter')) {
            $this->footertext = $this->titles(
                (string) $this->params->get('itempopupfooter'),
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

        $text = str_replace('{{scripture}}', $scripture, $text);

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
