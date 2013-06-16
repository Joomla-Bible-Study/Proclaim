<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Messages
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewMessagelist extends JViewLegacy
{

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

	/** @var  string Can Do */
	public $canDo;

	/** @var  string Books */
	public $books;

	/** @var  JRegistry Teachers */
	public $teachers;

	/** @var  string Series */
	public $series;

	/** @var  string Message Types */
	public $messageTypes;

	/** @var  string Years */
	public $years;

	/** @var JRegistry Params */
	protected $params;

	/** @var  string New Link */
	public $newlink;

	/** @var  string Document */
	public $document;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$items            = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		// Load the Admin settings and params from the template
		$this->admin = JBSMParams::getAdmin(true);
		$template = JBSMParams::getTemplateparams();

		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadString($template->params);
		$this->params = $registry;

		if (!BIBLESTUDY_CHECKREL)
		{
			$document = JFactory::getDocument();
			$document->addStyleSheet(JURI::base() . 'administrator/templates/system/css/system.css');
			$document->addStyleSheet(JURI::base() . 'administrator/templates/bluestork/css/template.css');
		}
		$this->canDo = JBSMBibleStudyHelper::getActions('', 'message');

		$this->books        = $this->get('Books');
		$this->teachers     = $this->get('Teachers');
		$this->series       = $this->get('Series');
		$this->messageTypes = $this->get('MessageTypes');
		$this->years        = $this->get('Years');
		$modelView          = $this->getModel();
		$this->items        = $modelView->getTranslated($items);

		if (!$this->canDo->get('core.edit'))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'message');

			return;
		}

		// Puts a new record link at the top of the form
		if ($this->canDo->get('core.create'))
		{
			$this->newlink = '<a href="' . JRoute::_('index.php?option=com_biblestudy&view=sermons&task=sermon.edit') . '" class="btn btn-primary">'
				. JText::_('JBS_CMN_NEW') . ' <i class="icon-plus icon-white"></i></a>';
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

		$title = $this->params->def('page_title', '');
		$title .= ' : ' . JText::_('JBS_CMN_MESSAGES_LIST');

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
