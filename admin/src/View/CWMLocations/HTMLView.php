<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMLocations;

// No Direct Access
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;

/**
 * View class for Locations
 *
 * @package  Proclaim.Admin
 * @since    7.0
 */
class HTMLView extends BaseHtmlView
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
	public $canDo;

	/**
	 * Filter Levels
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $f_levels;

	/**
	 * Side Bar
	 *
	 * @var string
	 * @since 9.0.0
	 */
	public $sidebar;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void  A string if successful, otherwise a JError object.
	 *
	 * @throws \Exception
	 * @since   11.1
	 *
	 * @see     fetch()
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->form        = $this->get('form');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->canDo         = CWMProclaimHelper::getActions('', 'location');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');
		}

		// Levels filter.
		$options   = array();
		$options[] = HtmlHelper::_('select.option', '1', Text::_('J1'));
		$options[] = HtmlHelper::_('select.option', '2', Text::_('J2'));
		$options[] = HtmlHelper::_('select.option', '3', Text::_('J3'));
		$options[] = HtmlHelper::_('select.option', '4', Text::_('J4'));
		$options[] = HtmlHelper::_('select.option', '5', Text::_('J5'));
		$options[] = HtmlHelper::_('select.option', '6', Text::_('J6'));
		$options[] = HtmlHelper::_('select.option', '7', Text::_('J7'));
		$options[] = HtmlHelper::_('select.option', '8', Text::_('J8'));
		$options[] = HtmlHelper::_('select.option', '9', Text::_('J9'));
		$options[] = HtmlHelper::_('select.option', '10', Text::_('J10'));

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
		ToolbarHelper::title(Text::_('JBS_CMN_LOCATIONS'), 'home home');

		if ($this->canDo->get('core.create'))
		{
			ToolBarHelper::addNew('location.add');
		}

		$dropdown = $toolbar->dropdownButton('status-group')
			->text('JTOOLBAR_CHANGE_STATUS')
			->toggleSplit(false)
			->icon('icon-ellipsis-h')
			->buttonClass('btn btn-action')
			->listCheck(true);
		$childBar = $dropdown->getChildToolbar();

		if ($this->canDo->get('core.edit'))
		{
			$toolbar->edit('location.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			$toolbar->divider();
			$toolbar->publish('locations.publish');
			$toolbar->unpublish('locations.unpublish');
			$toolbar->divider();
			$toolbar->archive('locations.archive');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			$toolbar->delete('', 'locations.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED)
		{
			$toolbar->trash('locations.trash')->listCheck(true);
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
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(Text::_('JBS_TITLE_LOCATIONS'));
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
			'location.location_text' => Text::_('JGRID_HEADING_ORDERING'),
			'location.published'     => Text::_('JSTATUS'),
			'access_level'           => Text::_('JGRID_HEADING_ACCESS'),
			'location.id'            => Text::_('JGRID_HEADING_ID')
		);
	}
}
