<?php
/**
 * Topics html
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
 * View class for Topics
 *
 * @package  Proclaim.Admin
 * @since    7.0
 */
class BiblestudyViewTopics extends JViewLegacy
{
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
	 * @var array
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

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @throws  Exception
	 * @since   11.1
	 * @see     fetch()
	 */
	public function display($tpl = null)
	{
		$items            = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->filterForm = $this->get('FilterForm');
		$this->canDo      = JBSMBibleStudyHelper::getActions('', 'topic');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');

			return false;
		}

		$modelView   = $this->getModel();
		$this->items = $modelView->getTranslated($items);

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

		ToolbarHelper::title(JText::_('JBS_CMN_TOPICS'), 'tags tags');

		if ($this->canDo->get('core.create'))
		{
			$toolbar->addNew('topic.add');
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
			$toolbar->edit('topic.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			$toolbar->divider();
			$toolbar->publish('topics.publish');
			$toolbar->unpublish('topics.unpublish');
			$toolbar->divider();
			$toolbar->archive('topics.archive', 'JTOOLBAR_ARCHIVE');
		}

		if ($this->state->get('filter.published') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
		{
			$toolbar->delete('topics.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED)
		{
			$toolbar->trash('topics.trash')->listCheck(true);
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
		$document->setTitle(JText::_('JBS_TITLE_TOPICS'));
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
			'topic.topic_text' => JText::_('JBS_CMN_TOPICS'),
			'topic.published'  => JText::_('JSTATUS'),
			'topic.id'         => JText::_('JGRID_HEADING_ID')
		);
	}
}