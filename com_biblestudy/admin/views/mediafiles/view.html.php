<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;


/**
 * View class for Mediafiles
 *
 * @package  BibleStudy.Admin
 * @since    7.0
 */
class BiblestudyViewMediafiles extends JViewLegacy
{

	/**
	 * Items
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Pagination
	 *
	 * @var array
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var object
	 */
	protected $state;

	/**
	 * Media Types
	 *
	 * @var string
	 */
	public $mediatypes;

	/**
	 * Can Do
	 *
	 * @var object
	 */
	public $canDo;

	/**
	 * Filter Levers
	 *
	 * @var string
	 */
	public $f_levels;

	/**
	 * Side Bare
	 *
	 * @var string
	 */
	public $sidebar;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl  The name of the template file to parse; automatically searches through the template paths.
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
		$this->mediatypes = $this->get('Mediatypes');
		$this->canDo      = JBSMBibleStudyHelper::getActions('', 'mediafile');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			JFactory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');

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

			if (BIBLESTUDY_CHECKREL)
			{
				$this->sidebar = JHtmlSidebar::render();
			}
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
		$user = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		JToolBarHelper::title(JText::_('JBS_CMN_MEDIA_FILES'), 'mp3.png');

		if ($this->canDo->get('core.create'))
		{
			JToolBarHelper::addNew('mediafile.add');
		}
		if ($this->canDo->get('core.edit'))
		{
			JToolBarHelper::editList('mediafile.edit');
		}
		if ($this->canDo->get('core.edit.state'))
		{
			JToolBarHelper::divider();
			JToolBarHelper::publishList('mediafiles.publish');
			JToolBarHelper::unpublishList('mediafiles.unpublish');
			JToolBarHelper::archiveList('mediafiles.archive', 'JTOOLBAR_ARCHIVE');
		}
		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList('', 'mediafiles.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.edit.state'))
		{
			JToolBarHelper::trash('mediafiles.trash');
			JToolBarHelper::divider();
		}

		// Add a batch button
		if ($user->authorise('core.edit'))
		{
			if (BIBLESTUDY_CHECKREL)
			{
				JHtml::_('bootstrap.modal', 'collapseModal');
			}
			$title = JText::_('JBS_CMN_BATCH_LABLE');
			$dhtml = "<button data-toggle=\"modal\" data-target=\"#collapseModal\" class=\"btn btn-small\">
						<i class=\"icon-checkbox-partial\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'batch');
		}
		if (BIBLESTUDY_CHECKREL)
		{
			include_once JPATH_COMPONENT . '/helpers/html/biblestudy.php';

			JHtmlSidebar::setAction('index.php?option=com_biblestudy&view=mediafiles');

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_PUBLISHED'), 'filter_published',
				JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
			);

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_ACCESS'), 'filter_access',
				JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
			);

			JHtmlSidebar::addFilter(
				JText::_('JBS_MED_SELECT'),
				'filter_mediaType',
				JHtml::_('select.options', JBSMBiblestudyHelper::getMediaTypes(), 'value', 'text', $this->state->get('filter.mediaType'))
			);

			JHtmlSidebar::addFilter(
				JText::_('JBS_MED_SELECT_YEAR'),
				'filter_mediaYears',
				JHtml::_('select.options', JBSMBiblestudyHelper::getMediaYears(), 'value', 'text', $this->state->get('filter.mediaYears'))
			);
			JHtmlSidebar::addFilter(
				JText::_('JBS_FILTER_DOWNLOAD'),
				'filter_download',
				JHtml::_('select.options', JHtmlBiblestudy::Link_typelist(), 'value', 'text', $this->state->get('filter.download'))
			);
			JHtmlSidebar::addFilter(
				JText::_('JBS_CMS_FILTER_PLAYER'),
				'filter_player',
				JHtml::_('select.options', JHtmlBiblestudy::playerlist(), 'value', 'text', $this->state->get('filter.player'))
			);
			JHtmlSidebar::addFilter(
				JText::_('JBS_CMN_FILTER_POPUP'),
				'filter_popup',
				JHtml::_('select.options', JHtmlBiblestudy::popuplist(), 'value', 'text', $this->state->get('filter.popup'))
			);
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
		$document->setTitle(JText::_('JBS_TITLE_MEDIA_FILES'));
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
			'study.studytitle'     => JText::_('JBS_CMN_STUDY_TITLE'),
			'mediatype.media_text' => JText::_('JBS_MED_MEDIA_TYPE'),
			'mediafile.filename'   => JText::_('JBS_MED_FILENAME'),
			'mediafile.ordering'   => JText::_('JGRID_HEADING_ORDERING'),
			'mediafile.published'  => JText::_('JSTATUS'),
			'mediafile.id'         => JText::_('JGRID_HEADING_ID')
		);
	}
}
