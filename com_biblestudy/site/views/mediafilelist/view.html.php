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
 * View class for MediaFilelist
 *
 * @property mixed document
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewMediafilelist extends JViewLegacy
{

	/** @var  string Can Do */
	public $canDo;

	/** @var  string Media Types */
	public $mediatypes;

	/** @var  string Page Class SFX */
	public $pageclass_sfx;

	/** @var  string New Link */
	public $newlink;

	/** Items @var JObject */
	protected $items;

	/** Pagination @var array */
	protected $pagination;

	/** State @var object */
	protected $state;

	/** @var  Registry */
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
		$app              = JFactory::getApplication();
		$this->canDo      = JBSMBibleStudyHelper::getActions('', 'mediafilesedit');
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->mediatypes = $this->get('Mediatypes');
		$this->pagination = $this->get('Pagination');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode("\n", $errors), 'error');

			return;
		}

		$language = JFactory::getLanguage();
		$language->load('', JPATH_ADMINISTRATOR, null, true);

		if (!$this->canDo->get('core.edit'))
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		// Create a shortcut to the parameters.
		$this->params = & $this->state->template->params;

		// Render the toolbar on the page. rendering it here means that it is displayed on every view of your component.
		// Puts a new record link at the top of the form
		if ($this->canDo->get('core.create'))
		{
			$this->newlink = '<a href="index.php?option=com_biblestudy&view=mediafileform&task=mediafileform.edit"  class="btn btn-primary">'
				. JText::_('JBS_CMN_NEW') . ' <i class="icon-plus"></i></a>';
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

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
		$title .= ' : ' . JText::_('JBS_TITLE_MEDIA_FILES');

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
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

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'study.studytitle'     => JText::_('JBS_CMN_STUDY_TITLE'),
			'mediafile.ordering'   => JText::_('JGRID_HEADING_ORDERING'),
			'mediafile.id'         => JText::_('JGRID_HEADING_ID')
		);
	}
}
