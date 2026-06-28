<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmplaylists;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmplaylistsModel;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;

/**
 * View class for Playlists
 *
 * @package  Proclaim.Admin
 * @since    10.3.3
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Can Do
     *
     * @var ?object
     * @since 10.3.3
     */
    public ?object $canDo = null;

    /**
     * Filter Form
     *
     * @var ?\Joomla\CMS\Form\Form
     * @since 10.3.3
     */
    public ?\Joomla\CMS\Form\Form $filterForm = null;

    /**
     * Active Filters
     *
     * @var ?array
     * @since 10.3.3
     */
    public ?array $activeFilters = null;

    /**
     * Items
     *
     * @var ?array
     * @since 10.3.3
     */
    protected ?array $items = null;

    /**
     * Pagination
     *
     * @var ?object
     * @since 10.3.3
     */
    protected ?object $pagination = null;

    /**
     * State
     *
     * @var ?object
     * @since 10.3.3
     */
    protected ?object $state = null;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmplaylistsModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();
        $this->canDo         = ContentHelper::getActions('com_proclaim');

        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        }

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    protected function addToolbar(): void
    {
        $toolbar = $this->getDocument()->getToolbar('toolbar');

        ToolbarHelper::title(Text::_('JBS_CMN_PLAYLISTS'), 'list list');

        if ($this->canDo->get('core.create')) {
            $toolbar->addNew('cwmplaylist.add');

            $toolbar->standardButton('import', Text::_('JBS_PLAYLIST_IMPORT'), 'cwmplaylists.import')
                ->icon('icon-upload')
                ->listCheck(false);
        }

        $dropdown = $toolbar->dropdownButton('status-group')
            ->text('JTOOLBAR_CHANGE_STATUS')
            ->toggleSplit(false)
            ->icon('icon-ellipsis-h')
            ->buttonClass('btn btn-action')
            ->listCheck(true);
        $childBar = $dropdown->getChildToolbar();

        if ($this->canDo->get('core.edit.state')) {
            $childBar->publish('cwmplaylists.publish');
            $childBar->unpublish('cwmplaylists.unpublish');
            $childBar->archive('cwmplaylists.archive');
            $childBar->checkin('cwmplaylists.checkin')->listCheck(true);

            if ((int) $this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED) {
                $childBar->trash('cwmplaylists.trash')->listCheck(true);
            }
        }

        if ($this->state->get('filter.published') === '-2' && $this->canDo->get('core.delete')) {
            $toolbar->delete('', 'cwmplaylists.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        ToolbarHelper::help('playlists', true);
    }

    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     *
     * @since   10.3.3
     */
    protected function getSortFields(): array
    {
        return [
            'playlist.ordering'  => Text::_('JGRID_HEADING_ORDERING'),
            'playlist.published' => Text::_('JSTATUS'),
            'playlist.title'     => Text::_('JGLOBAL_TITLE'),
            'access_level'       => Text::_('JGRID_HEADING_ACCESS'),
            'playlist.last_sync' => Text::_('JBS_PLAYLIST_LAST_SYNC'),
            'playlist.id'        => Text::_('JGRID_HEADING_ID'),
        ];
    }
}
