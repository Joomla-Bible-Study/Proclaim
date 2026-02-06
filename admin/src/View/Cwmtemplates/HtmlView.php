<?php

/**
 * Templates html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmtemplates;

// No Direct Access
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for Templates
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * State
     *
     * @var object
     * @since    7.0.0
     */
    public $canDo;

    /**
     * State
     *
     * @var object
     * @since    7.0.0
     */
    public $templates;

    /**
     * State
     *
     * @var array
     * @since    7.0.0
     */
    public $f_levels;

    /**
     * State
     *
     * @var object
     * @since    7.0.0
     */
    public $sidebar;

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
     * @var array
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
     * @see     fetch()
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        $this->filterForm = $this->get('FilterForm');
        $this->canDo      = ContentHelper::getActions('com_proclaim', 'template');

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // We don't need a toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        }

        // Display the template
        parent::display($tpl);
    }

    /**
     * Add Toolbar
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    protected function addToolbar(): void
    {
        $user = Factory::getApplication()->getIdentity();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('JBS_CMN_TEMPLATES'), 'grid grid');

        if ($this->canDo->get('core.create')) {
            ToolbarHelper::addNew('cwmtemplate.add');
        }

        $dropdown = $toolbar->dropdownButton('status-group')
            ->text('JTOOLBAR_CHANGE_STATUS')
            ->toggleSplit(false)
            ->icon('icon-ellipsis-h')
            ->buttonClass('btn btn-action')
            ->listCheck(true);
        $childBar = $dropdown->getChildToolbar();

        if ($this->canDo->get('core.edit.state')) {
            $childBar->publish('cwmtemplates.publish');
            $childBar->unpublish('cwmtemplates.unpublish');
            $childBar->trash('cwmtemplates.trash');

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

        if ($this->state->get('filter.published') === "-2" && $this->canDo->get('core.delete')) {
            $toolbar->delete('cwmtemplates.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        $url = Route::_('index.php?option=com_proclaim&view=cwmtemplates&layout=default_export');
        $toolbar->appendButton('Link', 'export', 'JBS_TPL_IMPORT_EXPORT_TEMPLATE', $url);

        ToolbarHelper::help('templates', true);
    }
}
