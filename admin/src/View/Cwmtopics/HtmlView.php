<?php

/**
 * Topics html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmtopics;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmtopicsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
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
class HtmlView extends BaseHtmlView
{
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
     * @since    7.0.0
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
     * Can Do
     *
     * @var ?object
     * @since    7.0.0
     */
    protected ?object $canDo = null;

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
        /** @var CwmtopicsModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $items               = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();
        $this->canDo         = ContentHelper::getActions('com_proclaim');

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->items = $model->getTranslated($items);

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
     * @since 7.0
     */
    protected function addToolbar(): void
    {
        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('JBS_CMN_TOPICS'), 'tags');

        if ($this->canDo->get('core.create')) {
            $toolbar->addNew('cwmtopic.add');
        }

        $dropdown = $toolbar->dropdownButton('status-group')
            ->text('JTOOLBAR_CHANGE_STATUS')
            ->toggleSplit(false)
            ->icon('icon-ellipsis-h')
            ->buttonClass('btn btn-action')
            ->listCheck(true);
        $childBar = $dropdown->getChildToolbar();

        if ($this->canDo->get('core.edit.state')) {
            $childBar->publish('cwmtopics.publish');
            $childBar->unpublish('cwmtopics.unpublish');
            $childBar->archive('cwmtopics.archive', 'JTOOLBAR_ARCHIVE');
            $childBar->checkin('cwmtopics.checkin')->listCheck(true);

            if ((int) $this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED) {
                $childBar->trash('cwmtopics.trash')->listCheck(true);
            }
        }

        if (
            (int) $this->state->get('filter.published') === ContentComponent::CONDITION_TRASHED
            && $this->canDo->get('core.delete')
        ) {
            $toolbar->delete('cwmtopics.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        ToolbarHelper::help('topics', true);
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
            'topic.topic_text' => Text::_('JBS_CMN_TOPICS'),
            'topic.published'  => Text::_('JSTATUS'),
            'topic.id'         => Text::_('JGRID_HEADING_ID'),
        ];
    }
}
