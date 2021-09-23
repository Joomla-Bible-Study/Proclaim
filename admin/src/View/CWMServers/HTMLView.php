<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMServers;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use JHtml;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die;

/**
 * View class for Servers
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
	 * @throws  \Exception
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->canDo         = CWMProclaimHelper::getActions('', 'server');
		$this->types         = $this->get('ServerOptions');
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
		ToolbarHelper::title(Text::_('JBS_CMN_SERVERS'), 'database database');

		if ($canDo->get('core.create'))
		{
			ToolbarHelper::addNew('server.add');
		}

		if ($canDo->get('core.edit'))
		{
			ToolbarHelper::editList('server.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::publishList('servers.publish');
			ToolbarHelper::unpublishList('servers.unpublish');
			ToolbarHelper::divider();
			ToolbarHelper::archiveList('servers.archive');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('', 'servers.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($canDo->get('core.delete'))
		{
			ToolbarHelper::trash('servers.trash');
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
