<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMLocations;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

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
     * @var object
     * @since    7.0.0
     */
    public $canDo;
    /**
     * Filter Levels
     *
     * @var object
     * @since    7.0.0
     */
    public $f_levels;
    /**
     * Side Bar
     *
     * @var string
     * @since 9.0.0
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
     * @var object
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
     * @throws \Exception
     * @since   11.1
     *
     * @see     fetch()
     */
    public function display($tpl = null): void
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->form          = $this->get('form');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->canDo         = ContentHelper::getActions('com_proclaim');

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        }

        $this->setDocumentTitle(Text::_('JBS_TITLE_LOCATIONS'));

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
        $user = Factory::getApplication()->getSession()->get('user');

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

            if ($this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED) {
                $childBar->trash('cwmlocations.trash')->listCheck(true);
            }

            // Add a batch button
            if ($user->authorise('core.create', 'com_proclaim')
                && $user->authorise('core.edit', 'com_proclaim')
                && $user->authorise('core.edit.state', 'com_proclaim')) {
                $childBar->popupButton('batch')
                    ->text('JTOOLBAR_BATCH')
                    ->selector('collapseModal')
                    ->listCheck(true);
            }
        }

        if ($this->state->get('filter.published') === '-2' && $this->canDo->get('core.delete')) {
            $toolbar->delete('', 'cwmlocations.delete')
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
            'location.location_text' => Text::_('JGRID_HEADING_ORDERING'),
            'location.published'     => Text::_('JSTATUS'),
            'access_level'           => Text::_('JGRID_HEADING_ACCESS'),
            'location.id'            => Text::_('JGRID_HEADING_ID')
        );
    }
}
