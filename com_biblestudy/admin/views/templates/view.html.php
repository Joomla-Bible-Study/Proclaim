<?php
/**
 * Templates html
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Templates
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewTemplates extends JViewLegacy
{
	/**
	 * State
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $canDo;

	/**
	 * State
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $templates;

	/**
	 * State
	 *
	 * @var array
	 * @since    7.0.0
	 */
	public $f_levels;

	/**
	 * State
	 *
	 * @var object
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

		$this->filterForm = $this->get('FilterForm');
		$this->canDo      = JBSMBibleStudyHelper::getActions('', 'template');

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

		$bar = JToolbar::getInstance('toolbar');
		$url = JRoute::_('index.php?option=com_biblestudy&view=templates&layout=default_export');
		$bar->appendButton('Link', 'export', 'JBS_TPL_IMPORT_EXPORT_TEMPLATE', $url);

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
		JToolbarHelper::title(JText::_('JBS_CMN_TEMPLATES'), 'grid grid');

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::addNew('template.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			JToolbarHelper::editList('template.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::publishList('templates.publish');
			JToolbarHelper::unpublishList('templates.unpublish');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::deleteList('', 'templates.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.delete'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::trash('templates.trash');
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
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_TEMPLATES'));
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
			'template.title'     => JText::_('JBS_TPL_TEMPLATE_ID'),
			'template.published' => JText::_('JSTATUS'),
			'template.id'        => JText::_('JGRID_HEADING_ID')
		);
	}
}
