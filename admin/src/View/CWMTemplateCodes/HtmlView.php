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

namespace CWM\Component\Proclaim\Administrator\View\CWMTemplateCodes;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for TemplateCodes
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class HtmlView extends BaseHtmlView
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
	public function display($tpl = null): void
	{
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		$this->filterForm    = $this->get('FilterForm');
		$this->canDo      = CWMProclaimHelper::getActions('', 'templatecode');

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

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
	protected function addToolbar(): void
	{
		ToolbarHelper::title(Text::_('JBS_TPLCODE_TPLCODES'), 'stack stack');

		if ($this->canDo->get('core.create'))
		{
			ToolbarHelper::addNew('cwmtemplatecode.add');
		}

		if ($this->canDo->get('core.edit'))
		{
			ToolbarHelper::editList('cwmtemplatecode.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			ToolbarHelper::divider();
			ToolbarHelper::publishList('cwmtemplatecodes.publish');
			ToolbarHelper::unpublishList('cwmtemplatecodes.unpublish');
			ToolbarHelper::divider();
			ToolbarHelper::archiveList('cwmtemplatecodes.archive');
		}

		if ($this->state->get('filter.published') === "-2" && $this->canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('', 'cwmtemplatecodes.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('cwmtemplatecodes.trash');
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
		$document->setTitle(Text::_('JBS_TITLE_TEMPLATECODES'));
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
			'study.studytitle'     => Text::_('JBS_CMN_STUDY_TITLE'),
			'mediatype.media_text' => Text::_('JBS_MED_MEDIA_TYPE'),
			'mediafile.filename'   => Text::_('JBS_MED_FILENAME'),
			'mediafile.ordering'   => Text::_('JGRID_HEADING_ORDERING'),
			'mediafile.published'  => Text::_('JSTATUS'),
			'mediafile.id'         => Text::_('JGRID_HEADING_ID')
		);
	}
}
