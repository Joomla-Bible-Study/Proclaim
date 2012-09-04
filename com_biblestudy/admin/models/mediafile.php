<?php

/**
 * Madel for MediaFile Admin
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * MediaFile model class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelMediafile extends JModelAdmin {

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param       array   $data   An array of input data.
     * @param       string  $key    The name of the key for the primary key.
     *
     * @return      boolean
     * @since       1.6
     */
    protected function allowEdit($data = array(), $key = 'id') {
        // Check specific edit permission then general edit permission.
        return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.mediafile.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }

    /**
     * Method to move a mediafile listing
     * @param string $direction
     * @access	public
     * @return	boolean	True on success
     * @since	1.5
     */
    public function move($direction) {
        $row = & $this->getTable();
        if (!$row->load($this->_id)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if (!$row->move($direction, ' study_id = ' . (int) $row->study_id . ' AND published >= 0 ')) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

    /**
     * Saves the manually set order of records.
     *
     * @param   array    $pks    An array of primary key ids.
     * @param   array    $cid
     * @param   integer  $order  +1 or -1
     *
     * @return  mixed
     * @since	11.1
     */
    public function saveorder($pks = null, $cid = array(), $order = null) {
        $row = & $this->getTable();
        $groupings = array();

        // update ordering values
        for ($i = 0; $i < count($cid); $i++) {
            $row->load((int) $cid[$i]);
            // track categories
            $groupings[] = $row->study_id;

            if ($row->ordering != $order[$i]) {
                $row->ordering = $order[$i];
                if (!$row->store()) {
                    $this->setError($this->_db->getErrorMsg());
                    return false;
                }
            }
        }

        // execute updateOrder for each parent group
        $groupings = array_unique($groupings);
        foreach ($groupings as $group) {
            $row->reorder('study_id = ' . (int) $group);
        }

        return true;
    }

    /**
     * Overrides the JModelAdmin save routine in order to implode the podcast_id
     *
     * @param array $data
     * @return <Boolean> True on sucessfull save
     * @since   7.0
     */
    public function save($data) {
        //Implode only if they selected at least one podcast. Otherwise just clear the podcast_id field
        $data['podcast_id'] = empty($data['podcast_id']) ? '' : implode(',', $data['podcast_id']);
        //This code could be uncommented and would remove spaces from filename
        //$data['filename'] = str_replace(' ','_',$data['filename']);
        // Remove starting and traling spaces
        $data['filename'] = trim($data['filename']);
        return parent::save($data);
    }

    /**
     * Preprocess Form
     *
     * @param JForm $form
     * @param array $data
     * @param string $group
     */
    protected function preprocessForm(JForm $form, $data, $group = 'content') {
        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Get the form data
     *
     * @param array $data
     * @param boolean $loadData
     * @return boolean|object
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_biblestudy.mediafile', 'mediafile', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    /**
     * Load Form Data
     * @return array
     * @since   7.0
     */
    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.mediafile.data', array());
        if (empty($data)) {
            $data = $this->getItem();
            $data->podcast_id = explode(',', $data->podcast_id);
        }
        return $data;
    }

    /**
     * Custom clean the cache of com_biblestudy and biblestudy modules
     * @param string $group
     * @param int $client_id
     * @since	1.6
     */
    protected function cleanCache($group = null, $client_id = 0) {
        parent::cleanCache('com_biblestudy');
        parent::cleanCache('mod_biblestudy');
    }

}