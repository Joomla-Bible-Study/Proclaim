<?php
/**
 * View html
 *
 * @package    BibleStudy
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMComments;

// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;

/**
 * View class for Comments
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HTMLView extends BaseHtmlView
{
	/**
	 * Side Bar
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	public $sidebar;

	/**
	 * Items
	 *
	 * @var array
	 *
	 * @since 9.0.0
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var object
	 *
	 * @since 9.0.0
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var object
	 *
	 * @since 9.0.0
	 */
	protected $state;

	/**
	 * Filter Levels
	 *
	 * @var string
	 *
	 * @since 9.0.0
	 */
	protected $f_levels;

	public $filterForm;

	public $activeFilters;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @throws Exception
	 * @since   11.1
	 * @see     fetch()
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');

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
		}

		// Set the document
		$this->setDocument();

		// Display the template
		parent::display($tpl);
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
		$user = Factory::getUser();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');
		$canDo   = JBSMBibleStudyHelper::getActions('', 'comment');
		ToolbarHelper::title(JText::_('JBS_CMN_COMMENTS'), 'comments-2 comments-2');

		if ($canDo->get('core.create'))
		{
			$toolbar->addNew('comment.add');
		}

		$dropdown = $toolbar->dropdownButton('status-group')
			->text('JTOOLBAR_CHANGE_STATUS')
			->toggleSplit(false)
			->icon('icon-ellipsis-h')
			->buttonClass('btn btn-action')
			->listCheck(true);
		$childBar = $dropdown->getChildToolbar();

		if ($canDo->get('core.edit'))
		{
			$toolbar->edit('comment.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			$toolbar->divider();
			$toolbar->publish('comments.publish');
			$toolbar->unpublish('comments.unpublish');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			$toolbar->divider();
			$toolbar->delete('', 'comments.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED)
		{
			$childBar->trash('comments.trash');
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_proclaim')
			&& $user->authorise('core.edit', 'com_proclaim')
			&& $user->authorise('core.edit.state', 'com_proclaim'))
		{
			$childBar->popupButton('batch')
				->text('JTOOLBAR_BATCH')
				->selector('collapseModal')
				->listCheck(true);
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
		$document = Factory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_COMMENTS'));
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
			'comment.full_name' => JText::_('JBS_CMT_FULL_NAME'),
			'comment.published' => JText::_('JSTATUS'),
			'study.studytitle'  => JText::_('JBS_CMN_TITLE'),
			'comment.language'  => JText::_('JGRID_HEADING_LANGUAGE'),
			'access_level'      => JText::_('JGRID_HEADING_ACCESS'),
			'comment.id'        => JText::_('JGRID_HEADING_ID')
		);
	}
}
