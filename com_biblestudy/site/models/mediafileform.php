<?php

/**
 * MediaFile Model
 *
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/models/mediafile.php';

/**
 * Model class for MediaFile
 *
 * @package BibleStudy.Site
 * @since   7.0.0
 */
class BiblestudyModelMediafileform extends BiblestudyModelMediafile
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   11.1
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('a_id'); 
		$this->setState('mediafile.id', $pk);

		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->get('layout'));
	}

	/**
	 * Method to get article data.
	 *
	 * @param    integer    The id of the article.
	 *
	 * @return    mixed    Content item data object on success, false on failure.
	 */
	public function getItem($itemId = null)
	{
		// Initialise variables.
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('mediafile.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false) {
			return false;
		}

		$properties = $table->getProperties(1);
		$value = JArrayHelper::toObject($properties, 'JObject');

		// Convert attrib field to Registry.

		// Convert params field to Registry.
		$registry = new JRegistry;
		$registry->loadString($value->params);
		$value->params = $registry->toArray();

		return $value;
	}


	/**
	 * Get the return URL.
	 *
	 * @return    string    The return URL.
	 * @since    1.6
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	/**
	 * @desc Functions to satisfy the ajax requests
	 */

	/**
	 * Get docMan Categories
	 *
	 * @return object
	 */
	public function getdocManCategories()
	{
		$query = "SELECT id, title FROM #__categories
				  WHERE `extension` = 'com_content' AND `published`=1";

		return $this->_getList($query);
	}

	/**
	 * Get Article Categories
	 */
	public function getArticleCategories()
	{
		$query = "SELECT id, title FROM #__categories WHERE `published`=1";

		return $this->_getList($query);
	}

	/**
	 * Get Article Articles
	 *
	 * @param it $catId
	 *
	 * @return string
	 */
	public function getArticleArticles($catId)
	{

		$query = "SELECT id, title FROM #__content WHERE `catid` = '$catId' AND `published`=1";

		return json_encode($this->_getList($query));
	}

	/**
	 * Get Articles Item
	 *
	 * @param int $id
	 *
	 * @return object
	 */
	public function getArticlesItem($id)
	{
		$query = "SELECT title FROM #__content WHERE `id` = '$id'";
		$this->_db->setQuery($query);
		$data = $this->_db->loadRow();

		return $data[0];
	}

	/**
	 * Get VirtuMart Categories
	 *
	 * @return object
	 */
	public function getvirtueMartCategories()
	{
		$query = "SELECT category_id AS id, category_name AS title FROM `#__vm_category` WHERE `category_publish` = 'Y'";

		return $this->_getList($query);
	}

	/**
	 * Get DocMan Category items
	 *
	 * @param int $catId
	 *
	 * @return string
	 */
	public function getdocManCategoryItems($catId)
	{
		$query = "SELECT id, title as name FROM #__content
				  WHERE `catid`='$catId' AND `published`=1";

		return json_encode($this->_getList($query));
	}

	/**
	 * Get Articles Sections
	 *
	 * @return object
	 */
	public function getArticlesSections()
	{
		$query = "SELECT id, title FROM #__sections WHERE `published` = 1";

		return $this->_getList($query);
	}

	/**
	 * Get Articles Section Categories
	 *
	 * @param int $secId
	 *
	 * @return string
	 */
	public function getArticlesSectionCategories($secId)
	{
		$query = "SELECT id, title FROM #__categories WHERE `section` = '$secId' AND `published` = 1";

		return json_encode($this->_getList($query));
	}

	/**
	 * Get Category Items
	 *
	 * @param int $catId
	 *
	 * @return string
	 */
	public function getCategoryItems($catId)
	{
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
	 *
	 * @param int $catId
	 *
	 * @return string
	 */
	public function getVirtueMartItems($catId)
	{
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
	 *
	 * @param int $id
	 *
	 * @return object
	 */
	public function getDocManItem($id)
	{
		$query = "SELECT title FROM #__content WHERE `id` = '$id'";
		$this->_db->setQuery($query);
		$data = $this->_db->loadRow();

		return $data[0];
	}

	/**
	 * Get Article Item
	 *
	 * @param string $id
	 *
	 * @return object
	 */
	public function getArticleItem($id)
	{
		$query = "SELECT title FROM #__content WHERE `id` = '$id'";
		$this->_db->setQuery($query);
		$data = $this->_db->loadRow();

		return $data[0];
	}

	/**
	 * Get VirtueMart Item
	 *
	 * @param int $id
	 *
	 * @return object
	 */
	public function getVirtueMartItem($id)
	{
		$query = "SELECT product_name AS name FROM #__vm_product WHERE `product_id` = $id";
		$this->_db->setQuery($query);
		$data = $this->_db->loadRow();

		return $data[0];
	}

	/**
	 * Get Study
	 *
	 * @return object
	 */
	public function getStudy()
	{
		$query = 'SELECT id, studytitle, studydate FROM #__bsms_studies ORDER BY id DESC LIMIT 1';
		$this->_db->setQuery($query);

		return $this->_db->loadObject();
	}

	/**
	 * Get Studies
	 *
	 * @return object
	 */
	public function getStudies()
	{
		$query = "SELECT id AS value, CONCAT(studytitle,' - ', date_format(studydate, '%a %b %e %Y'), ' - ', studynumber) AS text FROM #__bsms_studies ORDER BY studydate DESC";
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * Get Servers
	 *
	 * @return object
	 */
	public function getServers()
	{
		$query = 'SELECT id AS value, server_path AS text, published'
				. ' FROM #__bsms_servers'
				. ' WHERE published = 1'
				. ' ORDER BY server_path';
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * Get Folders
	 *
	 * @return Object
	 */
	public function getFolders()
	{
		$query = 'SELECT id AS value, folderpath AS text, published'
				. ' FROM #__bsms_folders'
				. ' WHERE published = 1'
				. ' ORDER BY folderpath';
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * Get Podcasts
	 *
	 * @return object
	 */
	public function getPodcasts()
	{
		$query = 'SELECT id AS value, title AS text FROM #__bsms_podcast WHERE published = 1 ORDER BY title ASC';
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * Get Media Images
	 *
	 * @return object
	 */
	public function getMediaImages()
	{
		$query = 'SELECT id AS value, media_image_name AS text, published'
				. ' FROM #__bsms_media'
				. ' WHERE published = 1'
				. ' ORDER BY media_image_name';
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * Get MimeTypes
	 *
	 * @return object
	 */
	public function getMimeTypes()
	{
		$query = 'SELECT id AS value, mimetext AS text, published FROM #__bsms_mimetype WHERE published = 1 ORDER BY id ASC';
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * Get Ordering
	 *
	 * @return string
	 */
	public function getOrdering()
	{
		$query = 'SELECT ordering AS value, ordering AS text'
				. ' FROM #__bsms_mediafiles'
				. ' WHERE study_id = ' . $this->_id
				. ' ORDER BY ordering';

		return $query;
	}

}