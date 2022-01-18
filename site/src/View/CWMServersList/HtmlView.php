<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\View\CWMServersList;
// No Direct Access
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use CWM\Component\Proclaim\Site\Helper\CWMListing;
use CWM\Component\Proclaim\Site\Helper\CWMShowScripture;
use CWM\Component\Proclaim\Site\Helper\CWMHelperRoute;
use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use CWM\Component\Proclaim\Site\Helper\CWMRelatedstudies;
use CWM\Component\Proclaim\Site\Helper\CWMPagebuilder;
use CWM\Component\Proclaim\Site\Helper\CWMPodcastsubscribe;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

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
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->canDo      = CWMProclaimHelper::getActions('', 'server');
		$this->types      = $this->get('ServerOptions');

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

			//$this->sidebar = HtmlHelperSidebar::render();
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
		$user = Factory::getUser();

		// Get the toolbar object instance
		$bar = JToolbar::getInstance('toolbar');

		JToolbarHelper::title(Text::_('JBS_CMN_SERVERS'), 'servers.png');

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::addNew('server.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			JToolbarHelper::editList('server.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::publishList('servers.publish');
			JToolbarHelper::unpublishList('servers.unpublish');
			JToolbarHelper::divider();
			JToolbarHelper::archiveList('servers.archive');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'servers.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.delete'))
		{
			JToolbarHelper::trash('servers.trash');
		}

		// Add a batch button
		if ($user->authorise('core.edit'))
		{
			JToolbarHelper::divider();
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
	protected function setDocument()
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
	protected function getSortFields()
	{
		return array(
			'servers.server_name' => Text::_('JGRID_HEADING_ORDERING'),
			'servers.published'   => Text::_('JSTATUS'),
			'servers.id'          => Text::_('JGRID_HEADING_ID')
		);
	}
}
