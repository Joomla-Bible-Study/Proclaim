<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmseries;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Model\CwmseriesModel;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\State;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for Series
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
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
     * @var Pagination
     * @since    7.0.0
     */
    protected Pagination $pagination;

    /**
     * State
     *
     * @var State|\Joomla\Registry\Registry
     * @since    7.0.0
     */
    protected State|\Joomla\Registry\Registry $state;

    /**
     * Filter Form
     *
     * @var Form
     * @since    7.0.0
     */
    public Form $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     * @since 4.0.0
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
     * All transitions, which can be executed if the items
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
        /** @var CwmseriesModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();
        $this->canDo         = ContentHelper::getActions('com_proclaim', 'serie');

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // We don't need a toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();

            // We do not need to filter by language when multilingual is disabled
            if (!Multilanguage::isEnabled()) {
                unset($this->activeFilters['language']);
                $this->filterForm->removeField('language', 'filter');
            }
        }

        // Add form control fields
        $this->filterForm
            ->addControlField('task', '')
            ->addControlField('boxchecked', '0');

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
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('JBS_CMN_SERIES'), 'tree-2 tree-2');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('cwmserie.add');
        }

        $dropdown = $toolbar->dropdownButton('status-group')
            ->text('JTOOLBAR_CHANGE_STATUS')
            ->toggleSplit(false)
            ->icon('icon-ellipsis-h')
            ->buttonClass('btn btn-action')
            ->listCheck(true);
        $childBar = $dropdown->getChildToolbar();

        if ($canDo->get('core.edit.state')) {
            $childBar->publish('cwmseries.publish');
            $childBar->unpublish('cwmseries.unpublish');
            $childBar->archive('cwmseries.archive');
            $childBar->checkin('cwmseries.checkin')->listCheck(true);

            if ($canDo->get('core.edit.state')) {
                $childBar->trash('cwmseries.trash');
            }

            // Add a batch button
            if (
                $user->authorise('core.create', 'com_proclaim')
                && $user->authorise('core.edit', 'com_proclaim')
                && $user->authorise('core.edit.state', 'com_proclaim')
            ) {
                $childBar->popupButton('batch')
                    ->text('JTOOLBAR_BATCH')
                    ->selector('collapseModal')
                    ->listCheck(true);
            }
        }

        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            $toolbar->delete('cwmseries.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        $toolbar->help('series', true);
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
        return [
            'serie.topic_text' => Text::_('JBS_CMN_TOPICS'),
            'serie.published'  => Text::_('JSTATUS'),
            'serie.id'         => Text::_('JGRID_HEADING_ID'),
        ];
    }
}
