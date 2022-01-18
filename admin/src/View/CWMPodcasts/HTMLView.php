<?php
/**
 * Podcasts html
 *
 * @package    BibleStudy
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMPodcasts;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die;

/**
 * View class for Podcasts
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HTMLView extends BaseHtmlView
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
	 * @return  void  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->canDo         = CWMProclaimHelper::getActions('', 'podcast');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

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

		$this->addToolbar();

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
		ToolbarHelper::title(Text::_('JBS_CMN_PODCASTS'), 'feed feed');

		if ($this->canDo->get('core.create'))
		{
			ToolbarHelper::addNew('podcast.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			ToolbarHelper::editList('podcast.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::publishList('podcasts.publish');
			ToolbarHelper::unpublishList('podcasts.unpublish');
			ToolbarHelper::divider();
			ToolbarHelper::archiveList('podcasts.archive');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('', 'podcasts.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.delete'))
		{
			ToolbarHelper::trash('podcasts.trash');
		}

		if ($this->canDo->get('core.create'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::custom('writeXMLFile', 'xml.png', '', 'JBS_PDC_WRITE_XML_FILES', false);
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
		$document->setTitle(Text::_('JBS_TITLE_PODCASTS'));
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
			'podcast.title'     => Text::_('JBS_CMN_PODCAST'),
			'podcast.published' => Text::_('JSTATUS'),
			'podcast.language'  => Text::_('JGRID_HEADING_LANGUAGE'),
			'podcast.id'        => Text::_('JGRID_HEADING_ID')
		);
	}
}
