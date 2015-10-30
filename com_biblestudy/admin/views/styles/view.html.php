<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Styles
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewStyles extends JViewLegacy
{

	/**
	 * Can Do
	 *
	 * @var object
	 */
	public $canDo;

	/** @var  array Filter Levels */
	public $f_levels;

	/** @var  object Side Bar */
	public $sidebar;

	/**
	 * Items
	 *
	 * @var object
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var object
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var object
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
	 */
	public function display($tpl = null)
	{
		$this->canDo      = JBSMBibleStudyHelper::getActions('', 'style');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			JFactory::getApplication()->enqueueMessage(implode("\n", $errors) . 'error');

			return false;
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

			$this->sidebar = JHtmlSidebar::render();
		}

		// Set the document
		$this->setDocument();

		// Display the template
		return parent::display($tpl);
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
		JToolBarHelper::title(JText::_('JBS_CMN_STYLES'), 'contract-2 contract-2');

		if ($this->canDo->get('core.create'))
		{
			JToolBarHelper::addNew('style.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			JToolBarHelper::editList('style.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::publishList('styles.publish');
			JToolBarHelper::unpublishList('styles.unpublish');
			JToolBarHelper::divider();
			JToolBarHelper::archiveList('styles.archive');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList('', 'styles.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.edit.state'))
		{
			JToolBarHelper::trash('styles.trash');
		}

		if ($this->canDo->get('core.edit'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::custom('styles.fixcss', 'refresh', 'refresh', 'JBS_STYLE_CSS_FIX', true);
		}

		JHtmlSidebar::setAction('index.php?option=com_biblestudy&view=styles');

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'), 'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_MAX_LEVELS'), 'filter_level',
			JHtml::_('select.options', $this->f_levels, 'value', 'text', $this->state->get('filter.level'))
		);
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
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_STYLES'));
	}

}
