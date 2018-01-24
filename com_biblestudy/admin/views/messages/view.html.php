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

/**
 * View class for a list of Messages.
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class BiblestudyViewMessages extends JViewLegacy
{
	/**
	 * Can Do
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $canDo;

	/**
	 * Books
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $books;

	/**
	 * Teachers
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $teachers;

	/**
	 * Series
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $series;

	/**
	 * Message Types
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $messageTypes;

	/**
	 * Years
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $years;

	/**
	 * Filter Levels
	 *
	 * @var array
	 * @since    7.0.0
	 */
	public $f_levels;

	/**
	 * Side Bar
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $sidebar;

	/**
	 * Items
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var object
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
		$items            = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->canDo = JBSMBibleStudyHelper::getActions('', 'message');
		$modelView   = $this->getModel();
		$this->items = $modelView->getTranslated($items);

		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			JFactory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');

			return false;
		}

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

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}

		// Set the document
		$this->setDocument();

		// Display the template
		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	protected function addToolbar()
	{
		$user = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolbar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('JBS_CMN_STUDIES'), 'book book');

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::addNew('message.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			JToolbarHelper::editList('message.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::publishList('messages.publish');
			JToolbarHelper::unpublishList('messages.unpublish');
			JToolbarHelper::divider();
			JToolbarHelper::archiveList('messages.archive');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'messages.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('messages.trash');
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_biblestudy')
			&& $user->authorise('core.edit', 'com_biblestudy')
			&& $user->authorise('core.edit.state', 'com_biblestudy'))
		{
			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
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
		$document->setTitle(JText::_('JBS_TITLE_STUDIES'));
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
			'study.studydate'          => JText::_('JBS_CMN_YEARS'),
			'teachers.teachername'     => JText::_('JBS_CMN_TEACHERS'),
			'series.seriestext'        => JText::_('JBS_CMN_SERIES'),
			'study.ordering'           => JText::_('JGRID_HEADING_ORDERING'),
			'study.published'          => JText::_('JPUBLISHED'),
			'study.id'                 => JText::_('JGRID_HEADING_ID'),
			'access_level'             => JText::_('JGRID_HEADING_ACCESS'),
			'study.language'           => JText::_('JGRID_HEADING_LANGUAGE'),
			'locations.location_text'  => JText::_('JBS_CMN_LOCATIONS'),
			'messageType.message_type' => JText::_('JBS_CMN_MESSAGETYPE')
		);
	}
}
