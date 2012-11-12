<?php

/**
 * MediaFile Model
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Model class for MediaFile
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyModelMediafile extends JModelAdmin {

    /**
     * Admin
     * @var type
     */
    var $_admin;

    /**
     * Context
     * @var type
     */
    var $_text_prefix = 'COM_BIBLESTUDY';

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   11.1
     */
    public function __construct($config = array()) {
        parent::__construct($config);

        /**
         * @todo J16 has new way of retrieving parameters so we need to implement it here too
         */
        jimport('joomla.html.parameter');
        $admin = $this->getLegacyAdmin();

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadString($admin[0]->params);
        $this->admin_params = $registry;

        $array = JRequest::getVar('cid', 0, '', 'array');
        $this->setId((int) $array[0]);
    }
 /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState() {
        $app = JFactory::getApplication('site');
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }
        // Load state from the request. We use a_id to avoid collisions with the router
        $pks = JRequest::getInt('a_id'); 
        $this->pks = $pks;
        $this->setState('mediafile.id', $pks);
    }
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
        return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.mediafilesedit.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     *
     * @since   11.1
     */
    public function getTable($name = 'mediafile', $prefix = 'Table', $options = array()) {
        JTable::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'tables');
        return JTable::getInstance($name, $prefix, $options);
    }

    /**
     * Set ID
     * @param int $id
     */
    public function setId($id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_data = null;
    }

    /**
     * Get Data
     * @return object
     */
    public function &legacygetData() {
        // Load the data
        if (empty($this->_data)) {
            $query = ' SELECT * FROM #__bsms_mediafiles ' .
                    '  WHERE id = ' . $this->_id;
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
        }
        if (!$this->_data) {
            $this->_data = new stdClass();
            $this->_data->id = 0;
            //TF added these
            $today = date("Y-m-d H:i:s");
            $this->_data->published = 1;
            $this->_data->media_image = null;
            $this->_data->server = ($this->_admin_params->get('server') > 0 ? $this->_admin_params->get('server') : null);
            $this->_data->path = ($this->_admin_params->get('path') > 0 ? $this->_admin_params->get('path') : null);
            $this->_data->special = ($this->_admin_params->get('target') != 'No default' ? $this->_admin_params->get('target') : null);
            ;
            $this->_data->filename = null;
            $this->_data->size = null;
            $this->_data->podcast_id = ($this->_admin_params->get('podcast') > 0 ? $this->_admin_params->get('podcast') : null);
            $this->_data->internal_viewer = null;
            $this->_data->mediacode = null;
            $this->_data->ordering = null;
            $this->_data->study_id = null;
            $this->_data->createdate = $today;
            $this->_data->link_type = ($this->_admin_params->get('download') > 0 ? $this->_admin_params->get('download') : null);
            $this->_date->hits = null;
            $this->_data->mime_type = ($this->_admin_params->get('mime') > 0 ? $this->_admin_params->get('mime') : null);
            $this->_data->docMan_id = null;
            $this->_data->article_id = null;
            $this->_data->comment = null;
            $this->_data->virtueMart_id = null;
            $this->_data->params = null;
            $this->_data->player = null;
            $this->_data->popup = null;
        }
        return $this->_data;
    }

    /**
     * Method to store a record
     *
     * @access	public
     * @return	boolean	True on success
     * @todo Need to check the current order of the studies for that particular
     * study, so that it doesn't default to 0, buecause that will break the
     * ordering functionality.
     */
    public function store() {
        $row = & $this->getTable();

        $data = JRequest::get('post');
        //This checks to see if the user has uploaded a file instead of just entered one in the box. It replaces the filename with the name of the uploaded file

        $file = JRequest::getVar('file', null, 'files', 'array');
        $filename_upload = $file['name'];
        if (isset($filename_upload)) {
            $name_bak = $data['filename'];
            $data['filename'] = $filename_upload;
        }
        if ($filename_upload == '') {
            $data['filename'] = $name_bak;
        }

        if ($this->_admin_params->get('character_filter') > 0) {
            $badchars = array(' ', '`', '@', '^', '!', '#', '$', '%', '*', '(', ')', '[', ']', '{', '}', '~', '?', '>', '<', ',', '|', '\\', ';');
            $data['filename'] = str_replace($badchars, '_', $data['filename']);
        }
        $data['filename'] = str_replace('&', '_and_', $data['filename']);
        $data['mediacode'] = str_replace('"', "'", $data['mediacode']);
        // Bind the form fields to the  table
        if ($data['docManItem'] == null) {
            $data['docMan_id'] = 0;
        } else {
            $data['docMan_id'] = $data['docManItem'];
        }
        if ($data['virtueMartItem'] == null) {
            $data['virtueMart_id'] = 0;
        } else {
            $data['virtueMart_id'] = $data['virtueMartItem'];
        }
        if ($data['categoryItem'] == null) {
            $data['article_id'] = 0;
        } else {
            $data['article_id'] = $data['categoryItem'];
        }
        if (is_array($data['podcast_id'])) {
            $data['podcast_id'] = implode(',', $data['podcast_id']);
        }

        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Make sure the  record is valid
        if (!$row->check()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Store the table to the database
        if (!$row->store()) {
            $this->setError($this->_db->getErrorMsg());
            //			$this->setError( $row->getErrorMsg() );
            return false;
        }
        return true;
    }

    /**
     * Method to delete one or more records.
     *
     * @param   array  &$pks  An array of record primary keys.
     *
     * @return  boolean  True if successful, false if an error occurs.
     *
     * @since   11.1
     */
    public function delete(&$pks) {
        $cids = JRequest::getVar('cid', array(0), 'post', 'array');

        $row = & $this->getTable();

        if (count($cids)) {
            foreach ($cids as $cid) {
                if (!$row->delete($cid)) {
                    $this->setError($row->getErrorMsg());
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Lagacy Publish
     * @deprecated since version 7.0.4
     * @param int $cid
     * @param int $publish
     * @return boolean
     */
    public function legacyPublish($cid = array(), $publish = 1) {

        if (count($cid)) {
            $cids = implode(',', $cid);

            $query = 'UPDATE #__bsms_mediafiles'
                    . ' SET published = ' . intval($publish)
                    . ' WHERE id IN ( ' . $cids . ' )'

            ;
            $this->_db->setQuery($query);
            if (!$this->_db->query()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }
    }

    /**
     * Method to move a mediafile listing
     *
     * @param string $direction
     *
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
     * @param   array    $cid
     * @param   array    $pks    An array of primary key ids.
     * @param   integer  $order  +1 or -1
     *
     * @return  mixed
     *
     * @since   11.1
     */
    public function saveorder($cid = array(), $pks = null, $order = null) {
        $row = $this->getTable();
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
     * Get Legacy Admin
     * @deprecated since version 7.0.4
     * @return object
     */
    public function getLegacyAdmin() {
        if (empty($this->_admin)) {
            $query = 'SELECT params'
                    . ' FROM #__bsms_admin'
                    . ' WHERE id = 1';
            $this->_admin = $this->_getList($query);
        }
        return $this->_admin;
    }

    /**
     * @desc Functions to satisfy the ajax requests
     */

    /**
     * Get docMan Categories
     * @return object
     */
    public function getdocManCategories() {
        $query = "SELECT id, title FROM #__categories
				  WHERE `extension` = 'com_content' AND `published`=1";
        return $this->_getList($query);
    }

    /**
     * Get Article Categories
     */
    public function getArticleCategories() {
        $query = "SELECT id, title FROM #__categories WHERE `published`=1";
        return $this->_getList($query);
    }

    /**
     * Get Article Articles
     * @param it $catId
     * @return string
     */
    public function getArticleArticles($catId) {

        $query = "SELECT id, title FROM #__content WHERE `catid` = '$catId' AND `published`=1";
        return json_encode($this->_getList($query));
    }

    /**
     * Get Articles Item
     * @param int $id
     * @return object
     */
    public function getArticlesItem($id) {
        $query = "SELECT title FROM #__content WHERE `id` = '$id'";
        $this->_db->setQuery($query);
        $data = $this->_db->loadRow();
        return $data[0];
    }

    /**
     * Get VirtuMart Categories
     * @return object
     */
    public function getvirtueMartCategories() {
        $query = "SELECT category_id AS id, category_name AS title FROM `#__vm_category` WHERE `category_publish` = 'Y'";
        return $this->_getList($query);
    }

    /**
     * Get DocMan Category items
     * @param int $catId
     * @return string
     */
    public function getdocManCategoryItems($catId) {
        $query = "SELECT id, title as name FROM #__content
				  WHERE `catid`='$catId' AND `published`=1";
        return json_encode($this->_getList($query));
    }

    /**
     * Get Articles Sections
     * @return object
     */
    public function getArticlesSections() {
        $query = "SELECT id, title FROM #__sections WHERE `published` = 1";
        return $this->_getList($query);
    }

    /**
     * Get Articles Section Categories
     * @param int $secId
     * @return string
     */
    public function getArticlesSectionCategories($secId) {
        $query = "SELECT id, title FROM #__categories WHERE `section` = '$secId' AND `published` = 1";
        return json_encode($this->_getList($query));
    }

    /**
     * Get Category Items
     * @param int $catId
     * @return string
     */
    public function getCategoryItems($catId) {
        $query = "SELECT id, title FROM #__content WHERE `state` = 1 AND `catid` = '$catId'";
        $this->getDBO()->setQuery($query);

        //We need to make the result in the right format for the ajax request
        $articles = array("-1" => JText::_("JBS_MED_SELECT_ARTICLE"));
        foreach ($this->getDBO()->loadAssocList() as $article) {
            $articles[$article['id']] = $article['title'];
        }
        return json_encode($articles);
    }

    /**
     * Get VertueMart Items
     * @param int $catId
     * @return string
     */
    public function getVirtueMartItems($catId) {
        $query = "SELECT #__vm_product_category_xref.product_id AS id, #__vm_product.product_name as title
				  FROM #__vm_product_category_xref
				  LEFT JOIN jos_vm_product
				  ON #__vm_product_category_xref.product_id=#__vm_product.product_id
				  WHERE #__vm_product_category_xref.category_id = $catId
				  ORDER BY #__vm_product.product_name ASC LIMIT 0, 30 ";
        return json_encode($this->_getList($query));
    }

    /**
     * Get DocMan Item
     * @param int $id
     * @return object
     */
    public function getDocManItem($id) {
        $query = "SELECT title FROM #__content WHERE `id` = '$id'";
        $this->_db->setQuery($query);
        $data = $this->_db->loadRow();
        return $data[0];
    }

    /**
     * Get Article Item
     * @param string $id
     * @return object
     */
    public function getArticleItem($id) {
        $query = "SELECT title FROM #__content WHERE `id` = '$id'";
        $this->_db->setQuery($query);
        $data = $this->_db->loadRow();
        return $data[0];
    }

    /**
     * Get VirtueMart Item
     * @param int $id
     * @return object
     */
    public function getVirtueMartItem($id) {
        $query = "SELECT product_name AS name FROM #__vm_product WHERE `product_id` = $id";
        $this->_db->setQuery($query);
        $data = $this->_db->loadRow();
        return $data[0];
    }

    /**
     * Get Study
     * @return object
     */
    public function getStudy() {
        $query = 'SELECT id, studytitle, studydate FROM #__bsms_studies ORDER BY id DESC LIMIT 1';
        $this->_db->setQuery($query);
        return $this->_db->loadObject();
    }

    /**
     * Get Studies
     * @return object
     */
    public function getStudies() {
        $query = "SELECT id AS value, CONCAT(studytitle,' - ', date_format(studydate, '%a %b %e %Y'), ' - ', studynumber) AS text FROM #__bsms_studies ORDER BY studydate DESC";
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    /**
     * Get Servers
     * @return object
     */
    public function getServers() {
        $query = 'SELECT id AS value, server_path AS text, published'
                . ' FROM #__bsms_servers'
                . ' WHERE published = 1'
                . ' ORDER BY server_path';
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    /**
     * Get Folders
     * @return Object
     */
    public function getFolders() {
        $query = 'SELECT id AS value, folderpath AS text, published'
                . ' FROM #__bsms_folders'
                . ' WHERE published = 1'
                . ' ORDER BY folderpath';
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    /**
     * Get Podcasts
     * @return object
     */
    public function getPodcasts() {
        $query = 'SELECT id AS value, title AS text FROM #__bsms_podcast WHERE published = 1 ORDER BY title ASC';
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    /**
     * Get Media Images
     * @return object
     */
    public function getMediaImages() {
        $query = 'SELECT id AS value, media_image_name AS text, published'
                . ' FROM #__bsms_media'
                . ' WHERE published = 1'
                . ' ORDER BY media_image_name';
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    /**
     * Get MimeTypes
     * @return object
     */
    public function getMimeTypes() {
        $query = 'SELECT id AS value, mimetext AS text, published FROM #__bsms_mimetype WHERE published = 1 ORDER BY id ASC';
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }

    /**
     * Get Ordering
     * @return string
     */
    public function getOrdering() {
        $query = 'SELECT ordering AS value, ordering AS text'
                . ' FROM #__bsms_mediafiles'
                . ' WHERE study_id = ' . $this->_id
                . ' ORDER BY ordering'
        ;
        return $query;
    }

    /**
     * Overloads the JModelAdmin save routine in order to impload the podcast_id
     *
     * @param array $data
     * @return boolean True on sucessfull save
     * @since   7.0
     */
    public function save($data) {
        //Implode only if they selected at least one podcast. Otherwise just clear the podcast_id field
        $data['podcast_id'] = empty($data['podcast_id']) ? '' : implode(',', $data['podcast_id']);
        $pks = JRequest::getInt('a_id');
        if ($pks) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->clear();
            $query->update('#__bsms_studies');
            $query->set(' study_id = ' . $db->Quote($data['study_id']));
            $query->set(' media_image = ' . $db->Quote($data['media_image']));
            $query->set(' server = ' . $db->Quote($data['server']));
            $query->set(' size = ' . $db->Quote($data['size']));
            $query->set(' mime_type = ' . $db->Quote($data['mime_type']));
            $query->set(' podcast_id = ' . $db->Quote($data['podcast_id']));
            $query->set(' internal_viewer = ' . $db->Quote($data['internal_viewer']));
            $query->set(' mediacode = ' . $db->Quote($data['mediacode']));
            $query->set(' ordering = ' . $db->Quote($data['ordering']));
            $query->set(' createdate = ' . $db->Quote($data['createdate']));
            $query->set(' link_type = ' . $db->Quote($data['link_type']));
            $query->set(' hits = ' . $db->Quote($data['hits']));
            $query->set(' published = ' . $db->Quote($data['published']));
            $query->set(' docMan_id = ' . $db->Quote($data['docMan_id']));
            $query->set(' article_id = ' . $db->Quote($data['article_id']));
            $query->set(' comment = ' . $db->Quote($data['comment']));
            $query->set(' virtueMart_id = ' . $db->Quote($data['virtueMart_id']));
            $query->set(' downloads = ' . $db->Quote($data['downloads']));
            $query->set(' plays = ' . $db->Quote($data['plays']));
            $query->set(' params = ' . $db->Quote($data['params']));
            $query->set(' player = ' . $db->Quote($data['player']));
            $query->set(' popup = ' . $db->Quote($data['popup']));
            $query->set(' asset_id = ' . $db->Quote($data['aset_id']));
            $query->set(' access = ' . $db->Quote($data['access']));
            $query->set(' language = ' . $db->Quote($data['language']));
            $query->set(' created_by = ' . $db->Quote($data['created_by']));
            $query->set(' created_by_alias = ' . $db->Quote($data['created_by_alias']));
            $query->set(' modified = ' . $db->Quote($data['modified']));
            $query->set(' modified_by = ' . $db->Quote($data['modified_by']));
            $query->where(' id =' . (int) $pks . ' LIMIT 1');
            $db->setQuery((string) $query);
            if (!$db->query()) {
                JError::raiseError(500, $db->getErrorMsg());
                return false;
            } else {
                
                return true;
            }
        }
        return parent::save($data);
    }

    /**
     * Method to allow derived classes to preprocess the form.
     *
     * @param   JForm   $form   A JForm object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @see     JFormField
     * @since   11.1
     * @throws  Exception if there is an error in the form event.
     */
    protected function preprocessForm(JForm $form, $data, $group = 'content') {
        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Abstract method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A JForm object on success, false on failure
     *
     * @since   11.1
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
     * Method to get the data that should be injected in the form.
     *
     * @return  array    The default data is an empty array.
     *
     * @since   11.1
     */
    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.mediafile.data', array());
        if (empty($data)) {
            $data = $this->getItem();
            $data->podcast_id = explode(',', $data->podcast_id);
        }


        return $data;
    }

}