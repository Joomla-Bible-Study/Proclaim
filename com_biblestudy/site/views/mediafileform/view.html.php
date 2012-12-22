<?php

/**
 * MediaFile JViewLegacy
 *
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

//JLoader::register('JBSAdmin', JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.admin.class.php');
JLoader::register('JBSAdmin', JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.admin.class.php');
//require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');
JLoader::register('JBSMHelper', JPATH_ADMINISTRATOR  . '/helpers/biblestudy.php');
//require_once (JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'upload.php');
JLoader::register('JBSMUpload', dirname(__FILE__)  . '/helpers/upload.php');
JLoader::register('JBSMParams', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/params.php');

/**
 * View class for MediaFile
 *
 * @package BibleStudy.Site
 * @since   7.0.0
 */
class biblestudyViewmediafileform extends JViewLegacy
{

	/**
	 * Form
	 *
	 * @var array
	 */
	protected $form;

	/**
	 * Item
	 *
	 * @var array
	 */
	protected $item;


	/**
	 * Return Page
	 *
	 * @var string
	 */
	protected $return_page;

	/**
	 * State
	 *
	 * @var array
	 */
	protected $state;

	/**
	 * Admin
	 *
	 * @var array
	 */
	protected $admin;

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
	public function display($tpl = null)
	{

		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// Get model data.
		$this->state       = $this->get('State');
		$this->item        = $this->get('Item');
		$this->form        = $this->get('Form');
		$this->return_page = $this->get('ReturnPage');

		$this->canDo = JBSMHelper::getActions($this->item->id, 'mediafilesedit');

		// Create a shortcut to the parameters.
		$params	= &$this->state->params;

		$this->admin = JBSMParams::getAdmin();

		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadString($this->admin->params);
		$this->admin_params = $registry;

		$template = JBSMParams::getTemplateparams();
		$registry = new JRegistry;
		$registry->loadString($template->params);
		$params->merge($registry);

		$this->params = $params;

		$user = JFactory::getUser();


		if (!$this->canDo->get('core.edit')) {
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}
		$document = JFactory::getDocument();
		$host     = JURI::root();
		$document->addScript($host . 'media/com_biblestudy/js/mediafile/submitbutton.js');
		$document->addStyleSheet(JURI::base() . 'administrator/templates/system/css/system.css');
		$document->addStyleSheet(JURI::base() . 'administrator/templates/bluestork/css/template.css');
		$document->addStyleSheet($host . 'media/system/css/modal.css');
		//Needed to load the article field type for the article selector
		JFormHelper::addFieldPath(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_content' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR . 'modal');

		$db = JFactory::getDBO();
		//get server for upload dropdown
		$query = 'SELECT id as value, server_name as text FROM #__bsms_servers WHERE published=1 ORDER BY server_name ASC';
		$db->setQuery($query);
		$db->query();
		// $servers = $db->loadObjectList();
		$server              = array(
			array(
				'value' => '',
				'text'  => JText::_('JBS_MED_SELECT_SERVER')
			),
		);
		$serverlist          = array_merge($server, $db->loadObjectList());
		$idsel               = "'SWFUpload_0'";
		$ref1                = JHTML::_('select.genericList', $serverlist, 'upload_server', 'class="inputbox" onchange="showupload(' . $idsel . ')"' . '', 'value', 'text', '');
		$this->upload_server = $ref1;

		//Get folders for upload dropdown
		$query = 'SELECT id as value, foldername as text FROM #__bsms_folders WHERE published=1 ORDER BY foldername ASC';
		$db->setQuery($query);
		//$db->query();
		$folders             = $db->loadObjectList();
		$folder              = array(
			array(
				'value' => '',
				'text'  => JText::_('JBS_MED_SELECT_FOLDER')
			),
		);
		$folderlist          = array_merge($folder, $db->loadObjectList());
		$idsel               = "'SWFUpload_0'";
		$ref2                = JHTML::_('select.genericList', $folderlist, 'upload_folder', 'class="inputbox" onchange="showupload(' . $idsel . ')"' . '', 'value', 'text', '');
		$this->upload_folder = $ref2;

		$this->setLayout('edit');

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app     = JFactory::getApplication();
		$menus   = $app->getMenu();
		$pathway = $app->getPathway();
		$title   = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('JBS_FORM_EDIT_ARTICLE'));
		}
		if (JBSPAGETITLE) {
			$title = $this->params->def('page_title', '');
		} else {
			$title = JText::_('JBS_CMN_JOOMLA_BIBLE_STUDY');
		}
		$isNew = ($this->item->id == 0);
		$state = $isNew ? JText::_('JBS_CMN_NEW') : JText::sprintf('JBS_CMN_EDIT',$this->form->getValue('studytitle'));
		$title .= ' : ' . $state;
		if ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		} elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		$pathway = $app->getPathWay();
		$pathway->addItem($title, '');

		if ($this->params->get('menu-meta_description')) {
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords')) {
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots')) {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}


}