<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMMessages;

defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Extension\ProclaimComponent;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
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
class HTMLView extends BaseHtmlView
{
	/**
	 * Items
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected array $items;

	/**
	 * Pagination
	 *
	 * @var      \Joomla\CMS\Pagination\Pagination
	 * @since    7.0.0
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var  \Joomla\CMS\Object\CMSObject
	 * @since    7.0.0
	 */
	protected $state;

	/**
	 * Active Filters
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected array $activeFilters;

	/**
	 * Filters of the Form
	 *
	 * @var  \Joomla\CMS\Form\Form
	 * @since    7.0.0
	 */
	public $filterForm;

	/**
	 * All transition, which can be executed of one if the items
	 *
	 * @var  array
	 * @since 4.0.0
	 */
	protected array $transitions = [];

	/**
	 * Is this view an Empty State
	 *
	 * @var   boolean
	 * @since 4.0.0
	 */
	private $isEmptyState = false;

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
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->canDo         = CWMProclaimHelper::getActions('', 'message');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (ComponentHelper::getParams('com_proclaim')->get('workflow_enabled'))
		{
			PluginHelper::importPlugin('workflow');

			$this->transitions = $this->get('Transitions');
		}

		// Check for errors.
		if (\count($errors = $this->get('Errors')) || $this->transitions === false)
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
				unset($this->activeFilters['language']);
				$this->filterForm->removeField('language', 'filter');
			}
		}
		else
		{
			// In article associations modal we need to remove language filter if forcing a language.
			// We also need to change the category filter to show show categories with All or the forced language.
			if ($forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
			{
				// If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
				$languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
				$this->filterForm->setField($languageXml, 'filter', true);

				// Also, unset the active language filter so the search tools is not open by default with this filter.
				unset($this->activeFilters['language']);

				// One last changes needed is to change the category filter to just show categories with All language or with the forced language.
				$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
			}
		}

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
		$user  = $this->getCurrentUser();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('JBS_CMN_STUDIES'), 'book book');

		if ($canDo->get('core.create'))
		{
			$toolbar->addNew('cwmmessage.add');
		}

		if (!$this->isEmptyState && ($canDo->get('core.edit.state') || \count($this->transitions)))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if (\count($this->transitions))
			{
				$childBar->separatorButton('transition-headline')
					->text('COM_PROCLAIM_RUN_TRANSITIONS')
					->buttonClass('text-center py-2 h3');

				$cmd      = "Joomla.submitbutton('cwmmessages.runTransition');";
				$messages = "{error: [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
				$alert    = 'Joomla.renderMessages(' . $messages . ')';
				$cmd      = 'if (document.adminForm.boxchecked.value == 0) { ' . $alert . ' } else { ' . $cmd . ' }';

				foreach ($this->transitions as $transition)
				{
					$childBar->standardButton('transition')
						->text($transition['text'])
						->buttonClass('transition-' . (int) $transition['value'])
						->icon('icon-project-diagram')
						->onclick('document.adminForm.transition_id.value=' . (int) $transition['value'] . ';' . $cmd);
				}

				$childBar->separatorButton('transition-separator');
			}

			if ($canDo->get('core.edit.state'))
			{
				$childBar->publish('cwmmessages.publish')->listCheck(true);

				$childBar->unpublish('cwmmessages.unpublish')->listCheck(true);

				$childBar->archive('cwmmessages.archive')->listCheck(true);

				$childBar->checkin('cwmmessages.checkin')->listCheck(true);

				if ($this->state->get('filter.published') != ProclaimComponent::CONDITION_TRASHED)
				{
					$childBar->trash('cwmmessages.trash')->listCheck(true);
				}
			}

			// Add a batch button
			if ($user->authorise('core.create', 'com_proclaim')
				&& $user->authorise('core.edit', 'com_proclaim')
				&& $user->authorise('core.execute.transition', 'com_proclaim')
			)
			{
				$childBar->popupButton('batch')
					->text('JTOOLBAR_BATCH')
					->selector('collapseModal')
					->listCheck(true);
			}
		}

		if (!$this->isEmptyState && $this->state->get('filter.published') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
		{
			$toolbar->delete('cwmmessages.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($user->authorise('core.admin', 'com_proclaim') || $user->authorise('core.options', 'com_proclaim'))
		{
			$toolbar->preferences('com_proclaim');
		}

		$toolbar->help('Messages', true);
	}
}
