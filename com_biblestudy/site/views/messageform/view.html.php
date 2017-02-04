<?php
/**
 * Message JViewLegacy
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View class for Message
 *
 * @property mixed document
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewMessageform extends JViewLegacy
{
	/** @var  string Media Files
	 *
	 * @since 7.0 */
	public $mediafiles;

	/** @var  string Can Do
	 *
	 * @since 7.0 */
	public $canDo;

	/** @var  Registry Params
	 *
	 * @since 7.0 */
	public $params;

	/** @var  string User
	 *
	 * @since 7.0 */
	public $user;

	/** @var  string Page Class SFX
	 *
	 * @since 7.0 */
	public $pageclass_sfx;

	/**  Form @var JForm
	 *
	 * @since 7.0 */
	protected $form;

	/** Item @var object
	 *
	 * @since 7.0 */
	protected $item;

	/** Return Page @var string
	 *
	 * @since 7.0 */
	protected $return_page;

	/** Return Page Item @var string
	 *
	 * @since 7.0 */
	protected $return_page_item;

	/** State @var array
	 *
	 * @since 7.0 */
	protected $state;

	/** Admin @var array
	 *
	 * @since 7.0 */
	protected $admin;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// Get model data.
		$this->state       = $this->get('State');
		$this->item        = $this->get('Item');
		$this->form        = $this->get('Form');
		$this->return_page = $this->get('ReturnPage');

		$input  = new JInput;
		$option = $input->get('option', '', 'cmd');
		$app    = JFactory::getApplication();
		$app->setUserState($option . 'sid', $this->item->id);
		$app->setUserState($option . 'sdate', $this->item->studydate);
		$input->set('sid', $this->item->id);
		$input->set('sdate', $this->item->studydate);
		$this->mediafiles = $this->get('MediaFiles');
		$this->canDo      = JBSMBibleStudyHelper::getActions($this->item->id, 'sermon');

		$user = JFactory::getUser();

		// Create a shortcut to the parameters.
		$this->params = $this->state->template->params;

		$this->user = $user;

		$language = JFactory::getLanguage();
		$language->load('', JPATH_ADMINISTRATOR, null, true);

		if (!$this->params->def('page_title', ''))
		{
			define('JBSPAGETITLE', 0);
		}

		$canDo = JBSMBibleStudyHelper::getActions($this->item->id, 'sermon');

		if (!$canDo->get('core.edit'))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		$document            = JFactory::getDocument();

		JHtml::_('jquery.framework');
		$document->addScript(JUri::base() . 'media/com_biblestudy/js/plugins/jquery.tokeninput.js');
		$document->addStyleSheet(JUri::base() . 'media/com_biblestudy/css/token-input-jbs.css');
		$script = "
            jQuery(document).ready(function() {
                jQuery('#topics').tokenInput(" . $this->get('alltopics') . ",
                {
                    theme: 'jbs',
                    hintText: '" . JText::_('JBS_CMN_TOPIC_TAG') . "',
                    noResultsText: '" . JText::_('JBS_CMN_NOT_FOUND') . "',
                    searchingText: '" . JText::_('JBS_CMN_SEARCHING') . "',
                    animateDropdown: false,
                    preventDuplicates: true,
                    allowFreeTagging: true,
                    prePopulate: " . $this->get('topics') . "
                });
            });
             ";

		$document->addScriptDeclaration($script);
		JHtml::_('biblestudy.framework');
		JHtml::_('biblestudy.loadcss', $this->params);

		$this->setLayout('edit');

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

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

		$title = $this->params->def('page_title', '');
		$isNew = ($this->item->id == 0);
		$state = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		$title .= ' : ' . $state . ' : ' . $this->form->getValue('studytitle');

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		$pathway = $app->getPathway();
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
