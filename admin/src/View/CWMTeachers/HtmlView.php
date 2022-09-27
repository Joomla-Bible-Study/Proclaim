<?php
/**
 * HTMLView
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMTeachers;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

/**
 * View class for Teachers
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
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
	 * @since    7.0.0
	 */
	protected $f_levels;

	/** @var  object Side Bar
	 * @since    7.0.0
	 */
	protected $sidebar;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void  A string if successful, otherwise a JError object.
	 *
	 * @throws  \Exception
	 * @since   11.1
	 * @see     fetch()
	 */
	public function display($tpl = null): void
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->filterForm = $this->get('FilterForm');
		$this->canDo      = CWMProclaimHelper::getActions('', 'teacher');

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();

			// We do not need to filter by language when multilingual is disabled
			if (!Multilanguage::isEnabled())
			{
				$this->filterForm->removeField('language', 'filter');
			}
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
		$canDo = ContentHelper::getActions('com_proclaim');
		$user  = Factory::getApplication()->getIdentity();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('JBS_CMN_TEACHERS'), 'users users');

		if ($canDo->get('core.create'))
		{
			$toolbar->addNew('cwmteacher.add');
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
			$toolbar->edit('teacher.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			$toolbar->divider();
			$toolbar->publish('cwmteachers.publish');
			$toolbar->unpublish('cwmteachers.unpublish');
			$toolbar->divider();
			$toolbar->archive('cwmteachers.archive');
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			$toolbar->delete('cwmteachers.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}
		elseif ($canDo->get('core.edit.state'))
		{
			$childBar->trash('cwmteachers.trash');
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
	 * @since    7.1.0
	 *
	 */
	protected function setDocument(): void
	{
		$document = Factory::getApplication()->getDocument();
		$document->setTitle(Text::_('JBS_TITLE_TEACHERS'));
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
			'teacher.teachername' => Text::_('JBS_CMN_STUDY_TITLE'),
			'teacher.language'    => Text::_('JGRID_HEADING_LANGUAGE'),
			'teacher.ordering'    => Text::_('JGRID_HEADING_ORDERING'),
			'teacher.published'   => Text::_('JSTATUS'),
			'access_level'        => Text::_('JGRID_HEADING_ACCESS'),
			'teacher.id'          => Text::_('JGRID_HEADING_ID')
		);
	}
}
