<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMServers;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for Servers
 *
 * @package  Proclaim.Admin
 * @since    7.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Items
     *
     * @var object
     * @since    7.0.0
     */
    protected $items;

    /**
     * @var object
     * @since 7.0.0
     */
    protected $types;

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
     * Can Do
     *
     * @var object
     * @since    7.0.0
     */
    protected $canDo;

    /** @var  array Filter Levels
     * @since    7.0.0
     */
    protected $f_levels;

    /** @var  object Side Bar
     * @since    7.0.0
     */
    protected $sidebar;

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
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->canDo         = ContentHelper::getActions('com_proclaim', 'server');
        $this->types         = $this->get('ServerOptions');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

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
     * @since 7.0
     */
    protected function addToolbar(): void
    {
        $canDo = ContentHelper::getActions('com_proclaim');
        ToolbarHelper::title(Text::_('JBS_CMN_SERVERS'), 'database database');

        if ($canDo->get('core.create')) {
            ToolbarHelper::addNew('cwmserver.add');
        }

        if ($canDo->get('core.edit')) {
            ToolbarHelper::editList('cwmserver.edit');
        }

        if ($canDo->get('core.edit.state')) {
            ToolbarHelper::divider();
            ToolbarHelper::publishList('cwmservers.publish');
            ToolbarHelper::unpublishList('cwmservers.unpublish');
            ToolbarHelper::divider();
            ToolbarHelper::archiveList('cwmservers.archive');
        }

        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            ToolbarHelper::deleteList('', 'cwmservers.delete', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($canDo->get('core.delete')) {
            ToolbarHelper::trash('cwmservers.trash');
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
            'servers.server_name' => Text::_('JGRID_HEADING_ORDERING'),
            'servers.published'   => Text::_('JSTATUS'),
            'servers.id'          => Text::_('JGRID_HEADING_ID')
        );
    }
}
