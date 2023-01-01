<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\View\CWMServersList;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

/**
 * View class for Servers
 *
 * @package  Proclaim.Admin
 * @since    7.0
 */
class HtmlView extends BaseHtmlView
{
	public $activeFilters;

	public $filterForm;

	public $types;

	/**
	 * Items
	 *
	 * @var object
	 *
	 * @since 7.0
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var object
	 *
	 * @since 7.0
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var object
	 *
	 * @since 7.0
	 */
	protected $state;

	/**
	 * Can Do
	 *
	 * @var object
	 *
	 * @since 7.0
	 */
	protected $canDo;

	/** @var  array Filter Levels
	 *
	 * @since 7.0 */
	protected $f_levels;

	/** @var  object Side Bar
	 *
	 * @since 7.0 */
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
	 */
	public function display($tpl = null): void
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->canDo      = CWMProclaimHelper::getActions('', 'server');
		$this->types      = $this->get('ServerOptions');

		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

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
	protected function addToolbar(): void
	{
		$user = Factory::getApplication()->getSession()->get('user');

		// Get the toolbar object instance
		$bar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('JBS_CMN_SERVERS'), 'servers.png');

		if ($this->canDo->get('core.create'))
		{
			ToolbarHelper::addNew('cwmserver.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			ToolbarHelper::editList('cwmserver.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::publishList('cwmservers.publish');
			ToolbarHelper::unpublishList('cwmservers.unpublish');
			ToolbarHelper::divider();
			ToolbarHelper::archiveList('cwmservers.archive');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('', 'cwmservers.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.delete'))
		{
			ToolbarHelper::trash('cwmservers.trash');
		}

		// Add a batch button
		if ($user->authorise('core.edit'))
		{
			ToolbarHelper::divider();
			HtmlHelper::_('bootstrap.modal', 'collapseModal');

			$title = Text::_('JBS_CMN_BATCH_LABLE');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
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
	protected function setDocument(): void
	{
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(Text::_('JBS_TITLE_SERVERS'));
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields(): array
	{
		return array(
			'servers.server_name' => Text::_('JGRID_HEADING_ORDERING'),
			'servers.published'   => Text::_('JSTATUS'),
			'servers.id'          => Text::_('JGRID_HEADING_ID')
		);
	}
}
