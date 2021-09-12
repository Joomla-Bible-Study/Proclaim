<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

/**
 * View class for a list of Messages.
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class BiblestudyViewMessages extends JViewLegacy
{
	/**
	 * Can Do
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $canDo;

	/**
	 * Books
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $books;

	/**
	 * Teachers
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $teachers;

	/**
	 * Series
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $series;

	/**
	 * Message Types
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $messageTypes;

	/**
	 * Years
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $years;

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
	 * @var object
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
	 * @return  void  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->canDo = JBSMBibleStudyHelper::getActions('', 'message');
		$modelView   = $this->getModel();

		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Levels filter.
		$options   = array();
		$options[] = JHtml::_('select.option', '1', JText::_('J1'));
		$options[] = JHtml::_('select.option', '2', JText::_('J2'));
		$options[] = JHtml::_('select.option', '3', JText::_('J3'));
		$options[] = JHtml::_('select.option', '4', JText::_('J4'));
		$options[] = JHtml::_('select.option', '5', JText::_('J5'));
		$options[] = JHtml::_('select.option', '6', JText::_('J6'));
		$options[] = JHtml::_('select.option', '7', JText::_('J7'));
		$options[] = JHtml::_('select.option', '8', JText::_('J8'));
		$options[] = JHtml::_('select.option', '9', JText::_('J9'));
		$options[] = JHtml::_('select.option', '10', JText::_('J10'));

		$this->f_levels = $options;

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();

			// We do not need to filter by language when multilingual is disabled
			if (!Multilanguage::isEnabled())
			{
				unset($this->activeFilters['language']);
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
	 * @since 7.0
	 */
	protected function addToolbar()
	{
		$canDo = ContentHelper::getActions('com_biblestudy', 'category', $this->state->get('filter.category_id'));
		$user = Factory::getUser();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(JText::_('JBS_CMN_STUDIES'), 'book book');

		if ($this->canDo->get('core.create'))
		{
			$toolbar->addNew('message.add');
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
			$toolbar->edit('message.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			$toolbar->divider();
			$toolbar->publish('messages.publish');
			$toolbar->unpublish('messages.unpublish');
			$toolbar->divider();
			$toolbar->archive('messages.archive');
		}

		if ($this->state->get('filter.published') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
		{
			$toolbar->delete('messages.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED)
		{
			$childBar->trash('articles.trash')->listCheck(true);
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_biblestudy')
			&& $user->authorise('core.edit', 'com_biblestudy')
			&& $user->authorise('core.edit.state', 'com_biblestudy'))
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
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$document = Factory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_STUDIES'));
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
			'study.studydate'          => JText::_('JBS_CMN_STUDYDATE'),
			'study.studytitle'         => JText::_('JBS_CMN_TITLE'),
			'teacher.teachername'      => JText::_('JBS_CMN_TEACHERS'),
			'series.series_text'       => JText::_('JBS_CMN_SERIES'),
			'study.ordering'           => JText::_('JGRID_HEADING_ORDERING'),
			'study.published'          => JText::_('JPUBLISHED'),
			'study.id'                 => JText::_('JGRID_HEADING_ID'),
			'access_level'             => JText::_('JGRID_HEADING_ACCESS'),
			'study.language'           => JText::_('JGRID_HEADING_LANGUAGE'),
			'locations.location_text'  => JText::_('JBS_CMN_LOCATIONS'),
			'messageType.message_type' => JText::_('JBS_CMN_MESSAGETYPE')
		);
	}
}
