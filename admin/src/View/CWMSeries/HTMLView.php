<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMSeries;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die;

/**
 * View class for Series
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
	 * @var string
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
	 * @throws  \Exception
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->filterForm    = $this->get('FilterForm');
		$this->canDo         = CWMProclaimHelper::getActions('', 'serie');

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
		$user = Factory::getUser();

		// Get the toolbar object instance
		$bar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('JBS_CMN_SERIES'), 'tree-2 tree-2');

		if ($this->canDo->get('core.create'))
		{
			ToolbarHelper::addNew('serie.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			ToolbarHelper::editList('serie.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::publishList('series.publish');
			ToolbarHelper::unpublishList('series.unpublish');
			ToolbarHelper::divider();
			ToolbarHelper::archiveList('series.archive');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('', 'series.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.delete'))
		{
			ToolbarHelper::trash('series.trash');
		}

		// Add a batch button
		if ($user->authorise('core.edit'))
		{
			ToolbarHelper::divider();
			JHtml::_('bootstrap.modal', 'collapseModal');

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
		$document = Factory::getDocument();
		$document->setTitle(Text::_('JBS_TITLE_SERIES'));
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
			'series.ordering'  => Text::_('JGRID_HEADING_ORDERING'),
			'series.published' => Text::_('JSTATUS'),
			'access_level'     => Text::_('JGRID_HEADING_ACCESS'),
			'series.language'  => Text::_('JGRID_HEADING_LANGUAGE'),
			'series.id'        => Text::_('JGRID_HEADING_ID')
		);
	}
}
