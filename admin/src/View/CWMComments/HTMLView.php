<?php
/**
 * View html
 *
 * @package    BibleStudy
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMComments;

// No Direct Access
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
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
	 * @return  void  A string if successful, otherwise a JError object.
	 *
	 * @throws \Exception
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
		}

		// Levels filter.
		$options   = array();
		$options[] = HTMLHelper::_('select.option', '1', Text::_('J1'));
		$options[] = HTMLHelper::_('select.option', '2', Text::_('J2'));
		$options[] = HTMLHelper::_('select.option', '3', Text::_('J3'));
		$options[] = HTMLHelper::_('select.option', '4', Text::_('J4'));
		$options[] = HTMLHelper::_('select.option', '5', Text::_('J5'));
		$options[] = HTMLHelper::_('select.option', '6', Text::_('J6'));
		$options[] = HTMLHelper::_('select.option', '7', Text::_('J7'));
		$options[] = HTMLHelper::_('select.option', '8', Text::_('J8'));
		$options[] = HTMLHelper::_('select.option', '9', Text::_('J9'));
		$options[] = HTMLHelper::_('select.option', '10', Text::_('J10'));

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
	 * @throws \Exception
	 * @since 7.0
	 */
	protected function addToolbar()
	{
		$user = $user = Factory::getApplication()->getSession()->get('user');

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');
		$canDo   = CWMProclaimHelper::getActions('', 'comment');
		ToolbarHelper::title(Text::_('JBS_CMN_COMMENTS'), 'comments-2 comments-2');

		if ($canDo->get('core.create'))
		{
			$toolbar->addNew('cwmcomment.add');
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
			$toolbar->edit('cwmcomment.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			$toolbar->divider();
			$toolbar->publish('cwmcomments.publish');
			$toolbar->unpublish('cwmcomments.unpublish');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			$toolbar->divider();
			$toolbar->delete('', 'cwmcomments.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED)
		{
			$childBar->trash('cwmcomments.trash');
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
	 * @throws \Exception
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(Text::_('JBS_TITLE_COMMENTS'));
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
			'comment.full_name' => Text::_('JBS_CMT_FULL_NAME'),
			'comment.published' => Text::_('JSTATUS'),
			'study.studytitle'  => Text::_('JBS_CMN_TITLE'),
			'comment.language'  => Text::_('JGRID_HEADING_LANGUAGE'),
			'access_level'      => Text::_('JGRID_HEADING_ACCESS'),
			'comment.id'        => Text::_('JGRID_HEADING_ID')
		);
	}
}
