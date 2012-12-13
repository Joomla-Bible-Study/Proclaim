<?php

/**
 * Message JViewLegacy
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php');
require_once (JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'params.php');

/**
 * View class for Message
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class BiblestudyViewMessageform extends JViewLegacy {

    /**
     * Form
     * @var array
     */
    protected $form;

    /**
     * Item
     * @var array
     */
    protected $item;


	/**
	 * Return Page
	 * @var string
	 */
	protected $return_page;

    /**
     * State
     * @var array
     */
    protected $state;

    /**
     * Admin
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
    public function display($tpl = null) {

	    $app		= JFactory::getApplication();
	    $user		= JFactory::getUser();

	    // Get model data.
	    $this->state		= $this->get('State');
	    $this->item			= $this->get('Item');
	    $this->form			= $this->get('Form');
	    $this->return_page	= $this->get('ReturnPage');

	    if (empty($this->item->id)) {
		    $authorised = $user->authorise('core.create', 'com_biblestudy');
	    }
	    else {
		    $authorised = $this->item->params->get('access-edit');
	    }

        $input = new JInput;
        $option = $input->get('option','','cmd');
        $JApplication = new JApplication();
        $JApplication->setUserState($option . 'sid', $this->item->id);
        $JApplication->setUserState($option . 'sdate', $this->item->studydate);
        $this->mediafiles = $this->get('MediaFiles');
        $this->setLayout('form');
        $this->canDo = JBSMHelper::getActions($this->item->id, 'message');
        $this->loadHelper('params');
        $this->admin = BsmHelper::getAdmin($isSite = true);

        $user = JFactory::getUser();

	    // Create a shortcut to the parameters.
	    $params	= &$this->state->params;

	    $this->params = $params;
	    $this->user   = $user;

        $canDo = JBSMHelper::getActions($this->item->id, 'message');

        if (!$canDo->get('core.edit')) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        $document = JFactory::getDocument();
        $document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery-noconflict.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery-ui.core.min.js');
        $document->addScript(JURI::base() . 'media/com_biblestudy/js/plugins/jquery.tokeninput.js');
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/token-input-jbs.css');
        $document->addStyleSheet(JURI::base() . 'administrator/templates/system/css/system.css');
        $document->addStyleSheet(JURI::base() . 'administrator/templates/bluestork/css/template.css');
        $script = "
            \$j(document).ready(function() {
                \$j('#topics').tokenInput(" . $this->get('alltopics') . ",
                {
                    theme: 'jbs',
                    hintText: '" . JText::_('JBS_CMN_TOPIC_TAG') . "',
                    noResultsText: '" . JText::_('JBS_CMN_NOT_FOUND') . "',
                    searchingText: '" . JText::_('JBS_CMN_SEARCHING') . "',
                    animateDropdown: false,
                    preventDuplicates: true,
                    prePopulate: " . $this->get('topics') . "
                });
            });
             ";

        $document->addScriptDeclaration($script);

        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/js/ui/theme/ui.all.css');
        $document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/jquery.tagit.css');

        $document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');

	    $this->_prepareDocument();
        parent::display($tpl);
    }

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title 		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('JBS_FORM_EDIT_ARTICLE'));
		}

		$title = $this->params->def('page_title', JText::_('JBS_FORM_EDIT_ARTICLE'));
		if ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		$pathway = $app->getPathWay();
		$pathway->addItem($title, '');

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}

}