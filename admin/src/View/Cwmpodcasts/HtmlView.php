<?php

/**
 * Podcasts html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmpodcasts;

// No Direct Access
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for Podcasts
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

    /** @var  array Filter Levels
     * @since    7.0.0
     */
    public $f_levels;

    /** @var  array Side Bar
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
    public function display($tpl = null): void
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state      = $this->get('State');

        $this->canDo         = ContentHelper::getActions('com_proclaim', 'podcast');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

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
        $user  = Factory::getApplication()->getIdentity();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(Text::_('JBS_CMN_PODCASTS'), 'feed feed');

        if ($this->canDo->get('core.create')) {
            $toolbar->addNew('cwmpodcast.add');
        }

        if ($this->canDo->get('core.edit', 'com_proclaim')) {
            /** @var  DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            $childBar->publish('cwmpodcasts.publish')->listCheck(true);
            $childBar->unpublish('cwmpodcasts.unpublish')->listCheck(true);
            $childBar->archive('cwmpodcasts.archive')->listCheck(true);

            if ($this->state->get('filter.published') != -2) {
                $childBar->trash('cwmpodcasts.trash')->listCheck(true);
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

        if ($this->canDo->get('core.create')) {
            ToolbarHelper::divider();
            ToolbarHelper::custom('cwmpodcasts.writeXMLFile', 'file', '', 'JBS_PDC_WRITE_XML_FILES', false);
        }

        ToolbarHelper::help('podcasts', true);
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
            'podcast.title'     => Text::_('JBS_CMN_PODCAST'),
            'podcast.published' => Text::_('JSTATUS'),
            'podcast.language'  => Text::_('JGRID_HEADING_LANGUAGE'),
            'podcast.id'        => Text::_('JGRID_HEADING_ID'),
        ];
    }
}
