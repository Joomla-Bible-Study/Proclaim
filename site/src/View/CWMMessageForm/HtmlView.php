<?php
/**
 * Message JViewLegacy
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\View\CWMMessageForm;
// No Direct Access
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Html\HtmlHelper;
use Joomla\CMS\Uri\Uri;
/**
 * View class for Message
 *
 * @property mixed document
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
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
	 * @return  mixed
	 *
	 * @throws  Exception
	 * @since   9.0.0
	 */
	public function display($tpl = null)
	{
		// Get model data.
		$this->state       = $this->get('State');
		$this->item        = $this->get('Item');
		$this->form        = $this->get('Form');
		$this->return_page = $this->get('ReturnPage');

		$input  = Factory::getApplication();
		$option = $input->get('option', '', 'cmd');
		$app    = Factory::getApplication();
		$app->setUserState($option . 'sid', $this->item->id);
		$app->setUserState($option . 'sdate', $this->item->studydate);
		$input->set('sid', $this->item->id);
		$input->set('sdate', $this->item->studydate);
		$this->mediafiles = $this->get('MediaFiles');
		$this->canDo      = CWMProclaimHelper::getActions($this->item->id, 'message');
		$user = Factory::getUser();

		// Create a shortcut to the parameters.
//		$this->params = $this->state->template->params;
		$this->admin = CWMParams::getAdmin();
		$registry    = new Registry;
		$registry->loadString($this->admin->params);
		$this->admin_params = $registry;
		$this->user = $user;
		$this->simple = CWMHelper::getSimpleView();
		$language = Factory::getLanguage();
		$language->load('', JPATH_ADMINISTRATOR, null, true);

//		if (!$this->params->def('page_title', ''))
//		{
//			define('JBSPAGETITLE', 0);
//		}

		if (!$this->canDo->get('core.edit'))
		{
			Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}

		// Escape strings for HTML output
		//$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		$document            = Factory::getDocument();

		HtmlHelper::_('jquery.framework');
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
		//$wa->useScript('com_proclaim.tokeninput');
		//$wa->useStyle('com_proclaim.token-input-jbs');


		$script = "
            jQuery(document).ready(function() {
                jQuery('#topics').tokenInput(" . $this->get('alltopics') . ",
                {
                    theme: 'jbs',
                    hintText: '" . Text::_('JBS_CMN_TOPIC_TAG') . "',
                    noResultsText: '" . Text::_('JBS_CMN_NOT_FOUND') . "',
                    searchingText: '" . Text::_('JBS_CMN_SEARCHING') . "',
                    animateDropdown: false,
                    preventDuplicates: true,
                    allowFreeTagging: true,
                    prePopulate: '" . $this->get('topics') . "'
                });
            });
             ";

		$document->addScriptDeclaration($script);
		//HtmlHelper::_('proclaim.framework');

		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wa->useStyle('com_proclaim.biblestudy');
		$wa->useStyle('com_proclaim.general');
		$this->setLayout('edit');

		$this->_prepareDocument();
		// Set the toolbar
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since 7.0
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		//if ($menu)
		//{
		//	$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		//}
		//else
		//{
		//	$this->params->def('page_heading', Text::_('JBS_FORM_EDIT_ARTICLE'));
		//}

		//$title = $this->params->def('page_title', '');
		$isNew = ($this->item->id == 0);
		$state = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
		$title .= ' : ' . $state . ' : ' . $this->form->getValue('studytitle');

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		$pathway = $app->getPathway();
		$pathway->addItem($title, '');
/*
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
*/
	}

	/**
	 * Adds ToolBar
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since  7.0
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$this->sidebar = \JHtmlSidebar::render();
		$user  = Factory::getUser();
		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');
		$isNew = ($this->item->id == 0);
		$title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
		ToolbarHelper::title(Text::_('JBS_CMN_STUDIES') . ': <small><small>[ ' . $title . ' ]</small></small>', 'book book');

		/*	if ($isNew && $this->canDo->get('core.edit.state', 'com_proclaim'))
			{
				$dropdown = $toolbar->dropdownButton('status-group')
					->text('JTOOLBAR_CHANGE_STATUS')
					->toggleSplit(false)
					->icon('fa fa-ellipsis-h')
					->buttonClass('btn btn-action')
					->listCheck(true);
				$childBar = $dropdown->getChildToolbar();
				$childBar->publish('cwmmessageform.publish')->listCheck(true);
				$childBar->unpublish('cwmmessageform.unpublish')->listCheck(true);
				$childBar->archive('cwmmessageform.archive')->listCheck(true);
			}

				if ($this->state->get('filter.published') != -2)
				{
					$childBar->trash('cwmmessageform.trash')->listCheck(true);
				}
				if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
				{
					$toolbar->delete('cwmmessageform.delete')
						->text('JTOOLBAR_EMPTY_TRASH')
						->message('JGLOBAL_CONFIRM_DELETE')
						->listCheck(true);
				} */
		if ($user->authorise('core.admin', 'com_proclaim') || $user->authorise('core.options', 'com_proclaim'))
		{
			$toolbar->preferences('com_proclaim');
		}


		return Toolbar::getInstance()->render();
	}

}
