<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmlocations;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmlocationsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;

/**
 * View class for Locations
 *
 * @package  Proclaim.Admin
 * @since    7.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Can Do
     *
     * @var ?object
     * @since    7.0.0
     */
    public ?object $canDo = null;
    /**
     * Filter Levels
     *
     * @var ?array
     * @since    7.0.0
     */
    public ?array $f_levels = null;
    /**
     * Side Bar
     *
     * @var string
     * @since 9.0.0
     */
    public string $sidebar = '';
    /**
     * Filter Form
     *
     * @var ?\Joomla\CMS\Form\Form
     * @since    7.0.0
     */
    public ?\Joomla\CMS\Form\Form $filterForm = null;
    /**
     * Active Filters
     *
     * @var ?array
     * @since    7.0.0
     */
    public ?array $activeFilters = null;
    /**
     * Items
     *
     * @var ?array
     * @since    7.0.0
     */
    protected ?array $items = null;
    /**
     * Pagination
     *
     * @var ?object
     * @since    7.0.0
     */
    protected ?object $pagination = null;
    /**
     * State
     *
     * @var ?object
     * @since    7.0.0
     */
    protected ?object $state = null;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void  A string if successful, otherwise a JError object.
     *
     * @throws \Exception
     * @since   11.1
     *
     * @see     fetch()
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmlocationsModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();
        $this->canDo         = ContentHelper::getActions('com_proclaim');

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
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
        $user = Factory::getApplication()->getIdentity();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('JBS_CMN_LOCATIONS'), 'home home');

        if ($this->canDo->get('core.create')) {
            $toolbar->addNew('cwmlocation.add');
        }

        $dropdown = $toolbar->dropdownButton('status-group')
            ->text('JTOOLBAR_CHANGE_STATUS')
            ->toggleSplit(false)
            ->icon('icon-ellipsis-h')
            ->buttonClass('btn btn-action')
            ->listCheck(true);
        $childBar = $dropdown->getChildToolbar();

        if ($this->canDo->get('core.edit.state')) {
            $childBar->publish('cwmlocations.publish');
            $childBar->unpublish('cwmlocations.unpublish');
            $childBar->archive('cwmlocations.archive');
            $childBar->checkin('cwmlocations.checkin')->listCheck(true);

            if ((int) $this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED) {
                $childBar->trash('cwmlocations.trash')->listCheck(true);
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

        // Add merge button when user has both delete and edit permissions
        if ($this->canDo->get('core.delete') && $this->canDo->get('core.edit')) {
            $childBar->popupButton('merge')
                ->text('JBS_LOC_MERGE')
                ->selector('mergeModal')
                ->listCheck(true)
                ->icon('icon-copy');
        }

        if ($this->state->get('filter.published') === '-2' && $this->canDo->get('core.delete')) {
            $toolbar->delete('', 'cwmlocations.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        ToolbarHelper::help('locations', true);
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
            'location.location_text' => Text::_('JGRID_HEADING_ORDERING'),
            'location.published'     => Text::_('JSTATUS'),
            'access_level'           => Text::_('JGRID_HEADING_ACCESS'),
            'location.id'            => Text::_('JGRID_HEADING_ID'),
        ];
    }
}
