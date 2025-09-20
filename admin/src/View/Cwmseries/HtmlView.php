<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMSeries;

// No Direct Access
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
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
     * Can Do
     *
     * @var object
     * @since    7.0.0
     */
    public $canDo;

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
     * Items
     *
     * @var object
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
     * @var mixed
     * @since    7.0.0
     */
    protected $state;

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
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        $this->filterForm = $this->get('FilterForm');
        $this->canDo      = ContentHelper::getActions('com_proclaim', 'serie');

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
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
        $canDo = ContentHelper::getActions('com_proclaim');
        $user  = Factory::getApplication()->getIdentity();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('JBS_CMN_SERIES'), 'tree-2 tree-2');
        $help_url = 'https://www.christianwebministries.org/index.php?option=com_content&view=article&id=28:admin-messages-list-help-screen&catid=20&Itemid=315&tmpl=component';
        ToolbarHelper::help('proclaim', false, $url = $help_url, 'com_proclaim');

        if ($this->canDo->get('core.create')) {
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
            'series.ordering'  => Text::_('JGRID_HEADING_ORDERING'),
            'series.published' => Text::_('JSTATUS'),
            'access_level'     => Text::_('JGRID_HEADING_ACCESS'),
            'series.language'  => Text::_('JGRID_HEADING_LANGUAGE'),
            'series.id'        => Text::_('JGRID_HEADING_ID')
        );
    }
}
