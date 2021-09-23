<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMMediaFiles;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Extension\ProclaimComponent;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use JHtml;
use JHtmlSidebar;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

defined('_JEXEC') or die;

/**
 * View class for Mediafiles
 *
 * @package  Proclaim.Admin
 * @since    7.0
 */
class HTMLView extends BaseHtmlView
{
	/**
	 * Media Types
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $mediatypes;

	/**
	 * Can Do
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $canDo;

	/**
	 * Filter Levers
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $f_levels;

	/**
	 * Side Bare
	 *
	 * @var string
	 * @since    7.0.0
	 */
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
	 * @throws  \Exception
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->mediatypes = $this->get('Mediatypes');

		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$this->sortDirection = $this->state->get('list.direction');
		$this->sortColumn    = $this->state->get('list.ordering');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');

			return false;
		}

		// Levels filter.
		$options   = array();
		$options[] = JHtml::_('select.option', '1', Text::_('J1'));
		$options[] = JHtml::_('select.option', '2', Text::_('J2'));
		$options[] = JHtml::_('select.option', '3', Text::_('J3'));
		$options[] = JHtml::_('select.option', '4', Text::_('J4'));
		$options[] = JHtml::_('select.option', '5', Text::_('J5'));
		$options[] = JHtml::_('select.option', '6', Text::_('J6'));
		$options[] = JHtml::_('select.option', '7', Text::_('J7'));
		$options[] = JHtml::_('select.option', '8', Text::_('J8'));
		$options[] = JHtml::_('select.option', '9', Text::_('J9'));
		$options[] = JHtml::_('select.option', '10', Text::_('J10'));

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
		$canDo = ContentHelper::getActions('com_proclaim');
		$user = Factory::getApplication()->getIdentity();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('JBS_CMN_MEDIA_FILES'), 'video video');

		if ($canDo->get('core.create'))
		{
			$toolbar->addNew('cwmmediafile.add');
		}

		$dropdown = $toolbar->dropdownButton('status-group')
			->text('JTOOLBAR_CHANGE_STATUS')
			->toggleSplit(false)
			->icon('icon-ellipsis-h')
			->buttonClass('btn btn-action')
			->listCheck(true);

		$childBar = $dropdown->getChildToolbar();

		if ($canDo->get('core.edit.state'))
		{
			$toolbar->edit('cwmmediafile.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			$toolbar->divider();
			$toolbar->publish('cwmmediafiles.publish');
			$toolbar->unpublish('cwmmediafiles.unpublish');
			$toolbar->divider();
			$toolbar->archive('cwmmediafiles.archive');
		}

		if ($this->state->get('filter.published') == ProclaimComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
		{
			$toolbar->delete('cwmmediafiles.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED)
		{
			$childBar->trash('cwmmediafiles.trash')->listCheck(true);
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

		$toolbar->help('JHELP_CONTENT_ARTICLE_MANAGER');
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
		$document->setTitle(Text::_('JBS_TITLE_MEDIA_FILES'));
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
			'study.studytitle'     => Text::_('JBS_CMN_STUDY_TITLE'),
			'mediatype.media_text' => Text::_('JBS_MED_MEDIA_TYPE'),
			'mediafile.filename'   => Text::_('JBS_MED_FILENAME'),
			'mediafile.ordering'   => Text::_('JGRID_HEADING_ORDERING'),
			'mediafile.published'  => Text::_('JSTATUS'),
			'mediafile.id'         => Text::_('JGRID_HEADING_ID')
		);
	}
}
