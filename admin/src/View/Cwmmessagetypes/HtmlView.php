<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMMessageTypes;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

/**
 * View class for Messagetype
 *
 * @package  Proclaim.Admin
 * @since    7.0
 */
class HtmlView extends BaseHtmlView
{
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
     * @var object
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

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        }

        $this->setDocumentTitle(Text::_('JBS_TITLE_MESSAGETYPES'));

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

        ToolbarHelper::title(Text::_('JBS_CMN_MESSAGETYPES'), 'list-2 list-2');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('cwmmessagetype.add');
        }

        $dropdown = $toolbar->dropdownButton('status-group')
            ->text('JTOOLBAR_CHANGE_STATUS')
            ->toggleSplit(false)
            ->icon('icon-ellipsis-h')
            ->buttonClass('btn btn-action')
            ->listCheck(true);
        $childBar = $dropdown->getChildToolbar();

        if ($canDo->get('core.edit.state')) {
            $childBar->publish('cwmmessagetypes.publish');
            $childBar->unpublish('cwmmessagetypes.unpublish');
            $childBar->archive('cwmmessagetypes.archive');

            if ($this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED) {
                $childBar->trash('cwmmessagetypes.trash')->listCheck(true);
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

        if ($this->state->get('filter.published') === '-2' && $canDo->get('core.delete')) {
            $toolbar->delete('', 'cwmmessagetypes.delete')
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
            'messagetypes.message_type' => Text::_('JGRID_HEADING_ORDERING'),
            'messagetypes.published'    => Text::_('JSTATUS'),
            'messagetypes.id'           => Text::_('JGRID_HEADING_ID')
        );
    }
}
