<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmmessages;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Extension\ProclaimComponent;
use CWM\Component\Proclaim\Administrator\Model\CwmmessagesModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Button\DropdownButton;
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
class HtmlView extends BaseHtmlView
{
    /**
     * Filters of the Form
     *
     * @var  Form
     * @since    7.0.0
     */
    public Form $filterForm;
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
     * @var      Pagination
     * @since    7.0.0
     */
    protected Pagination $pagination;
    /**
     * State
     *
     * @var  mixed
     * @since    7.0.0
     */
    protected mixed $state;
    /**
     * Active Filters
     *
     * @var array
     * @since    7.0.0
     */
    public array $activeFilters;
    /**
     * Can Do
     *
     * @var ?object
     * @since    7.0.0
     */
    public ?object $canDo = null;

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
     * @var   bool
     * @since 4.0.0
     */
    private bool $isEmptyState = false;

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
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmmessagesModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->canDo         = ContentHelper::getActions('com_proclaim', 'message');
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        if (ComponentHelper::getParams('com_proclaim')->get('workflow_enabled')) {
            PluginHelper::importPlugin('workflow');

            $this->transitions = $model->getTransitions();
        }

        // Check for errors.
        if ($this->transitions === false || \count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();

            // We do not need to filter by language when multilingual is disabled
            if (!Multilanguage::isEnabled()) {
                unset($this->activeFilters['language']);
                $this->filterForm->removeField('language', 'filter');
            }
        } elseif ($forcedLanguage = Factory::getApplication()->getInput()->get('forcedLanguage', '', 'CMD')) {
            // If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
            $languageXml = new \SimpleXMLElement(
                '<field name="language" type="hidden" default="' . $forcedLanguage . '" />'
            );
            $this->filterForm->setField($languageXml, 'filter', true);

            // Also, unset the active language filter so the search tools is not open by default with this filter.
            unset($this->activeFilters['language']);
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
        $canDo   = ContentHelper::getActions('com_proclaim');
        $user    = $this->getCurrentUser();
        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(Text::_('JBS_CMN_STUDIES'), 'book book');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('cwmmessage.add');
            $toolbar->linkButton('wizard', 'JBS_CMN_QUICK_CREATE')
                ->url(Route::_('index.php?option=com_proclaim&view=cwmmessage&layout=wizard', false))
                ->icon('icon-magic');
        }

        if (!$this->isEmptyState && ($canDo->get('core.edit.state') || \count($this->transitions))) {
            /** @var  DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if (\count($this->transitions)) {
                $childBar->separatorButton('transition-headline')
                    ->text('COM_PROCLAIM_RUN_TRANSITIONS')
                    ->buttonClass('text-center py-2 h3');

                $cmd      = "Joomla.submitbutton('cwmmessages.runTransition');";
                $messages = "{error: [Joomla.Text._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
                $alert    = 'Joomla.renderMessages(' . $messages . ')';
                $cmd      = 'if (document.adminForm.boxchecked.value == 0) { ' . $alert . ' } else { ' . $cmd . ' }';

                foreach ($this->transitions as $transition) {
                    $childBar->standardButton('transition')
                        ->text($transition['text'])
                        ->buttonClass('transition-' . (int)$transition['value'])
                        ->icon('icon-project-diagram')
                        ->onclick('document.adminForm.transition_id.value=' . (int)$transition['value'] . ';' . $cmd);
                }

                $childBar->separatorButton('transition-separator');
            }

            if ($canDo->get('core.edit.state')) {
                $childBar->publish('cwmmessages.publish')->listCheck(true);

                $childBar->unpublish('cwmmessages.unpublish')->listCheck(true);

                $childBar->archive('cwmmessages.archive')->listCheck(true);

                $childBar->checkin('cwmmessages.checkin')->listCheck(true);

                if ((int) $this->state->get('filter.published') !== ProclaimComponent::CONDITION_TRASHED) {
                    $childBar->trash('cwmmessages.trash')->listCheck(true);
                }
            }

            // Add a batch button
            if (
                $user->authorise('core.create', 'com_proclaim')
                && $user->authorise('core.edit', 'com_proclaim')
                && $user->authorise('core.execute.transition', 'com_proclaim')
            ) {
                $childBar->popupButton('batch')
                    ->text('JTOOLBAR_BATCH')
                    ->selector('collapseModal')
                    ->listCheck(true);
            }
        }

        if (
            !$this->isEmptyState && $this->state->get(
                'filter.published'
            ) === ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete')
        ) {
            $toolbar->delete('cwmmessages.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);

            // Delete confirmation dialog for physical files
            $wa = $this->getDocument()->getWebAssetManager();
            $wa->useScript('com_proclaim.delete-confirm');

            Text::script('JBS_DEL_PHYSICAL_FILES_TITLE');
            Text::script('JBS_DEL_PHYSICAL_FILES_WARNING');
            Text::script('JBS_DEL_PHYSICAL_FILES_COUNT');
            Text::script('JBS_DEL_DELETE_EVERYTHING');
            Text::script('JBS_DEL_RECORDS_ONLY');
            Text::script('JCANCEL');
        }

        ToolbarHelper::help('messages', true);
    }
}
