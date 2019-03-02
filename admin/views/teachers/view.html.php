<?php
/**
 * JView html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Teachers
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyViewTeachers extends JViewLegacy
{
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
	 * Can Do
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $canDo;

	/** @var  array Filter Levels
	 * @since    7.0.0 */
	protected $f_levels;

	/** @var  object Side Bar
	 * @since    7.0.0 */
	protected $sidebar;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->filterForm = $this->get('FilterForm');
		$this->canDo      = JBSMBibleStudyHelper::getActions('', 'teacher');

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

		JToolbarHelper::title(JText::_('JBS_CMN_TEACHERS'), 'users users');

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::addNew('teacher.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			JToolbarHelper::editList('teacher.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::publishList('teachers.publish');
			JToolbarHelper::unpublishList('teachers.unpublish');
			JToolbarHelper::divider();
			JToolbarHelper::archiveList('teachers.archive');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'teachers.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.edit.state'))
		{
			JToolbarHelper::trash('teachers.trash');
		}

		// Add a batch button
		if ($user->authorise('core.edit'))
		{
			JToolbarHelper::divider();
			JHtml::_('bootstrap.modal', 'collapseModal');

			$title = JText::_('JBS_CMN_BATCH_LABLE');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'batch');
		}
	}

	/**
	 * Add the page title to browser.
	 *
	 * @since    7.1.0
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_TEACHERS'));
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
			'teacher.teachername' => JText::_('JBS_CMN_STUDY_TITLE'),
			'teacher.language'    => JText::_('JGRID_HEADING_LANGUAGE'),
			'teacher.ordering'    => JText::_('JGRID_HEADING_ORDERING'),
			'teacher.published'   => JText::_('JSTATUS'),
			'access_level'        => JText::_('JGRID_HEADING_ACCESS'),
			'teacher.id'          => JText::_('JGRID_HEADING_ID')
		);
	}
}
