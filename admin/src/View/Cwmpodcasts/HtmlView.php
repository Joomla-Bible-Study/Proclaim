<?php

/**
 * Podcasts html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMPodcasts;

// No Direct Access
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
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

        $this->setDocumentTitle(Text::_('JBS_TITLE_PODCASTS'));
    }

    /**
     * Add Toolbar
     *
     * @return void
     *
     * @since 7.0.0
     */
    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('JBS_CMN_PODCASTS'), 'feed feed');

        if ($this->canDo->get('core.create')) {
            ToolbarHelper::addNew('cwmpodcast.add');
        }

        if ($this->canDo->get('core.edit')) {
            ToolbarHelper::editList('cwmpodcast.edit');
        }

        if ($this->canDo->get('core.edit.state')) {
            ToolbarHelper::divider();
            ToolbarHelper::publishList('cwmpodcasts.publish');
            ToolbarHelper::unpublishList('cwmpodcasts.unpublish');
            ToolbarHelper::divider();
            ToolbarHelper::archiveList('cwmpodcasts.archive');
        }

        if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete')) {
            ToolbarHelper::deleteList('', 'cwmpodcasts.delete', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($this->canDo->get('core.delete')) {
            ToolbarHelper::trash('cwmpodcasts.trash');
        }

        if ($this->canDo->get('core.create')) {
            ToolbarHelper::divider();
            ToolbarHelper::custom('cwmpodcasts.writeXMLFile', 'xml.png', '', 'JBS_PDC_WRITE_XML_FILES', false);
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
            'podcast.title'     => Text::_('JBS_CMN_PODCAST'),
            'podcast.published' => Text::_('JSTATUS'),
            'podcast.language'  => Text::_('JGRID_HEADING_LANGUAGE'),
            'podcast.id'        => Text::_('JGRID_HEADING_ID')
        );
    }
}
