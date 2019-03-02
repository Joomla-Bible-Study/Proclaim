<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View class for Messages
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewMessagelist extends JViewLegacy
{
	/** @var  string Can Do
	 *
	 * @since 7.0 */
	public $canDo;

	/** @var  string Books
	 *
	 * @since 7.0 */
	public $books;

	/** @var  Registry Teachers
	 *
	 * @since 7.0 */
	public $teachers;

	/** @var  string Series
	 *
	 * @since 7.0 */
	public $series;

	/** @var  string Message Types
	 *
	 * @since 7.0 */
	public $messageTypes;

	/** @var  string Years
	 *
	 * @since 7.0 */
	public $years;

	/** @var  string New Link
	 *
	 * @since 7.0 */
	public $newlink;

	/** @var  JDocument Document
	 *
	 * @since 7.0 */
	public $document;

	public $filterForm;

	public $activeFilters;

	/**
	 * Items
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $state;

	/**
	 * @var object
	 *
	 * @since 7.0
	 */
	protected $admin;

	/** @var Registry Params
	 *
	 * @since 7.0 */
	protected $params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 *
	 * @since 7.0
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$app              = JFactory::getApplication();
		$items            = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->params     = $this->state->template->params;

		$this->canDo = JBSMBibleStudyHelper::getActions('', 'message');

		$this->books         = $this->get('Books');
		$this->teachers      = $this->get('Teachers');
		$this->series        = $this->get('Series');
		$this->messageTypes  = $this->get('MessageTypes');
		$this->years         = $this->get('Years');
		$modelView           = $this->getModel();
		$this->items         = $modelView->getTranslated($items);
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$language = JFactory::getLanguage();
		$language->load('', JPATH_ADMINISTRATOR, null, true);

		if (!$this->canDo->get('core.edit'))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}

		// Puts a new record link at the top of the form
		if ($this->canDo->get('core.create'))
		{
			$this->newlink = '<a href="' . JRoute::_('index.php?option=com_biblestudy&task=messageform.edit') . '" class="btn btn-primary">'
				. JText::_('JBS_CMN_NEW') . ' <i class="icon-plus"></i></a>';
		}

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @since 7.0
	 * @throws Exception
	 */
	protected function _prepareDocument()
	{
		$app     = JFactory::getApplication('site');
		$menus   = $app->getMenu();
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

		$title = $this->params->def('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
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
			$this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetaData('robots', $this->params->get('robots'));
		}
	}
}
