<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
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

	/** @var  string Can Do */
	public $canDo;

	/** @var  string Books */
	public $books;

	/** @var  Registry Teachers */
	public $teachers;

	/** @var  string Series */
	public $series;

	/** @var  string Message Types */
	public $messageTypes;

	/** @var  string Years */
	public $years;

	/** @var  string New Link */
	public $newlink;

	/** @var  JDocument Document */
	public $document;

	/**
	 * Items
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var array
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var array
	 */
	protected $state;

	/**
	 * @var object
	 */
	protected $admin;

	/** @var Registry Params */
	protected $params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
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

			return;
		}

		// Puts a new record link at the top of the form
		if ($this->canDo->get('core.create'))
		{
			$this->newlink = '<a href="' . JRoute::_('index.php?option=com_biblestudy&view=sermons&task=sermon.edit') . '" class="btn btn-primary">'
				. JText::_('JBS_CMN_NEW') . ' <i class="icon-plus"></i></a>';
		}

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
