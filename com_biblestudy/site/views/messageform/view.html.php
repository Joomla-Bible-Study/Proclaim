<?php
/**
 * Message JViewLegacy
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Message
 *
 * @property mixed document
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewMessageform extends JViewLegacy
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
	 * Return Page Item
	 *
	 * @var string
	 */
	protected $return_page_item;

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

	/** @var  string Media Files */
	public $mediafiles;

	/** @var  string Can Do */
	public $canDo;

	/** @var  JRegistry Params */
	public $params;

	/** @var  string User */
	public $user;

	/** @var  string Page Class SFX */
	public $pageclass_sfx;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
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

		$input        = new JInput;
		$option       = $input->get('option', '', 'cmd');
		$JApplication = new JApplication;
		$JApplication->setUserState($option . 'sid', $this->item->id);
		$JApplication->setUserState($option . 'sdate', $this->item->studydate);
		$input->set('sid', $this->item->id);
		$input->set('sdate', $this->item->studydate);
		$this->mediafiles = $this->get('MediaFiles');
		$this->canDo      = JBSMBibleStudyHelper::getActions($this->item->id, 'sermon');
		$this->admin      = JBSMParams::getAdmin($isSite = true);

		$user = JFactory::getUser();

		// Create a shortcut to the parameters.
		$params = & $this->state->params;

		if (!$this->admin->params->def('page_title', ''))
		{
			define('JBSPAGETITLE', 0);
		}
		$params->merge($this->admin->params);
		$this->admin->params->merge($params);
		$this->params = $params;
		$this->user   = $user;

		$canDo = JBSMBibleStudyHelper::getActions($this->item->id, 'sermon');

		if (!$canDo->get('core.edit'))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}
		// Create a shortcut to the parameters.
		$params = & $this->state->params;

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));
		$document            = JFactory::getDocument();

		if (!BIBLESTUDY_CHECKREL)
		{
			$document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery.js');
			$document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery-noconflict.js');
			$document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery.ui.core.min.js');
			$document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/bootstrap.js');
		}
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

		if (!BIBLESTUDY_CHECKREL)
		{
			$document->addStyleSheet('media/com_biblestudy/jui/css/bootstrap.css');
			$document->addStyleSheet('media/com_biblestudy/jui/css/chosen.css');
			JHTML::stylesheet('media/com_biblestudy/css/biblestudy-j2.5.css');
		}

		$document->addScript(JURI::base() . 'media/com_biblestudy/js/noconflict.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
		$this->return_page_item = base64_encode(JURI::base() . '/index.php?option=com_biblestudy&view=squeezebox&tmpl=component');

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
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

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('JBS_FORM_EDIT_ARTICLE'));
		}
		if (JBSPAGETITLE)
		{
			$title = $this->params->def('page_title', '');
		}
		else
		{
			$title = JText::_('JBS_CMN_JOOMLA_BIBLE_STUDY');
		}
		$isNew = ($this->item->id == 0);
		$state = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		$title .= ' : ' . $state . ' : ' . $this->form->getValue('studytitle');

		if ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
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
