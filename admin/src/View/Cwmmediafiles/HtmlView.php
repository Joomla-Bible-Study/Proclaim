<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMMediaFiles;

// No Direct Access
use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for MediaFiles
 *
 * @package  Proclaim.Admin
 * @since    7.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Media Types
     *
     * @var string
     * @since    7.0.0
     */
    public $mediatypes;

    /**
     * Can Do
     *
     * @var object
     * @since    7.0.0
     */
    public $canDo;

    /**
     * Filter Levers
     *
     * @var string
     * @since    7.0.0
     */
    public $f_levels;

    /**
     * Side Bare
     *
     * @var string
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
     * All transitions, which can be executed of one if the items
     *
     * @var  array
     * @since 4.0.0
     */
    protected array $transitions = [];

    /**
     * Is this view an Empty State
     *
     * @var   bool
     * @since 4.0.0
     */
    private bool $isEmptyState = false;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void  A string if successful, otherwise a JError object.
     *
     * @throws  Exception
     * @since   11.1
     * @see     fetch()
     */
    public function display($tpl = null): void
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->canDo         = ContentHelper::getActions('com_proclaim', 'message');
        $this->mediatypes    = $this->get('Mediatypes');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        if (ComponentHelper::getParams('com_proclaim')->get('workflow_enabled')) {
            PluginHelper::importPlugin('workflow');

            $this->transitions = $this->get('Transitions');
        }

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();

            // We do not need to filter by language when multilingual is disabled
            if (!Multilanguage::isEnabled()) {
                unset($this->activeFilters['language']);
                $this->filterForm->removeField('language', 'filter');
            }
        } elseif ($forcedLanguage = Factory::getApplication()->getInput()->get('forcedLanguage', '', 'CMD')) {
            // If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
            $languageXml = new \SimpleXMLElement(
                '<field name="language" type="hidden" default="' . $forcedLanguage . '" />'
            );
            $this->filterForm->setField($languageXml, 'filter', true);

            // Also, unset the active language filter so the search tools is not open by default with this filter.
            unset($this->activeFilters['language']);
        }

        // Display the template
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar
     *
     * @return void
     *
     * @throws Exception
     * @since 7.0
     */
    protected function addToolbar(): void
    {
        $canDo   = ContentHelper::getActions('com_proclaim');
        $user    = Factory::getApplication()->getIdentity();
        /** @var Toolbar $toolbar **/
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('JBS_CMN_MEDIA_FILES'), 'video video');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('cwmmediafile.add');
        }

        if (!$this->isEmptyState && ($canDo->get('core.edit.state'))) {
            /** @var  DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if ($canDo->get('core.edit.state')) {
                $childBar->publish('cwmmediafiles.publish');
                $childBar->unpublish('cwmmediafiles.unpublish');
                $childBar->archive('cwmmediafiles.archive');

                if ($this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED) {
                    $childBar->trash('cwmmediafiles.trash')->listCheck(true);
                }
            }

            // Add a batch button
            if (
                $user->authorise('core.create', 'com_proclaim')
                && $user->authorise('core.edit', 'com_proclaim')
                && $user->authorise('core.edit.transition', 'com_proclaim')
            ) {
                $childBar->popupButton('batch')
                    ->text('JTOOLBAR_BATCH')
                    ->selector('collapseModal')
                    ->listCheck(true);
            }
        }

        if (
            !$this->isEmptyState
            && $this->state->get('filter.published') === ContentComponent::CONDITION_TRASHED && $canDo->get(
                'core.delete'
            )
        ) {
            $toolbar->delete('cwmmediafiles.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }
        $help_url = 'https://www.christianwebministries.org/index.php?option=com_content&view=article&id=28:admin-messages-list-help-screen&catid=20&Itemid=315&tmpl=component';
        $toolbar->help('proclaim', false, $url = $help_url, 'com_proclaim');
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
            'study.studytitle'     => Text::_('JBS_CMN_STUDY_TITLE'),
            'mediatype.media_text' => Text::_('JBS_MED_MEDIA_TYPE'),
            'mediafile.ordering'   => Text::_('JGRID_HEADING_ORDERING'),
            'mediafile.published'  => Text::_('JSTATUS'),
            'mediafile.id'         => Text::_('JGRID_HEADING_ID')
        );
    }
}
