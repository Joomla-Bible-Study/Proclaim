<?php

/**
 * PreachIT Converter system
 * @package BibleStudy.Admin
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

/**
 * Convert Class
 * @package BibleStudy.Admin
 * @since 7.1.0
 */
class JBSPIconvert {

    /**
     * Convert PreachIT
     * @since 7.1.0
     */
    function convertPI() {
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
        $svadd = 0;
        $svnoadd = 0;
        $fnoadd = 0;
        $fadd = 0;
        $tnoadd = 0;
        $tadd = 0;
        $srnoadd = 0;
        $sradd = 0;
        $pnoadd = 0;
        $padd = 0;
        $lnoadd = 0;
        $ladd = 0;
        $snoadd = 0;
        $sadd = 0;
        $mnoadd = 0;
        $madd = 0;
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__picomments';
        $db->setQuery($query);
        $db->query();
        $this->picomments = $db->loadObjectList();

        //Create servers and folders
        $query = 'SELECT * FROM #__pifilepath';
        $db->setQuery($query);
        $db->query();
        $piservers = $db->loadObjectList();
        if (!$piservers) {
            $svnoadd++;
        } else {
            foreach ($piservers AS $pi) {
                $query = 'INSERT INTO #__bsms_servers SET `server_path` = "' . $pi->server . '", `server_name` = "' . $pi->server . '", `published` = "' . $pi->published . '"';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() > 0) {
                    $svnoadd++;
                    //$error = $db->getErrorMsg();
                    //$piconversion .= '<tr><td>'.JText::_('JBS_IBM_ERROR_OCCURED_MIGRATING_SERVERS').' id: '.$pi->id.' - '.$error.'</td></tr>';
                } else {
                    $svadd++;
                    //$supdated = 0;
//        				$supdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
//        				$sadd = $sadd + $supdated;
                    //get the new serverid so we can later connect it to a study
                    $query = 'SELECT id FROM #__bsms_servers ORDER BY `id` DESC LIMIT 1';
                    $db->setQuery($query);
                    $db->query();
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $this->serversids[] = array('newid' => $newid, 'oldid' => $oldid);
                }
                $query = 'INSERT INTO #__bsms_folders SET `foldername` = "' . $pi->name . '", `folderpath` = "' . $pi->server . '/' . $pi->folder . '/", `published` = "' . $pi->published . '"';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() > 0) {
                    $fnoadd++;
                    //$error = $db->getErrorMsg();
                    //$piconversion .= '<tr><td>'.JText::_('JBS_IBM_ERROR_OCCURED_MIGRATING_FOLDER').' id: '.$pi->id.' - '.$error.'</td></tr>';
                } else {
                    $fadd++;
                    //$fupdated = 0;
//        				$fupdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
//        				$fadd = $fadd + $fupdated;
                    //get the new folderid so we can later connect it to a study
                    $query = 'SELECT id FROM #__bsms_folders ORDER BY `id` DESC LIMIT 1';
                    $db->setQuery($query);
                    $db->query();
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $this->foldersids[] = array('newid' => $newid, 'oldid' => $oldid);
                }
            }
        }
        //Teachers
        $query = 'SELECT * FROM #__piteachers';
        $db->setQuery($query);
        $db->query();
        $piteachers = $db->loadObjectList();
        if (!$piteachers) {
            $tnoadd++;
        } else {
            foreach ($piteachers AS $pi) {
                //Map new folder for images to old one
                $foldersmall = $pi->image_folder;
                $folderlarge = $pi->image_folderlrg;
                foreach ($this->foldersids as $folder) {
                    if ($folder['oldid'] == $foldersmall) {
                        $foldersmall = $folder['newid'];
                    }
                    if ($folder['oldid'] == $folderlarge) {
                        $folderlarge = $folder['newid'];
                    }
                }
                //look up folders to use in teacher images
                $query = 'SELECT folderpath FROM #__bsms_folders WHERE id = ' . $foldersmall;
                $db->setQuery($query);
                $object = $db->loadObject();
                $newfoldersmall = $object->folderpath;
                $query = 'SELECT folderpath FROM #__bsms_folders WHERE id = ' . $folderlarge;
                $db->setQuery($query);
                $object = $db->loadObject();
                $newfolderlarge = $object->folderpath;
                $query = 'INSERT INTO #__bsms_teachers SET `teachername` = "' . $pi->teacher_name . '", `alias` = "' . $pi->alias
                        . '", `title` = "' . $pi->teacher_role . '", `image` = "' . $newfolderlarge . $pi->teacher_image_lrg . '", `thumb` = "'
                        . $newfoldersmall . $pi->teacher_image_sm . '", `email` = "' . $pi->teacher_email . '", `website` = "' . $pi->teacher_website . '",
                    `short` = "' . $db->getEscaped($pi->teacher_description) . '", `list_show` = "' . $pi->teacher_view . '", `published` = "' . $pi->published . '"';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() > 0) {
                    $tnoadd++;
                    //$error = $db->getErrorMsg();
                    //$piconversion .= '<tr><td>'.JText::_('JBS_IBM_ERROR_OCCURED_MIGRATING_TEACHER').' id: '.$pi->id.' - '.$error.'</td></tr>';
                } else {
                    $tadd++;
                    //$tupdated = 0;
//            				$tupdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
//            				$tadd = $tadd + $tupdated;
                    //get the new teacherid so we can later connect it to a study
                    $query = 'SELECT id FROM #__bsms_teachers ORDER BY `id` DESC LIMIT 1';
                    $db->setQuery($query);
                    $db->query();
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $this->teachersids[] = array('newid' => $newid, 'oldid' => $oldid);
                }
            }
        }
        //Convert Ministries
        $query = 'SELECT * FROM #__piministry';
        $db->setQuery($query);
        $db->query();
        $ministries = $db->loadObjectList();
        if (!$ministries) {
            $piconversion .= '<tr><td>' . JText::_('JBS_IBM_NO_MINISTRIES') . '</td></tr>';
        } else {
            foreach ($ministries as $pi) {
                $query = 'INSERT INTO #__bsms_locations SET `published` = "' . $pi->published . '", `location_text` = "' . $pi->ministry_name . '",
                    `access` = "' . $pi->access . '", `ordering` = "' . $pi->ordering . '"';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() > 0) {
                    $lnoadd++;
                    //$error = $db->getErrorMsg();
                    //	$piconversion .= '<tr><td>'.JText::_('JBS_IBM_ERROR_OCCURED_MIGRATING_MINSTRY').' id: '.$pi->id.' - '.$error.'</td></tr>';
                } else {
                    $ladd++;
                    //$mupdated = 0;
//            				$mupdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
//            				$madd = $madd + $mupdated;
                    //get the new teacherid so we can later connect it to a study
                    $query = 'SELECT id FROM #__bsms_locations ORDER BY `id` DESC LIMIT 1';
                    $db->setQuery($query);
                    $db->query();
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $this->locations[] = array('newid' => $newid, 'oldid' => $oldid);
                }
            }
        }
        //Convert Series
        $query = 'SELECT * FROM #__piseries';
        $db->setQuery($query);
        $db->query();
        $series = $db->loadObjectList();
        if (!$series) {
            $piconversion .= '<tr><td>' . JText::_('JBS_IBM_NO_SERIES') . '</td></tr>';
        } else {
            foreach ($series AS $pi) {
                //Map new folder for images to old one
                $foldersmall = $pi->image_folder;
                $folderlarge = $pi->image_folderlrg;
                foreach ($this->foldersids as $folder) {
                    if ($folder['oldid'] == $foldersmall) {
                        $foldersmall = $folder['newid'];
                    }
                    if ($folder['oldid'] == $folderlarge) {
                        $folderlarge = $folder['newid'];
                    }
                }
                //look up folders to use in series images
                $query = 'SELECT folderpath FROM #__bsms_folders WHERE id = ' . $foldersmall;
                $db->setQuery($query);
                $object = $db->loadObject();
                $newfoldersmall = $object->folderpath;
                $query = 'SELECT folderpath FROM #__bsms_folders WHERE id = ' . $foldersmall;
                $db->setQuery($query);
                $object = $db->loadObject();
                $newfolderlarge = $object->folderpath;
                $query = 'INSERT INTO #__bsms_series SET `series_text` = "' . $pi->series_name . '", `alias` = "' . $pi->series_alias . '",
                    `description` = "' . $pi->series_description . '", `series_thumbnail` = "' . $newfoldersmall . $pi->series_image_sm . '", `published` = "' . $pi->published . '"';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() > 0) {
                    $srnoadd++;
                    //$error = $db->getErrorMsg();
                    //$piconversion .= '<tr><td>'.JText::_('JBS_IBM_ERROR_OCCURED_MIGRATING_SERIES').' id: '.$pi->id.' - '.$error.'</td></tr>';
                } else {
                    $sradd++;
                    // $tupdated = 0;
//            				$tupdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
//            				$tadd = $tadd + $tupdated;
                    //get the new teacherid so we can later connect it to a study
                    $query = 'SELECT id FROM #__bsms_series ORDER BY `id` DESC LIMIT 1';
                    $db->setQuery($query);
                    $db->query();
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $this->seriesids[] = array('newid' => $newid, 'oldid' => $oldid);
                }
            }
        }
        //Convert the podcacst
        $query = 'SELECT * FROM #__pipodcast';
        $db->setQuery($query);
        $db->query();
        $podcasts = $db->loadObjectList();
        if (!$podcasts) {
            $piconversion .= '<tr><td>' . JText::_('JBS_IBM_NO_PODCASTS') . '</td></tr>';
        } else {
            foreach ($podcasts AS $pi) {
                $query = 'INSERT INTO #__bsms_podcast SET `title` = "' . $pi->name . '", `website` = "' . $pi->website . '",
                    `description` = "' . $pi->description . '", `image` = "' . $pi->image . '", `imageh` = "' . $pi->imagehgt . '", `imagew` = "'
                        . $pi->imagewth . '", `author` = "' . $pi->author . '", `filename` = "' . $pi->filename . '", `language` = "' . $pi->language . '",
                    `editor_name` = "' . $pi->editor . '", `editor_email` = "' . $pi->email . '", `podcastlimit` = "' . $pi->records . '",
                    `episodetitle` = "' . $pi->itunestitle . '", `detailstemplateid` = "1", `published` = "' . $pi->published . '", `podcastsearch` = "' . $pi->search . '"';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() > 0) {
                    $pnoadd++;
                    //$error = $db->getErrorMsg();
                    //$piconversion .= '<tr><td>'.JText::_('JBS_IBM_ERROR_OCCURED_MIGRATING_PODCAST').' id: '.$pi->id.' - '.$error.'</td></tr>';
                } else {
                    $padd++;
                    //$pupdated = 0;
//            				$pupdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
//            				$padd = $padd + $pupdated;
                    //get the new teacherid so we can later connect it to a study
                    $query = 'SELECT id FROM #__bsms_podcast ORDER BY `id` DESC LIMIT 1';
                    $db->setQuery($query);
                    $db->query();
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $this->podcasts[] = array('newid' => $newid, 'oldid' => $oldid);
                }
            }
        }

        //Convert studies and media files
        $books = $this->getBooks();
        $query = 'SELECT * FROM #__pistudies';
        $db->setQuery($query);
        $db->query();
        $studies = $db->loadObjectList();
        if (!$studies) {
            $piconversion .= '<tr><td>' . JText::_('JBS_IBM_NO_STUDIES') . '</td></tr>';
        } else {
            foreach ($studies AS $pi) {
                $studydate = $pi->study_date;
                $studytitle = $pi->study_name;
                foreach ($this->teachersids as $teacher) {
                    if ($teacher['oldid'] == $pi->teacher) {
                        $teacher_id = $teacher['newid'];
                    } else {
                        $teacher_id = '1';
                    }
                }
                $studynumber = $pi->id;
                foreach ($books AS $book) {
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
                foreach ($this->locations AS $location) {
                    if ($location['oldid'] == $pi->ministry) {
                        $location_id = $location['newid'];
                    }
                }
                $alias = $pi->study_alias;
                $studyintro = $pi->study_description;
                $media_hours = $pi->dur_hrs;
                $media_minutes = $pi->dur_mins;
                $media_seconds = $pi->dur_secs;
                foreach ($this->seriesids as $series) {
                    if ($series['oldid'] == $pi->series) {
                        $series_id = $series['newid'];
                    }
                }
                $studytext = $db->getEscaped($pi->study_text);
                $imagefolder = 0;
                $newfolder = 0;
                $thumbnailm = '';

                foreach ($this->foldersids as $folder) {
                    if ($folder['oldid'] == $pi->image_folder) {
                        $imagefolder = $folder['newid'];
                        $image = $pi->imagesm;
                    }
                    if ($folder['oldid'] == $pi->image_foldermed) {
                        $imagefolder = $folder['newid'];
                        $image = $pi->imagemed;
                    }
                    if ($folder['oldid'] == $pi->image_folderlrg) {
                        $imagefolder = $folder['newid'];
                        $image = $pi->imagelrg;
                    }
                }
                if ($imagefolder) {
                    $query = 'SELECT folderpath FROM #__bsms_folders WHERE id = ' . $imagefolder;
                    $db->setQuery($query);
                    $object = $db->loadObject();
                    $newfolder = $object->folderpath;
                    $thumbnailm = $newfolder . $image;
                }
                $published = $pi->published;
                $params = '{"metakey":"' . $pi->tags . '","metadesc":""}';
                $params = $db->getEscaped($params);
                $access = $pi->saccess;
                //Create the study then get the id to create the media file and comments
                $query = 'INSERT INTO #__bsms_studies SET `published` = "' . $published . '", `studydate` = "' . $studydate . '",
                     `studytitle` = "' . $studytitle . '", `teacher_id` = "' . $teacher_id . '", `studynumber` = "' . $studynumber . '",
                     `booknumber` = "' . $booknumber . '", `booknumber2` = "' . $booknumber2 . '", `chapter_begin` = "' . $chapter_begin . '",
                     `chapter_end` = "' . $chapter_end . '", `verse_begin` = "' . $verse_begin . '", `verse_end` = "' . $verse_end . '",
                     `chapter_begin2` = "' . $chapter_begin2 . '", `chapter_end2` = "' . $chapter_end2 . '", `verse_begin2` =
                     "' . $verse_begin2 . '", `verse_end2` = "' . $verse_end2 . '", `comments` = "' . $comments . '", `hits` = "' . $hits . '",
                     `user_id` = "' . $user_id . '", `show_level` = "' . $show_level . '", `location_id` = "' . $location_id . '",
                     `alias` = "' . $alias . '", `studyintro` = "' . $studyintro . '", `media_hours` = "' . $media_hours . '",
                     `media_minutes` = "' . $media_minutes . '", `media_seconds` = "' . $media_seconds . '", `series_id` = "' . $series_id . '",
                     `studytext` = "' . $studytext . '", `thumbnailm` = "' . $thumbnailm . '", `params` = "' . $params . '", `access` = "' . $access . '"';
                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum() > 0) {
                    $snoadd++;
                    //$error = $db->getErrorMsg();
                    //$piconversion .= '<tr><td>'.JText::_('JBS_IBM_ERROR_OCCURED_MIGRATING_STUDY').' id: '.$pi->id.' - '.$error.'</td></tr>';
                } else {
                    $sadd++;
                    // $mupdated = 0;
                    //	$mupdated = $db->getAffectedRows(); //echo 'affected: '.$updated;
                    //	$madd = $madd + $mupdated;
                    //get the new teacherid so we can later connect it to a study
                    $query = 'SELECT id FROM #__bsms_studies ORDER BY `id` DESC LIMIT 1';
                    $db->setQuery($query);
                    $db->query();
                    $newid = $db->loadResult();
                    $oldid = $pi->id;
                    $this->studiesids[] = array('newid' => $newid, 'oldid' => $oldid);
                }
                //Create the mediafiles
                if ($pi->audio_link) {
                    if (!$audio = $this->insertMedia($pi, $type = 'audio', $newid, $oldid)) {
                        $mnoadd++;
                    } else {
                        $madd++;
                    }
                }
                if ($pi->video_link) {
                    if (!$video = $this->insertMedia($pi, $type = 'video', $newid, $oldid)) {
                        $mnoadd++;
                    } else {
                        $madd++;
                    }
                }
                if ($pi->slides_link) {
                    if (!$slides = $this->insertMedia($pi, $type = 'slides', $newid, $oldid)) {
                        $mnoadd++;
                    } else {
                        $madd++;
                    }
                }
                if ($pi->notes_link) {
                    if (!$notes = $this->insertMedia($pi, $type = 'notes', $newid, $oldid)) {
                        $mnoadd++;
                    } else {
                        $madd++;
                    }
                }
                $comments = $this->insertComments($oldid, $newid);
            } //endforeach study
        }

        $piconversion = '<table><tr><td><h3>' . JText::_('JBS_IBM_PREACHIT_RESULTS') . '</h3></td></tr>'
                . '<tr><td>' . JText::_('JBS_IBM_PI_SERVERS') . '<strong>' . $svadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $svnoadd . '</td></tr>'
                . '<tr><td>' . JText::_('JBS_IBM_PI_FOLDERS') . '<strong>' . $fadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $fnoadd . '</td></tr>'
                . '<tr><td>' . JText::_('JBS_IBM_PI_TEACHERS') . '<strong>' . $tadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $tnoadd . '</td></tr>'
                . '<tr><td>' . JText::_('JBS_IBM_PI_SERIES') . '<strong>' . $sradd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $srnoadd . '</td></tr>'
                . '<tr><td>' . JText::_('JBS_IBM_PI_PODCAST') . '<strong>' . $padd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $pnoadd . '</td></tr>'
                . '<tr><td>' . JText::_('JBS_IBM_PI_STUDIES') . '<strong>' . $sadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $snoadd . '</td></tr>'
                . '<tr><td>' . JText::_('JBS_IBM_PI_MEDIA') . '<strong>' . $madd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $mnoadd . '</td></tr>'
                . '<tr><td>' . JText::_('JBS_IBM_PI_COMMENTS') . '<strong>' . $this->cadd . '</strong> - ' . JText::_('JBS_IBM_NOT_CONVERTED') . $this->cnoadd . '</td></tr>'
                . '</table>';
        return $piconversion;
    }

    /**
     * Insert Medai into BibleStudy
     * @param int $pi
     * @param string $type
     * @param int $newid
     * @param int $oldid
     * @return boolean
     */
    function insertMedia($pi, $type, $newid, $oldid) {
        $db = JFactory::getDBO();
        $podcast_id = '-1';
        $study_id = $newid;
        $media_image = '';
        $path = '';
        $filename = '';
        $size = '';
        $mime_type = '';
        $podcast_id = '';
        $query = 'SELECT * FROM #__pipodmes';
        $db->setQuery($query);
        $db->query();
        $podcasts = $db->loadObjectList();
        if ($type == 'audio') {
            $player = $pi->audio_type;
            $media_image = '1';
            $mime_type = '1';
            $filesize = $pi->audiofs;
            $player = '1';
            $filename = $pi->audio_link;

            if ($podcasts) {
                foreach ($podcasts as $podcast) {
                    if ($podcast->mesid == $oldid) {
                        $oldpodid = $podcast->podaudid;
                        if ($pod['oldid'] == $oldpodid) {
                            $podcast_id = $pod['newid'];
                        }
                    }
                }
            }
        }
        if ($type == 'video') {
            if ($podcasts) {
                foreach ($podcasts as $podcast) {
                    if ($podcast->mesid == $oldid) {
                        $oldpodid = $podcast->podvidid;
                        if ($pod['oldid'] == $oldpodid) {
                            $podcast_id = $pod['newid'];
                        }
                    }
                }
            }
            $filesize = $pi->videofs;
            switch ($pi->video_type) {
                case 4:
                    //bliptv
                    $mediacode = '<embed src="http://blip.tv/play/' . $pi->video_link . '" type="application/x-shockwave-flash" width="500" height="500" wmode="transparent" allowscriptaccess="always" allowfullscreen="true" ></embed>';
                    $mediacode = $db->getEscaped($mediacode);
                    $player = '8';
                    $media_image = '5';
                    $mime_type = '15';
                    break;

                case 7:
                    //flowplayer
                    foreach ($this->foldersids as $folder) {
                        if ($pi->video_folder == $folder['oldid']) {
                            //look up the text to put here for $folder.
                            $query = 'SELECT folderpath FROM #__bsms_folders WHERE id = ' . $folder['newid'];
                            $db->setQuery($query);
                            $object = $db->loadObject();
                            $path = $object->folderpath;
                            $filename = $path . $pi->video_link;
                        }
                    }
                    $player = '1';
                    $media_image = '5';
                    $mime_type = '15';
                    $server = '-1';
                    break;

                case 1:
                    //JWPlayer
                    foreach ($this->foldersids as $folder) {
                        if ($pi->video_folder == $folder['oldid']) {
                            //look up the text to put here for $folder.
                            $query = 'SELECT folderpath FROM #__bsms_folders WHERE id = ' . $folder['newid'];
                            $db->setQuery($query);
                            $object = $db->loadObject();
                            $path = $object->folderpath;
                            $filename = $path . $pi->video_link;
                        }
                    }
                    $player = '1';
                    $media_image = '5';
                    $mime_type = '15';
                    $server = '-1';
                    break;

                case 2:
                    //Vimeo
                    $mediacode = '<iframe src="http://player.vimeo.com/video/' . $pi->video_link . '" width="500" height="500" frameborder="0"></iframe> ';
                    $mediacode = $db->getEscaped($mediacode);
                    $player = '8';
                    $media_image = '5';
                    $mime_type = '15';
                    $path = '-1';
                    $server = '-1';
                    break;

                case 3:
                    //Youtube
                    $mediacode = '<iframe width="500" height="500" src="http://www.youtube.com/embed/' . $pi->video_link . '" frameborder="0" allowfullscreen></iframe>';
                    $mediacode = $db->getEscaped($mediacode);
                    $player = '8';
                    $media_image = '13';
                    $mime_type = '15';
                    $path = '-1';
                    $server = '-1';
                    break;
            }
        }
        $createdate = $pi->study_date;
        if ($type == 'audio') {
            $link_type = $pi->audio_download;
        }
        if ($type == 'video') {
            $link_type = $pi->video_download;
        }

        if ($type == 'notes') {
            $filesize = $pi->notesfs;
            $download = '1';
            $player = '0';
            $media_image = '12';
            $mime_type = '6';
            foreach ($this->foldersids as $folder) {
                if ($pi->notes_folder == $folder['oldid']) {
                    $query = 'SELECT folderpath FROM #__bsms_folders WHERE id = ' . $folder['newid'];
                    $db->setQuery($query);
                    $object = $db->loadObject();
                    $path = $object->folderpath;
                    $filename = $path . $pi->notes_link;
                    $server = '-1';
                }
            }
        }
        $hits = $pi->hits;
        $downloads = $pi->downloads;
        $published = $pi->published;
        $params = '{"playerwidth":"","playerheight":"","itempopuptitle":"","itempopupfooter":"","popupmargin":"50"}';
        $params = $db->getEscaped($params);
        $popup = '1';
        $access = $pi->access;
        if ($type == 'slides') {
            $download = '1';
            $filesize = $pi->slidesfs;
            $player = '0';
            $filename = $pi->slides_link;
            foreach ($this->foldersids as $folder) {
                if ($pi->slides_folder == $folder['oldid']) {
                    $query = 'SELECT folderpath FROM #__bsms_folders WHERE id = ' . $folder['newid'];
                    $db->setQuery($query);
                    $object = $db->loadObject();
                    $path = $object->folderpath;
                    $server = '-1';
                    $filename = $path . $pi->slides_link;
                    $media_image = '12';
                    $mime_type = '6';
                }
            }
        }
        $query = 'INSERT INTO #__bsms_mediafiles SET
            `published` = "' . $published . '", `study_id` = "' . $newid . '", `path` = "' . $path . '", `filename` = "' . $filename . '",
            `size` = "' . $filesize . '", `mime_type` = "' . $mime_type . '", `podcast_id` = "' . $podcast_id . '",
            `mediacode` = "' . $mediacode . '", `createdate` = "' . $createdate . '", `link_type` = "' . $link_type . '",
            `hits` = "' . $pi->hits . '", `params` = "' . $params . '", `player` = "' . $player . '", `popup` = "1", `access` = "' . $pi->access . '",
            `media_image` = "' . $media_image . '"';
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum() > 0) {
            return false;
        }
        return true;
    }

    /**
     * Get Books
     * @return array
     */
    function getBooks() {
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
     * Insert Comments
     * @param int $oldid
     * @param int $newid
     * @return boolean
     */
    function insertComments($oldid, $newid) {

        if (!$this->picomments) {
            return false;
        } else {
            $db = JFactory::getDBO();
            foreach ($this->picomments AS $pi) {
                if ($pi->id == $oldid) {
                    $query = 'INSERT INTO #__bsms_comments SET `published` = "' . $pi->published . '", `study_id` = "' . $newid .
                            '", `user_id` = "' . $pi->user_id . '", `full_name` = "' . $pi->full_name . '", `comment_date` = "' . $pi->comment_date . '", `comment_text` = "' . $db->getEscaped($pi->comment_text) . '"';
                    $db->setQuery($query);
                    $db->query();
                    if ($db->getErrorNum() > 0) {
                        $this->cnoadd++;
                    } else {
                        $this->cadd++;
                    }
                }
            }
        }
        return true;
    }

}