<?php
/**
 * Podcasts html
 *
 * @package    BibleStudy
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Podcasts
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyViewPodcasts extends JViewLegacy
{
	/**
	 * Can Do
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $canDo;

	/** @var  array Filter Levels
	 * @since    7.0.0 */
	public $f_levels;

	/** @var  array Side Bar
	 * @since    7.0.0 */
	public $sidebar;

	/**
	 * Items
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $state;

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
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->canDo         = JBSMBibleStudyHelper::getActions('', 'podcast');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Levels filter.
		$options   = array();
		$options[] = JHtml::_('select.option', '1', JText::_('J1'));
		$options[] = JHtml::_('select.option', '2', JText::_('J2'));
		$options[] = JHtml::_('select.option', '3', JText::_('J3'));
		$options[] = JHtml::_('select.option', '4', JText::_('J4'));
		$options[] = JHtml::_('select.option', '5', JText::_('J5'));
		$options[] = JHtml::_('select.option', '6', JText::_('J6'));
		$options[] = JHtml::_('select.option', '7', JText::_('J7'));
		$options[] = JHtml::_('select.option', '8', JText::_('J8'));
		$options[] = JHtml::_('select.option', '9', JText::_('J9'));
		$options[] = JHtml::_('select.option', '10', JText::_('J10'));

		$this->f_levels = $options;

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Add Toolbar
	 *
	 * @return void
	 *
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('JBS_CMN_PODCASTS'), 'feed feed');

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::addNew('podcast.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			JToolbarHelper::editList('podcast.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::publishList('podcasts.publish');
			JToolbarHelper::unpublishList('podcasts.unpublish');
			JToolbarHelper::divider();
			JToolbarHelper::archiveList('podcasts.archive');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'podcasts.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.delete'))
		{
			JToolbarHelper::trash('podcasts.trash');
		}

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::custom('writeXMLFile', 'xml.png', '', 'JBS_PDC_WRITE_XML_FILES', false);
		}
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 *
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_PODCASTS'));
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
			'podcast.title'     => JText::_('JBS_CMN_PODCAST'),
			'podcast.published' => JText::_('JSTATUS'),
			'podcast.language'  => JText::_('JGRID_HEADING_LANGUAGE'),
			'podcast.id'        => JText::_('JGRID_HEADING_ID')
		);
	}
}
