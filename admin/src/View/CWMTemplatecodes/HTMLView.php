<?php
/**
 * TemplateCode html
 *
 * @package    BibleStudy
 * @copyright  (C) 2007 - 2012 CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * @since      7.1.0
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMTemplatecodes;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die;

/**
 * View class for TemplateCodes
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class HTMLView extends BaseHtmlView
{
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
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void  A string if successful, otherwise a JError object.
	 *
	 * @throws \Exception
	 * @since   11.1
	 * @see     fetch()
	 */
	public function display($tpl = null)
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->filterForm    = $this->get('FilterForm');
		$this->canDo      = CWMProclaimHelper::getActions('', 'templatecode');

		foreach ($this->items as $item)
		{
			switch ($item->type)
			{
				case 1:
					$item->typetext = Text::_('JBS_TPLCODE_SERMONLIST');
					break;
				case 2:
					$item->typetext = Text::_('JBS_TPLCODE_SERMON');
					break;
				case 3:
					$item->typetext = Text::_('JBS_TPLCODE_TEACHERS');
					break;
				case 4:
					$item->typetext = Text::_('JBS_TPLCODE_TEACHER');
					break;
				case 5:
					$item->typetext = Text::_('JBS_TPLCODE_SERIESDISPLAYS');
					break;
				case 6:
					$item->typetext = Text::_('JBS_TPLCODE_SERIESDISPLAY');
					break;
				case 7:
					$item->typetext = Text::_('JBS_TPLCODE_MODULE');
					break;
			}
		}

		// Levels filter.
		$options   = [];
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
	 * Add Toolbar
	 *
	 * @return void
	 *
	 * @since 7.1.0
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('JBS_TPLCODE_TPLCODES'), 'stack stack');

		if ($this->canDo->get('core.create'))
		{
			ToolbarHelper::addNew('templatecode.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			ToolbarHelper::editList('templatecode.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::publishList('templatecodes.publish');
			ToolbarHelper::unpublishList('templatecodes.unpublish');
			ToolbarHelper::divider();
			ToolbarHelper::archiveList('templatecodes.archive');
		}

		if ($this->state->get('filter.published') === "-2" && $this->canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('', 'templatecodes.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('templatecodes.trash');
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
		$document->setTitle(Text::_('JBS_TITLE_TEMPLATECODES'));
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
			'study.studytitle'     => Text::_('JBS_CMN_STUDY_TITLE'),
			'mediatype.media_text' => Text::_('JBS_MED_MEDIA_TYPE'),
			'mediafile.filename'   => Text::_('JBS_MED_FILENAME'),
			'mediafile.ordering'   => Text::_('JGRID_HEADING_ORDERING'),
			'mediafile.published'  => Text::_('JSTATUS'),
			'mediafile.id'         => Text::_('JGRID_HEADING_ID')
		);
	}
}