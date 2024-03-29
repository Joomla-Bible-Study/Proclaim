<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2021 (C) CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Lib;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Convert Class
 * PreachIT Converter system
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class CwmpIconvert
{
    /**
     * Array of Comments Id's
     *
     * @var array
     *
     * @since 9.0.0
     */
    public $commentsids;

    /**
     * Array of Servers Id's
     *
     * @var array
     *
     * @since 9.0.0
     */
    public $serversids;

    /**
     * Array of Folders Id's
     *
     * @var array
     *
     * @since 9.0.0
     */
    public $foldersids;

    /**
     * Array of Studies Id's
     *
     * @var array
     *
     * @since 9.0.0
     */
    public $studiesids;

    /**
     * Array of Media-Files Id's
     *
     * @var array
     *
     * @since 9.0.0
     */
    public $mediafilesids;

    /**
     * Array of Teachers Id's
     *
     * @var array
     *
     * @since 9.0.0
     */
    public $teachersids;

    /**
     * Array of Series Id's
     *
     * @var array
     *
     * @since 9.0.0
     */
    public $seriesids;

    /**
     * Array of Podcasts Id's
     *
     * @var array
     *
     * @since 9.0.0
     */
    public $podcastids;

    /**
     * Array of Locations
     *
     * @var array
     *
     * @since 9.0.0
     */
    public $locations;

    /**
     * ???
     *
     * @var int
     *
     * @since 9.0.0
     */
    public $cnoadd;

    /**
     * Can Add switch
     *
     * @var int
     *
     * @since 9.0.0
     */
    public $cadd;

    /**
     * Comment object
     *
     * @var object
     *
     * @since 9.0.0
     */
    public $picomments;

    /**
     * Number of Podcasts
     *
     * @var int
     *
     * @since 9.0.0
     */
    public $podcasts;

    /**
     * Convert PreachIT
     *
     * @return string
     *
     * @since 7.1.0
     */
    public function convertPI()
    {
        // Check for request forgeries.
        Session::checkToken('get') or Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $this->commentsids = array();
        $this->serversids = array();
        $this->foldersids = array();
        $this->mediafilesids = array();
        $this->studiesids = array();
        $this->teachersids = array();
        $this->seriesids = array();
        $this->podcastids = array();
        $this->locations = array();
        $this->cnoadd = 0;
        $this->cadd = 0;
        $this->fnoadd = 0;
        $this->fadd = 0;
        $this->tnoadd = 0;
        $this->tadd = 0;
        $this->srnoadd = 0;
        $this->sradd = 0;
        $this->pnoadd = 0;
        $this->padd = 0;
        $this->lnoadd = 0;
        $this->ladd = 0;
        $this->snoadd = 0;
        $this->sadd = 0;
        $this->mnoadd = 0;
        $this->madd = 0;
        $newid = 0;
        $oldid = 0;

        //drop sample table entries in Proclaim!
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->truncateTable('#__bsms_studies');
        $db->truncateTable('#__bsms_teachers');
        $db->truncateTable('#__bsms_mediafiles');
        $db->truncateTable('#__bsms_podcast');
        $db->truncateTable('#__bsms_locations');
        $db->truncateTable('#__bsms_series');

        //edit servers for current url

        $uri = Uri::getInstance();
        $url = $uri->getHost();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*')->from('#__bsms_servers');
        $db->setQuery($query);
        $servers = $db->loadObjectList();
        foreach ($servers as $server) {
            $reg = new Registry();
            $reg->loadString($server->params);
            $reg->set('path', $url);
            $query = $db->getQuery(true);
            $query->update('#__bsms_servers')
                ->set('params = ' . $db->q($reg->toString()))
                ->where('id = ' . (int)$server->id);
            $db->setQuery($query);
            $db->execute();
        }
        //Convert comments
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*')->from('#__picomments');
        $db->setQuery($query);
        $this->picomments = $db->loadObjectList();
        /** @var $piconversion string */
        $piconversion = null;


        // Teachers
        //Create a blank teacher
        $teach = new \stdClass();
        $teach->teachername = 'Not Listed';
        $teach->list_show = 0;
        $teach->access = 1;
        if (!$db->insertObject('#__bsms_teachers', $teach, 'id')) {
            $this->tnoadd++;
        } else {
            $this->tadd++;
            $query = $db->getQuery(true);
            $query->select('id')->from('#__bsms_teachers')->order('id desc');
            $db->setQuery($query, 0, 1);
            $this->genericteacher = $db->loadResult();
        }
        $query = $db->getQuery(true);
        $query->select('*')->from('#__piteachers');
        $db->setQuery($query);
        $piteachers = $db->loadObjectList();

        if (!$piteachers) {
            $this->tnoadd++;
        } else {
            foreach ($piteachers as $pi) {
                $datateachers = new \stdClass();
                $datateachers->id = null;
                $datateachers->teachername = $pi->name . " " . $pi->lastname;
                $datateachers->alias = $pi->alias;
                $datateachers->title = $pi->teacher_title;
                $datateachers->image = $pi->image_folderlrg . $pi->teacher_image_lrg;
                $datateachers->thumb = $pi->image_folderlrg . $pi->teacher_image_lrg;
                $datateachers->email = $pi->email;
                $datateachers->website = $pi->website;
                $datateachers->short = $db->escape($pi->description);
                $datateachers->list_show = $pi->teacher_view;
                $datateachers->published = $pi->published;

                if (!$db->insertObject('#__bsms_teachers', $datateachers, 'id')) {
                    $this->tnoadd++;
                } else {
                    $this->tadd++;

                    // Get the new teacherid so we can later connect it to a study
                    $query = $db->getQuery(true);
                    $query->select('id')->from('#__bsms_teachers')->order('id desc');
                    $db->setQuery($query, 0, 1);
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $this->teachersids[] = array('newid' => $newid, 'oldid' => $oldid);
                }
            }
        }

        // Convert Ministries
        $query = $db->getQuery(true);
        $query->select('*')->from('#__piministry');
        $db->setQuery($query);
        $ministries = $db->loadObjectList();

        if (!$ministries) {
            $piconversion .= '<tr><td>' . Text::_('JBS_IBM_NO_MINISTRIES') . '</td></tr>';
        } else {
            foreach ($ministries as $pi) {
                $locations = new \stdClass();
                $locations->id = null;
                $locations->published = $pi->published;
                $locations->location_text = $pi->name;
                $locations->access = $pi->access;
                $locations->ordering = $pi->ordering;
                $locations->misc = $pi->description;
                $locations->image = $pi->image_folderlrg . $pi->ministry_image_lrg;

                if (!$db->insertObject('#__bsms_locations', $locations, 'id')) {
                    $this->lnoadd++;
                } else {
                    $this->ladd++;

                    // Get the new locationid so we can later connect it to a study
                    $query = $db->getQuery(true);
                    $query->select('id')->from('#__bsms_locations')->order('id desc');
                    $db->setQuery($query, 0, 1);
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $this->locations[] = array('newid' => $newid, 'oldid' => $oldid);
                }
            }
        }


        // Convert Series
        $query = $db->getQuery(true);
        $query->select('*')->from('#__piseries');
        $db->setQuery($query);
        $series = $db->loadObjectList();

        if (!$series) {
            $piconversion .= '<tr><td>' . Text::_('JBS_IBM_NO_SERIES') . '</td></tr>';
        } else {
            foreach ($series as $pi) {
                $dataseries = new \stdClass();
                $dataseries->id = null;
                $dataseries->series_text = $pi->name;
                $dataseries->alias = $pi->alias;
                $dataseries->description = $pi->description;
                $dataseries->series_thumbnail = $pi->image_folderlrg . $pi->series_image_lrg;
                $dataseries->published = $pi->published;

                if (!$db->insertObject('#__bsms_series', $dataseries, 'id')) {
                    $this->srnoadd++;
                } else {
                    $this->sradd++;

                    // Get the new seriesid so we can later connect it to a study
                    $query = $db->getQuery(true);
                    $query->select('id')->from('#__bsms_series')->order('id desc');
                    $db->setQuery($query, 0, 1);
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $this->seriesids[] = array('newid' => $newid, 'oldid' => $oldid);
                }
            }
        }

        // Convert podcacsts
        $query = $db->getQuery(true);
        $query->select('*')->from('#__pipodcast')->where('published = 1');
        $db->setQuery($query);
        $podcasts = $db->loadObjectList();

        if (!$podcasts) {
            $piconversion .= '<tr><td>' . Text::_('JBS_IBM_NO_PODCASTS') . '</td></tr>';
        } else {
            foreach ($podcasts as $pi) {
                $podcast = new \stdClass();
                $podcast->id = null;
                $podcast->title = $pi->name;
                $podcast->website = $pi->website;
                $podcast->description = $pi->description;
                $podcast->image = $pi->image;
                $podcast->imageh = $pi->imagehgt;
                $podcast->imagew = $pi->imagewth;
                $podcast->author = $pi->author;
                $podcast->filename = $pi->filename;
                $podcast->language = $pi->language;
                $podcast->editor_name = $pi->editor;
                $podcast->editor_email = $pi->email;
                $podcast->podcastlimit = $pi->records;
                $podcast->episodetitle = $pi->itunestitle;
                $podcast->detailstemplateid = 1;
                $podcast->published = $pi->published;
                $podcast->podcastsearch = $pi->search;
                $podcast->language = "*";

                if (!$db->insertObject('#__bsms_podcast', $podcast, 'id')) {
                    $this->pnoadd++;
                } else {
                    $this->padd++;

                    // Get the new podcast id so we can later connect it to a study
                    $query = $db->getQuery(true);
                    $query->select('id')->from('#__bsms_podcast')->order('id desc');
                    $db->setQuery($query, 0, 1);
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $this->podcastids[] = array('newid' => $newid, 'oldid' => $oldid);
                }
            }
        }
        // Convert studies and media files
        $books = $this->getBooks();
        $query = $db->getQuery(true);
        $query->select('*')->from('#__pistudies');
        $db->setQuery($query);
        $studies = $db->loadObjectList();

        if (!$studies) {
            $piconversion .= '<tr><td>' . Text::_('JBS_IBM_NO_STUDIES') . '</td></tr>';
        } else {
            foreach ($studies as $pi) {
                $studydate = $pi->date;
                $studytitle = $pi->name;
                $teacher_id = null;
                $t = json_decode($pi->teacher, true);
                foreach ($this->teachersids as $teacher) {
                    if ($teacher['oldid'] == $t['0']) {
                        $teacher_id = $teacher['newid'];
                    } elseif ($t['0'] == 0) {
                        $teacher_id = $this->genericteacher;
                    }
                }

                $studynumber = $pi->id;
                $booknumber = null;
                $booknumber2 = null;

                foreach ($books as $book) {
                    if ($book['id'] == $pi->study_book) {
                        $booknumber = $book['jbs'];
                    } else {
                        $booknumber = '101';
                    }

                    if ($book['id'] == $pi->study_book2) {
                        $booknumber2 = $book['jbs'];
                    }
                }

                $chapter_begin = $pi->ref_ch_beg;
                $chapter_end = $pi->ref_ch_end;
                $verse_begin = $pi->ref_vs_beg;
                $verse_end = $pi->ref_vs_end;
                $chapter_begin2 = $pi->ref_ch_beg2;
                $chapter_end2 = $pi->ref_ch_end2;
                $verse_begin2 = $pi->ref_vs_beg2;
                $verse_end2 = $pi->ref_vs_end2;
                $comments = $pi->comments;
                $hits = $pi->hits;
                $user_id = $pi->user;
                $show_level = $pi->access;
                $location_id = '';
                $l = json_decode($pi->ministry, true);

                foreach ($this->locations as $location) {
                    if ($location['oldid'] == $l['0']) {
                        $location_id = $location['newid'];
                    }
                }

                $alias = $pi->alias;
                $studyintro = $pi->description;
                $series_id = '';

                foreach ($this->seriesids as $series) {
                    if ($series['oldid'] == $pi->series) {
                        $series_id = $series['newid'];
                    }
                }

                $studytext = $db->escape($pi->study_text);
                $imagefolder = 0;
                $newfolder = 0;
                $thumbnailm = '';
                $image = '';

                foreach ($this->foldersids as $folder) {
                    if ($folder['oldid'] == $pi->image_folderlrg) {
                        $imagefolder = $folder['newid'];
                        $image = $pi->imagelrg;
                    }
                }

                if ($imagefolder) {
                    $thumbnailm = $newfolder . $image;
                }

                $published = $pi->published;
                $params = '{"metakey":"' . $pi->metakey . '","metadesc":""}';
                $access = $pi->saccess;

                // Create the study then get the id to create the media file and comments
                $datastudies = new \stdClass();
                $datastudies->id = '';
                $datastudies->published = $published;
                $datastudies->studydate = $studydate;
                $datastudies->studytitle = $studytitle;
                $datastudies->teacher_id = $teacher_id;
                $datastudies->studynumber = $studynumber;
                $datastudies->booknumber = $booknumber;
                $datastudies->booknumber2 = $booknumber2;
                $datastudies->chapter_begin = $chapter_begin;
                $datastudies->chapter_end = $chapter_end;
                $datastudies->verse_begin = $verse_begin;
                $datastudies->verse_end = $verse_end;
                $datastudies->chapter_begin2 = $chapter_begin2;
                $datastudies->chapter_end2 = $chapter_end2;
                $datastudies->verse_begin2 = $verse_begin2;
                $datastudies->verse_end2 = $verse_end2;
                $datastudies->comments = $comments;
                $datastudies->hits = $hits;
                $datastudies->user_id = $user_id;
                $datastudies->show_level = $show_level;
                $datastudies->location_id = $location_id;
                $datastudies->alias = $alias;
                $datastudies->studyintro = $studyintro;
                $datastudies->series_id = $series_id;
                $datastudies->studytext = $studytext;
                $datastudies->thumbnailm = $thumbnailm;
                $datastudies->params = $params;
                $datastudies->access = $access;
                $datastudies->language = '*';

                if (!$db->insertObject('#__bsms_studies', $datastudies, 'id')) {
                    $this->snoadd++;
                } else {
                    $this->sadd++;

                    // Get the new studiesid so we can later connect it to a study
                    $query = $db->getQuery(true);
                    $query->select('id')->from('#__bsms_studies')->order('id desc');
                    $db->setQuery($query, 0, 1);
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $this->studiesids[] = array('newid' => $newid, 'oldid' => $oldid);
                }

                // Create the mediafiles
                if ($pi->audio_link) {
                    if (!$audio = $this->insertMedia($pi, $type = 'audio', $newid, $oldid)) {
                        $this->mnoadd++;
                    } else {
                        $this->madd++;
                    }
                }

                if ($pi->video_link) {
                    if (!$video = $this->insertMedia($pi, $type = 'video', $newid, $oldid)) {
                        $this->mnoadd++;
                    } else {
                        $this->madd++;
                    }
                }

                if ($pi->slides_link) {
                    if (!$slides = $this->insertMedia($pi, $type = 'slides', $newid, $oldid)) {
                        $this->mnoadd++;
                    } else {
                        $this->madd++;
                    }
                }

                if ($pi->notes_link) {
                    if (!$notes = $this->insertMedia($pi, $type = 'notes', $newid, $oldid)) {
                        $this->mnoadd++;
                    } else {
                        $this->madd++;
                    }
                }

                $comments = $this->insertComments($oldid, $newid);
            }
            // Endforeach study
        }
        /**$piconvertresults = '<table><tr><td><h3>' . JText::_('JBS_IBM_PREACHIT_RESULTS') . '</h3></td></tr>'
         * . '<tr><td>' . JText::_('JBS_IBM_PI_SERVERS') . '<strong>' . $this->svadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $this->svnoadd . '</td></tr>'
         * . '<tr><td>' . JText::_('JBS_IBM_PI_TEACHERS') . '<strong>' . $this->tadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $this->tnoadd . '</td></tr>'
         * . '<tr><td>' . JText::_('JBS_IBM_PI_SERIES') . '<strong>' . $$this->sradd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $this->srnoadd . '</td></tr>'
         * . '<tr><td>' . JText::_('JBS_IBM_PI_PODCAST') . '<strong>' . $this->padd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $this->pnoadd . '</td></tr>'
         * . '<tr><td>' . JText::_('JBS_IBM_PI_STUDIES') . '<strong>' . $this->sadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $this->snoadd . '</td></tr>'
         * . '<tr><td>' . JText::_('JBS_IBM_PI_MEDIA') . '<strong>' . $this->madd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $this->mnoadd . '</td></tr>'
         * . '<tr><td>' . JText::_('JBS_IBM_PI_COMMENTS') . '<strong>' . $this->cadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED')
         * . $this->cnoadd . '</td></tr>'
         * . '</table>';
         *
         * Factory::getApplication()->enqueueMessage(JText::_($piconvertresults), 'message'); ;*/
    }

    /**
     * Get Books
     *
     * @return array
     *
     * @since 9.0.0
     */
    private function getBooks()
    {
        $books = array(
            array('id' => '1', 'book_name' => 'Genesis', 'published' => '1', 'jbs' => '101'),
            array('id' => '2', 'book_name' => 'Exodus', 'published' => '1', 'jbs' => '102'),
            array('id' => '3', 'book_name' => 'Leviticus', 'published' => '1', 'jbs' => '103'),
            array('id' => '4', 'book_name' => 'Numbers', 'published' => '1', 'jbs' => '104'),
            array('id' => '5', 'book_name' => 'Deuteronomy', 'published' => '1', 'jbs' => '105'),
            array('id' => '6', 'book_name' => 'Joshua', 'published' => '1', 'jbs' => '106'),
            array('id' => '7', 'book_name' => 'Judges', 'published' => '1', 'jbs' => '107'),
            array('id' => '8', 'book_name' => 'Ruth', 'published' => '1', 'jbs' => '108'),
            array('id' => '9', 'book_name' => '1 Samuel', 'published' => '1', 'jbs' => '109'),
            array('id' => '10', 'book_name' => '2 Samuel', 'published' => '1', 'jbs' => '110'),
            array('id' => '11', 'book_name' => '1 Kings', 'published' => '1', 'jbs' => '111'),
            array('id' => '12', 'book_name' => '2 Kings', 'published' => '1', 'jbs' => '112'),
            array('id' => '13', 'book_name' => '1 Chronicles', 'published' => '1', 'jbs' => '113'),
            array('id' => '14', 'book_name' => '2 Chronicles', 'published' => '1', 'jbs' => '114'),
            array('id' => '15', 'book_name' => 'Ezra', 'published' => '1', 'jbs' => '115'),
            array('id' => '16', 'book_name' => 'Nehemiah', 'published' => '1', 'jbs' => '116'),
            array('id' => '17', 'book_name' => 'Esther', 'published' => '1', 'jbs' => '117'),
            array('id' => '18', 'book_name' => 'Job', 'published' => '1', 'jbs' => '118'),
            array('id' => '19', 'book_name' => 'Psalm', 'published' => '1', 'jbs' => '119'),
            array('id' => '20', 'book_name' => 'Proverbs', 'published' => '1', 'jbs' => '120'),
            array('id' => '21', 'book_name' => 'Ecclesiastes', 'published' => '1', 'jbs' => '121'),
            array('id' => '22', 'book_name' => 'Song of Songs', 'published' => '1', 'jbs' => '122'),
            array('id' => '23', 'book_name' => 'Isaiah', 'published' => '1', 'jbs' => '123'),
            array('id' => '24', 'book_name' => 'Jeremiah', 'published' => '1', 'jbs' => '124'),
            array('id' => '25', 'book_name' => 'Lamentations', 'published' => '1', 'jbs' => '125'),
            array('id' => '26', 'book_name' => 'Ezekiel', 'published' => '1', 'jbs' => '126'),
            array('id' => '27', 'book_name' => 'Daniel', 'published' => '1', 'jbs' => '127'),
            array('id' => '28', 'book_name' => 'Hosea', 'published' => '1', 'jbs' => '129'),
            array('id' => '29', 'book_name' => 'Joel', 'published' => '1', 'jbs' => '129'),
            array('id' => '30', 'book_name' => 'Amos', 'published' => '1', 'jbs' => '130'),
            array('id' => '31', 'book_name' => 'Obadiah', 'published' => '1', 'jbs' => '131'),
            array('id' => '32', 'book_name' => 'Jonah', 'published' => '1', 'jbs' => '132'),
            array('id' => '33', 'book_name' => 'Micah', 'published' => '1', 'jbs' => '133'),
            array('id' => '34', 'book_name' => 'Nahum', 'published' => '1', 'jbs' => '134'),
            array('id' => '35', 'book_name' => 'Habakkuk', 'published' => '1', 'jbs' => '135'),
            array('id' => '36', 'book_name' => 'Zephaniah', 'published' => '1', 'jbs' => '136'),
            array('id' => '37', 'book_name' => 'Haggai', 'published' => '1', 'jbs' => '137'),
            array('id' => '38', 'book_name' => 'Zechariah', 'published' => '1', 'jbs' => '138'),
            array('id' => '39', 'book_name' => 'Malachi', 'published' => '1', 'jbs' => '139'),
            array('id' => '40', 'book_name' => 'Matthew', 'published' => '1', 'jbs' => '140'),
            array('id' => '41', 'book_name' => 'Mark', 'published' => '1', 'jbs' => '141'),
            array('id' => '42', 'book_name' => 'Luke', 'published' => '1', 'jbs' => '142'),
            array('id' => '43', 'book_name' => 'John', 'published' => '1', 'jbs' => '143'),
            array('id' => '44', 'book_name' => 'Acts', 'published' => '1', 'jbs' => '144'),
            array('id' => '45', 'book_name' => 'Romans', 'published' => '1', 'jbs' => '145'),
            array('id' => '46', 'book_name' => '1 Corinthians', 'published' => '1', 'jbs' => '146'),
            array('id' => '47', 'book_name' => '2 Corinthians', 'published' => '1', 'jbs' => '147'),
            array('id' => '48', 'book_name' => 'Galatians', 'published' => '1', 'jbs' => '148'),
            array('id' => '49', 'book_name' => 'Ephesians', 'published' => '1', 'jbs' => '149'),
            array('id' => '50', 'book_name' => 'Philippians', 'published' => '1', 'jbs' => '150'),
            array('id' => '51', 'book_name' => 'Colossians', 'published' => '1', 'jbs' => '151'),
            array('id' => '52', 'book_name' => '1 Thessalonians', 'published' => '1', 'jbs' => '152'),
            array('id' => '53', 'book_name' => '2 Thessalonians', 'published' => '1', 'jbs' => '153'),
            array('id' => '54', 'book_name' => '1 Timothy', 'published' => '1', 'jbs' => '154'),
            array('id' => '55', 'book_name' => '2 Timothy', 'published' => '1', 'jbs' => '155'),
            array('id' => '56', 'book_name' => 'Titus', 'published' => '1', 'jbs' => '156'),
            array('id' => '57', 'book_name' => 'Philemon', 'published' => '1', 'jbs' => '157'),
            array('id' => '58', 'book_name' => 'Hebrews', 'published' => '1', 'jbs' => '158'),
            array('id' => '59', 'book_name' => 'James', 'published' => '1', 'jbs' => '159'),
            array('id' => '60', 'book_name' => '1 Peter', 'published' => '1', 'jbs' => '160'),
            array('id' => '61', 'book_name' => '2 Peter', 'published' => '1', 'jbs' => '161'),
            array('id' => '62', 'book_name' => '1 John', 'published' => '1', 'jbs' => '162'),
            array('id' => '63', 'book_name' => '2 John', 'published' => '1', 'jbs' => '163'),
            array('id' => '64', 'book_name' => '3 John', 'published' => '1', 'jbs' => '164'),
            array('id' => '65', 'book_name' => 'Jude', 'published' => '1', 'jbs' => '165'),
            array('id' => '66', 'book_name' => 'Revelation', 'published' => '1', 'jbs' => '166')
        );

        return $books;
    }

    /**
     * Insert Media into Proclaim
     *
     * @param object $pi ?
     * @param string $type Type of Media
     * @param int $newid New ID
     * @param int $oldid Old ID
     *
     * @return boolean
     *
     * @since 9.0.0
     *
     */
    public function insertMedia($pi, $type, $newid, $oldid)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*')->from('#__pifilepath');
        $db->setQuery($query);
        $folders = $db->loadObjectList();
        $podcast_id = '0';
        $media = new \stdClass();
        $study_id = $newid;
        $media_image = '';
        $path = '';
        $filename = '';
        $size = '';
        $mime_type = '';
        $podcast_id = '';
        $filesize = '';
        $mediacode = '';
        $link_type = '';
        $player = '';
        $pod = array();
        $query = $db->getQuery(true);
        $query->select('*')->from('#__pipodcast')->where('published = 1');
        $db->setQuery($query);
        $podcasts = $db->loadObjectList();

        if ($type == 'video') {
            $filesize = $pi->videofs;
            switch ($pi->video_type) {
                case 4:
                    // Bliptv no longer exists
                    $this->mnoadd++;
                    break;

                case 7:
                    // Flowplayer
                    $this->mnoadd++;
                    //Not yet supported
                    break;

                case 1:
                    // JWPlayer
                    $query = $db->getQuery(true);
                    $query->select('folder')->from('#__pifilepath')->where('id = ' . $pi->video_link);
                    $db->setQuery($query);
                    $object = $db->loadObject();
                    $path = $object->folder;
                    $filename = $path . $pi->video_link;
                    $filename = $db->escape($filename);
                    $media = new \stdClass();
                    $media->params = '{"size":"' . $filesize . ',"filename":"' . $filename . ',"link_type":"","player":"3","popup":"1","mediacode":"","media_image":"","media_use_button_icon":"3","media_button_text":"Video","media_button_type":"btn-link","media_button_color":"","media_icon_type":"fas fa-video","media_custom_icon":"","media_icon_text_size":"24","mime_type":"image\/jpeg","autostart":"1"":"","media_hours":"' . $pi->dur_hrs . '","media_minutes":"' . $pi->dur_mins . '","media_seconds":"' . $pi->dur_secs . '"}';
                    $media->study_id = $newid;
                    $media->server_id = $this->legacyvideo;
                    $media->podcast_id = $this->insertPodcast($pi);
                    $media->createdate = $pi->date;
                    $media->hits = $pi->hits;
                    $media->access = $pi->accesscode;
                    $media->language = '*';
                    $media->created_by = $pi->user;
                    if (!$this->insertMediaRecord($media)) {
                        $this->mnoadd++;
                        break;
                    }

                    $this->madd++;
                    break;

                case 2:
                    // Vimeo
                    $media = new \stdClass();
                    $mediacode = '<iframe src="https://player.vimeo.com/video/' . $pi->video_link . '" width="500" height="500" frameborder="0"></iframe> ';
                    $mediacode = $db->escape($mediacode);
                    $media->params = '{"filename":"' . $pi->video_link . '","link_type":"","player":"5","popup":"1","mediacode":"' . $mediacode . '","media_image":"","media_use_button_icon":"3","media_button_text":"Video","media_button_type":"btn-link","media_button_color":"","media_icon_type":"fas fa-video","media_custom_icon":"","media_icon_text_size":"24","mime_type":"image\/jpeg","autostart":"1","media_hours":"' . $pi->dur_hrs . '","media_minutes":"' . $pi->dur_mins . '","media_seconds":"' . $pi->dur_secs . '"}';
                    $media->study_id = $newid;
                    $media->server_id = $this->legacyvideo;
                    $media->podcast_id = $this->insertPodcast($pi);
                    $media->createdate = $pi->date;
                    $media->hits = $pi->hits;
                    $media->access = $pi->accesscode;
                    $media->language = '*';
                    $media->created_by = $pi->user;
                    if (!$this->insertMediaRecord($media)) {
                        $this->mnoadd++;
                        break;
                    }

                    $this->madd++;
                    break;

                case 3:
                    // Youtube
                    $mediacode = '<iframe width="500" height="500" src="https://www.youtube.com/embed/' . $pi->video_link
                        . '" allowfullscreen></iframe>';
                    $media = new \stdClass();
                    $mediacode = $db->escape($mediacode);
                    $media->params = '{"filename":"https:\/\/youtu.be\/' . $pi->video_link . '","link_type":"","player":"1","popup":"3","mediacode":"","media_image":"","media_use_button_icon":"3","media_button_text":"Watch","media_button_type":"btn-link","media_button_color":"","media_icon_type":"fas fa-youtube","media_custom_icon":"","media_icon_text_size":"24","mime_type":"image\/jpeg","autostart":"1","media_hours":"' . $pi->dur_hrs . '","media_minutes":"' . $pi->dur_mins . '","media_seconds":"' . $pi->dur_secs . '"}';
                    $media->study_id = $newid;
                    $media->server_id = $this->youtube;
                    $media->podcast_id = $this->insertPodcast($pi);
                    $media->createdate = $pi->date;
                    $media->hits = $pi->hits;
                    $media->access = $pi->accesscode;
                    $media->language = '*';
                    $media->created_by = $pi->user;
                    if (!$this->insertMediaRecord($media)) {
                        $this->mnoadd++;
                        break;
                    }

                    $this->madd++;
                    break;
            }
        }

        if ($type == 'audio') {
            foreach ($folders as $folder) {
                if ($folder->id == $pi->audio_folder) {
                    $filename = $folder->folder . $pi->audio_link;
                    $filename = $db->escape($filename);
                }
            }
            $media = new \stdClass();
            $media->params = '{"filename":"' . $filename . '","mediacode":"","size":"' . $pi->audiofs .
                '","special":"","player":"7","popup":"3","link_type":"1","media_hours":"' .
                $pi->dur_hrs . '","media_minutes":"' . $pi->dur_mins . '","media_seconds":"' . $pi->dur_secs .
                '","media_image":"images\/biblestudy\/speaker24.png","media_use_button_icon":"3","media_button_text":"Listen",' .
                '"media_button_color":"","media_icon_type":"fas fa-play","media_custom_icon":"","media_icon_text_size":"24","mime_type":"audio\/mp3",' .
                '"playerwidth":"","playerheight":"","itempopuptitle":"","itempopupfooter":"","popupmargin":"50","autostart":"1"}';
            $media->study_id = $newid;
            $media->server_id = 1;
            $media->podcast_id = $this->insertPodcast($pi);
            $media->createdate = $pi->date;
            $media->hits = $pi->hits;
            $media->downloads = $pi->downloads;
            $media->language = '*';
            $media->created_by = $pi->user;
            if ($podcasts) {
                $media->podcast_id = $this->insertPodcast($pi);
            }
            if (!$this->insertMediaRecord($media)) {
                $this->mnoadd++;
            } else {
                $this->madd++;
            }
        }


        if ($type == 'notes') {
            $filesize = $pi->notesfs;
            $query = $db->getQuery(true);
            $query->select('folder')->from('#__pifilepath')->where('id = ' . $pi->notes_folder);
            $db->setQuery($query);
            $object = $db->loadObject();
            $path = $object->folder;
            $filename = $path . $pi->notes_link;
            $filename = $db->escape($filename);
            $media = new \stdClass();
            $media->server_id = 3;
            $media->params = '{"filename":"' . $filename . '","mediacode":"","size":"' . $filesize .
                '","special":"","player":"0","popup":"3","link_type":"0","media_hours":"","media_minutes":"","media_seconds":"",' .
                '"docMan_id":"0","article_id":"","virtueMart_id":"0","media_image":"images\/biblestudy\/speaker24.png","media_use_button_icon":"3",' .
                '"media_button_text":"Text","media_button_color":"","media_icon_type":"fas fa-sticky-note","media_custom_icon":"",' .
                '"media_icon_text_size":"24","mime_type":"audio\/mp3","playerwidth":"","playerheight":"","itempopuptitle":"","itempopupfooter":"",' .
                '"popupmargin":"50","autostart":"false"}';
            $media->study_id = $newid;
            $media->podcast_id = $this->insertPodcast($pi);
            $media->createdate = $pi->date;
            $media->hits = $pi->hits;
            $media->downloads = $pi->downloads;
            $media->language = '*';
            $media->created_by = $pi->user;
            if (!$this->insertMediaRecord($media)) {
                $this->mnoadd++;
            } else {
                $this->madd++;
            }
        }


        if ($type == 'slides') {
            $filesize = $pi->slidesfs;
            $query = $db->getQuery(true);
            $query->select('folder')->from('#__pifilepath')->where('id = ' . $pi->slides_folder);
            $db->setQuery($query);
            $object = $db->loadObject();
            $path = $object->folder;
            $filename = $path . $pi->slides_link;
            $filename = $db->escape($filename);
            $media = new \stdClass();
            $media->params = '{"filename":"' . $filename . '","mediacode":"","size":"' . $filesize .
                '","special":"","player":"0","popup":"3","link_type":"0","media_hours":"' . $pi->dur_hrs .
                '","media_minutes":"' . $pi->dur_mins . '","media_seconds":"' . $pi->dur_secs .
                '","docMan_id":"0","article_id":"","virtueMart_id":"0","media_image":"images\/biblestudy\/speaker24.png",' .
                '"media_use_button_icon":"3","media_button_text":"Audio","media_button_color":"","media_icon_type":"fas fa-file-powerpoint",' .
                '"media_custom_icon":"","media_icon_text_size":"24","mime_type":"audio\/mp3","playerwidth":"","playerheight":"",' .
                '"itempopuptitle":"","itempopupfooter":"","popupmargin":"50","autostart":"false"}';
            $media->study_id = $newid;
            $media->server_id = 3;
            $media->podcast_id = $this->insertPodcast($pi);
            $media->createdate = $pi->date;
            $media->hits = $pi->hits;
            $media->downloads = $pi->downloads;
            $media->language = '*';
            $media->created_by = $pi->user;
            if ($podcasts) {
                $podcast_id = $this->insertPodcast($pi);
            }
            if (!$this->insertMediaRecord($media)) {
                $this->mnoadd++;
            } else {
                $this->madd++;
            }
        }


        return true;
    }

    /**
     * @param $pi
     *
     * @return false|mixed|void
     *
     * @since 9.0.0
     */
    private function insertPodcast($pi)
    {
        $podtest = 0;
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*')->from('#__pipodcast')->where('published = 1');
        $db->setQuery($query);
        $podcasts = $db->loadObjectList();
        $includeteacher = array();
        $includeministry = array();
        $includeseries = array();
        $includemedia = array();
        //Series, ministry, teacher, media (audio, video, notes, slidewhow)
        // $podcast_series = 0 means "all"
        // 1 means inclusive (only those)
        // 2 means exclusive (not those)
        // series_list - lists the elements included or excluded
        foreach ($podcasts as $podcast) {
            //run through media
            //0 means all 1 means the selection in the array
            if ($podcast->media == 1) {
                $registry = new Registry();
                $registry->loadString($podcast->media_list);
                $media_list = $registry->toArray();
                foreach ($media_list as $medi) {
                    if ($medi == 'audio') {
                        $includemedia[] = 'audio';
                    }
                    if ($medi == 'video') {
                        $includemedia[] = 'video';
                    }
                    if ($medi == 'slides') {
                        $includemedia[] = 'slides';
                    }
                }
            } else {
                $includemedia = array();
                $includemedia[] = 'all';
            }
            //check for exclusion
            if ($podcast->teacher == 2) {
                $registry = new Registry();
                $registry->loadString($podcast->teacher_list);
                $teacher_list = $registry->toArray();
                foreach ($teacher_list as $t) {
                    if (in_array($t, $pi->teacher)) {
                        return false;
                    }
                }
            }
            //check for exclusion
            if ($podcast->series == 2) {
                $registry = new Registry();
                $registry->loadString($podcast->series_list);
                $series_list = $registry->toArray();
                foreach ($series_list as $s) {
                    if ($s == $pi->series) {
                        return false;
                    }
                }
            }
            ///check for exclusion
            if ($podcast->ministry == 2) {
                $registry = new Registry();
                $registry->loadString($podcast->ministry_list);
                $ministry_list = $registry->toArray();
                foreach ($ministry_list as $m) {
                    if (in_array($m, $pi->ministry)) {
                        return false;
                    }
                }
            }
            //check for inclusion series
            if ($podcast->series == 1) {
                $registry = new Registry();
                $registry->loadString($podcast->series_list);
                $series_list = $registry->toArray();
                if (count($series_list) > 1) {
                    foreach ($series_list as $si) {
                        if ($si == $pi->series) {
                            return $this->checkMedia($includemedia, $pi, $podcast);
                        }
                    }
                } elseif ($series_list[0]) {
                    $value = $series_list[0];
                    if ($value == $pi->series) {
                        return $this->checkMedia($includemedia, $pi, $podcast);
                    }
                }
            }
            if ($podcast->series == 0) {
                return $this->checkMedia($includemedia, $pi, $podcast);
            }
            //include teachers
            if ($podcast->teacher == 1) {
                $registry = new Registry();
                $registry->loadString($podcast->teacher_list);
                $teacher_list = $registry->toArray();
                $registry = new Registry();
                $registry->loadString($pi->teacher);
                $teacher = $registry->toArray();
                if (count($teacher) > 1) {
                    foreach ($teacher_list as $ti) {
                        if (in_array($ti, $teacher) || $podcast->teacher == 0) {
                            return $this->checkMedia($includemedia, $pi, $podcast);
                        }
                    }
                } elseif ($teacher_list[0]) {
                    $value = $teacher_list[0];
                    if ($value == $pi->teacher) {
                        return $this->checkMedia($includemedia, $pi, $podcast);
                    }
                }
            }
            //check for inclusion ministry
            if ($podcast->ministry == 1) {
                $registry = new Registry();
                $registry->loadString($podcast->ministry_list);
                $ministry_list = $registry->toArray();
                $registry = new Registry();
                $registry->loadString($pi->ministry);
                $ministry = $registry->toArray();
                if (count($ministry_list) > 1) {
                    foreach ($ministry_list as $mi) {
                        if (in_array($mi, $ministry) || $podcast->ministry == 0) {
                            return $this->checkMedia($includemedia, $pi, $podcast);
                        }
                    }
                } elseif ($ministry_list[0]) {
                    $value = $ministry_list[0];
                    if ($value == $pi->ministry) {
                        return $this->checkMedia($includemedia, $pi, $podcast);
                    }
                }
            }
            //Use include/exlude lists to determine if media file is in which podcast
        } // end foreach podcast
    }

    /**
     * @param $includemedia
     * @param $pi
     * @param $podcast
     *
     * @return mixed|void
     *
     * @since 9.0.0
     */
    private function checkMedia($includemedia, $pi, $podcast)
    {
        //check for which media to include
        if ($includemedia == 'all') {
            foreach ($this->podcastids as $pods) {
                if ($pods['oldid'] == $podcast->id) {
                    $podcast_id = $pods['newid'];

                    return $podcast_id;
                }
            }
        }
        //go through the media inclusion list for a match
        if (in_array('audio', $includemedia, true) && isset($pi->audio_link)) {
            {
            foreach ($this->podcastids as $pods) {
                if ($pods['oldid'] == $podcast->id) {
                    $podcast_id = $pods['newid'];

                    return $podcast_id;
                }
            }
            }
        }
        if (in_array('video', $includemedia, true) && isset($pi->video_link)) {
            {
            foreach ($this->podcastids as $pods) {
                if ($pods['oldid'] == $podcast->id) {
                    $podcast_id = $pods['newid'];

                    return $podcast_id;
                }
            }
            }
        }
        if (in_array('slides', $includemedia, true) && $pi->slides_link > 0) {
            {
            foreach ($this->podcastids as $pods) {
                if ($pods['oldid'] == $podcast->id) {
                    $podcast_id = $pods['newid'];

                    return $podcast_id;
                }
            }
            }
        }
    }

    /**
     * @param $mediafiles
     *
     * @return bool
     *
     * @since 9.0.0
     */
    private function insertMediaRecord($mediafiles)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        if (!$db->insertObject('#__bsms_mediafiles', $mediafiles, 'id')) {
            return false;
        }

        return true;
    }

    /**
     * Insert Comments
     *
     * @param int $oldid ?
     * @param int $newid ?
     *
     * @return boolean
     *
     * @since 9.0.0
     */
    private function insertComments($oldid, $newid)
    {
        if (!$this->picomments) {
            return false;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        foreach ($this->picomments as $pi) {
            if ($pi->id == $oldid) {
                $comments = new \stdClass();
                $comments->id = '';
                $comments->published = $pi->published;
                $comments->study_id = $newid;
                $comments->user_id = $pi->user_id;
                $comments->full_name = $pi->full_name;
                $comments->comment_date = $pi->comment_date;
                $comments->user_email = $pi->email;
                $comments->comment_text = $db->escape($pi->comment_text);

                if (!$db->insertObject('#__bsms_comments', $comments, 'id')) {
                    $this->cnoadd++;
                } else {
                    $this->cadd++;
                }
            }
        }

        return true;
    }
}
