<?php

/**
 * Popup JViewLegacy
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

JLoader::register('jbsMedia', JPATH_ROOT . '/components/com_biblestudy/lib/biblestudy.media.class.php');
JLoader::register('jbsImages', JPATH_ROOT . '/components/com_biblestudy/lib/biblestudy.images.class.php');

// This is the popup window for the teachings.  We could put anything in this window.

/**
 * View class for Popup
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyViewpopup extends JViewLegacy {

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise a JError object.
     *
     * @see     fetch()
     * @since   11.1
     */
    public function display($tpl = null) {
        /*
         * Load Helpers
         */
        JViewLegacy::loadHelper('scripture');
        JViewLegacy::loadHelper('date');
        JViewLegacy::loadHelper('duration');
        JViewLegacy::loadHelper('params');

        JRequest::setVar('tmpl', 'component');
        $mediaid = JRequest::getInt('mediaid', '', 'get');
        $close = JRequest::getInt('close', '0', 'get');
        $this->player = JRequest::getInt('player', '1', 'get');

        /*
         *  If this is a direct new window then all we need to do is perform hitPlay and close this window
         */
        if ($close == 1) {
            echo JHTML::_('content.prepare', '<script language="javascript" type="text/javascript">window.close();</script>');
        }

        $document = JFactory::getDocument();

        $document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');

        jimport('joomla.application.component.helper');

        $getMedia = new jbsMedia();
        $this->media = $getMedia->getMediaRows2($mediaid);
        $template = BsmHelper::getTemplateparams();

        /*
         *  Convert parameter fields to objects.
         */
        $registry = new JRegistry;
        $registry->loadString($template->params);
        $this->params = $registry;

        /*
         *  Convert parameter fields to objects.
         */
        $registry = new JRegistry;
        $registry->loadString($this->media->params);
        $this->params->merge($registry);

        $css = $this->params->get('css', 'biblestudy.css');
        if ($css != '-1'):
            $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
        endif;

        $saveid = $this->media->id;
        $this->media->id = $this->media->study_id;
        $this->scripture = getScripture($this->params, $this->media, $esv = '0', $scripturerow = '1');
        $this->media->id = $saveid;
        $this->date = getstudyDate($this->params, $this->media->studydate);
        /*
         *  The popup window call the counter function
         */
        $getMedia->hitPlay($mediaid);
        $this->lenght = getDuration($this->params, $this->media);
        //$badchars = array("'", '"');
//$studytitle = str_replace($badchars, ' ', $this->media->studytitle);
//$studyintro = str_replace($badchars, ' ', $this->media->studyintro);
        $images = new jbsImages();
        $seriesimage = $images->getSeriesThumbnail($this->media->series_thumbnail);
        $this->series_thumbnail = '<img src="' . JURI::base() . $seriesimage->path . '" width="' . $seriesimage->width . '" height="' . $seriesimage->height . '" alt="' . $this->media->series_text . '" />';
        $image = $images->getTeacherThumbnail($this->media->teacher_thumbnail, $this->media->thumb);
        $this->teacherimage = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $this->media->teachername . '" />';
        $this->path1 = $this->media->spath . $this->media->fpath . $this->media->filename;
        if (preg_match('@^(?:http://)?([^/]+)@i', $this->path1)) {
            $this->path1 = 'http://' . $this->path1;
        }
        $this->playerwidth = $this->params->get('player_width');
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
        $this->backcolor = $this->params->get('backcolor', '0x287585');
        $this->frontcolor = $this->params->get('frontcolor', '0xFFFFFF');
        $this->lightcolor = $this->params->get('lightcolor', '0x000000');
        $this->screencolor = $this->params->get('screencolor', '0xFFFFFF');
        if ($this->params->get('autostart', 1) == 1) {
            $this->autostart = 'true';
        } else {
            $this->autostart = 'false';
        }
        if ($this->params->get('playeridlehide')) {
            $this->playeridlehide = 'true';
        } else {
            $this->playeridlehide = 'false';
        }
        if ($this->params->get('autostart') == 1) {
            $this->autostart = 'true';
        } elseif ($this->params->get('autostart') == 2) {
            $this->autostart = 'false';
        }
        $this->headertext = $this->titles($this->params->get('popuptitle'), $this->media, $this->scripture, $this->date, $this->lenght);
        if ($this->params->get('itempopuptitle')) {
            $this->headertext = $this->titles($this->params->get('itempopuptitle'), $this->media, $this->scripture, $this->date, $this->lenght);
        }
        $this->footertext = $this->titles($this->params->get('popupfooter'), $this->media, $this->scripture, $this->date, $this->lenght);
        if ($this->params->get('itempopupfooter')) {
            $this->footertext = $this->titles($this->params->get('itempopupfooter'), $this->media, $this->scripture, $this->date, $this->lenght);
        }

        parent::display($tpl);
    }

    /**
     * Set Titles
     * @param string $text
     * @param string $this->media
     * @param string $scripture
     * @param string $date
     * @param string $length
     * @return object
     */
    private function titles($text, $media, $scripture, $date, $length) {
        if (isset($media->teachername)) {
            $text = str_replace('{{teacher}}', $media->teachername, $text);
        }
        if (isset($date)) {
            $text = str_replace('{{studydate}}', $date, $text);
        }
        if (isset($media->filename)) {
            $text = str_replace('{{filename}}', $media->filename, $text);
        }
        if (isset($media->studyintro)) {
            $text = str_replace('{{description}}', $media->studyintro, $text);
        }
        if (isset($length)) {
            $text = str_replace('{{length}}', $length, $text);
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

//end of class
