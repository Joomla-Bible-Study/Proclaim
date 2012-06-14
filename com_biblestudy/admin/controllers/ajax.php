<?php

/**
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Controler for Ajax
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class biblestudyControllerajax extends JController {

    /**
     * Returns the number of files in a folder (Recursively)
     * @return Number of files, or null
     */
    function getFolder() {
        $folders = new JFolder();
        $ext = new JFile();

        $folder = JRequest::getVar('folder');
        if (empty($folder)) {
            $folder = JPATH_SITE . DS . 'media';
        }
        if ($folders->files($folder, '.', true) != false) {
            echo json_encode(array('fileCount' => count($folders->files($folder, '.', true))));
        }
    }

    /**
     * Analyze Files
     */
    function analyzeFiles() {
        // @todo need to chang to right place
        require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'getid3.php');
        $id3 = new getID3();
        $files = new JFolder();
        $ext = new JFile();

        $folder = JRequest::getVar('folder');

        $files = $files->files($folder, '.', true, true);
        if ($files != false) {
            foreach ($files as $file) {
                echo json_encode($id3->analyze($file));
            }
        }
    }

    /**
     * Ipmport File
     * @return string
     */
    function importFile() {
        $jFile = new JFile();
        $model = $this->getModel('biblestudy');
        $file = JRequest::getVar('file', null, 'POST', 'ARRAY');
        $errors = array();

        //The first element in the array should be the path to the file.
        if (empty($file[0])) {
            $error[] = "The file was not found";
            return;
        }
        /**
         * Build Objects
         */
        $study = new stdClass;
        $mediaFile = new stdClass;
        $teacher = new stdClass;
        $location = new stdClass;
        $series = new stdClass;
        $topic = new stdClass;
        $type = new stdClass;
        $server = new stdClass;
        $folder = new stdClass;
        $podcast = new stdClass;
        $mimeType = new stdClass;


        $mediaFile->size = $file[15];
        $mediaFile->filename = $jFile->getName($file[0]);
        $mediaFile->mediacode = $file[14];
        $mediaFile->createdate = $file[3];
        $mediaFile->size = $file[15];
        $mediaFile->comment = $file[20];

        $study->studytitle = $file[1];
        $study->studynumber = $file[2];
        $study->studydate = $file[3];
        $study->studytext = $file[13];
        $study->studytext2 = $file[4];
        $duration = explode(':', $file[12]);
        if (count($duration) == 3) {
            $study->media_hours = $duration[0];
            $study->media_minutes = $duration[1];
            $study->media_seconds = $duration[2];
        } else {
            $study->media_minutes = $duration[0];
            $study->media_seconds = $duration[1];
        }

        $study->secondary_reference = $file[6];


        //Move the files to the server if necessary
        if ($file[21] == 'on') {
            jimport('joomla.filter.filteroutput');
            $filter = new JFilterOutput();
            $source = $file[0];
            $filename = explode('/', $jFile->getName($source));
            $filename = $jFile->stripExt($filename[count($filename) - 1]);
            $filename = $filter->stringURLSafe($filename) . '.' . $jFile->getExt($source);

            //Update filename
            $mediaFile->filename = $filename;

            $destination = JPATH_SITE . $model->getFolder()->folderpath . $filename;

            if (!$jFile->copy($source, $destination)) {
                $errors[] = "Cannot move file";
            }
        }

        $teacher->teachername = $file[7];
        $location->location_text = $file[8];
        $series->series_text = $file[9];
        $topic->topic_text = $file[10];
        $type->message_type = $file[11];
        $server->server_name = $file[16];
        $folder->foldername = $file[17];
        $podcast->title = $file[18];
        $mimeType->mimetype = $file[19];

        //Save everything
        $model->addTeacher($teacher);
        $model->addLocation($location);
        $model->addSeries($series);
        $model->addTopic($topic);
        $model->addType($type);
        $model->addServer($server);
        $model->addFolder($folder);
        $model->addPodcast($podcast);
        $model->addMimeType($mimeType);
        $model->addStudy($study);
        $model->addMediaFile($mediaFile);



        echo json_encode($errors);
    }

}