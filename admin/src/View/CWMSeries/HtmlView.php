<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMSeries;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\MVC\View\GenericDataException;
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
class HtmlView extends BaseHtmlView
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
	 * @var mixed
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
	 * @throws  \Exception
	 */
	public function display($tpl = null): void
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->filterForm    = $this->get('FilterForm');
		$this->canDo         = CWMProclaimHelper::getActions('', 'serie');

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

		ToolbarHelper::title(Text::_('JBS_CMN_SERIES'), 'tree-2 tree-2');

		if ($this->canDo->get('core.create'))
		{
			ToolbarHelper::addNew('cwmserie.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			ToolbarHelper::editList('cwmserie.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::publishList('cwmseries.publish');
			ToolbarHelper::unpublishList('cwmseries.unpublish');
			ToolbarHelper::divider();
			ToolbarHelper::archiveList('cwmseries.archive');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('', 'cwmseries.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.delete'))
		{
			ToolbarHelper::trash('cmwseries.trash');
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
	 * @throws \Exception
	 * @since    7.1.0
	 */
	protected function setDocument(): void
	{
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(Text::_('JBS_TITLE_SERIES'));
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
			'series.ordering'  => Text::_('JGRID_HEADING_ORDERING'),
			'series.published' => Text::_('JSTATUS'),
			'access_level'     => Text::_('JGRID_HEADING_ACCESS'),
			'series.language'  => Text::_('JGRID_HEADING_LANGUAGE'),
			'series.id'        => Text::_('JGRID_HEADING_ID')
		);
	}
}
