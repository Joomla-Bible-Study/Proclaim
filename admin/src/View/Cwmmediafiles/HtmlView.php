<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmmediafiles;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Model\CwmmediafilesModel;
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
     * Can Do
     *
     * @var ?object
     * @since    7.0.0
     */
    public ?object $canDo = null;

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
     * @throws  \Exception
     * @since   11.1
     * @see     fetch()
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmmediafilesModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->canDo         = ContentHelper::getActions('com_proclaim', 'message');
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        if (ComponentHelper::getParams('com_proclaim')->get('workflow_enabled')) {
            PluginHelper::importPlugin('workflow');

            $this->transitions = $model->getTransitions();
        }

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
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
     * @throws \Exception
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
                $childBar->checkin('cwmmediafiles.checkin')->listCheck(true);

                if ((int) $this->state->get('filter.published') !== ContentComponent::CONDITION_TRASHED) {
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
            && (int) $this->state->get('filter.published') === ContentComponent::CONDITION_TRASHED
            && $canDo->get('core.delete')
        ) {
            $toolbar->delete('cwmmediafiles.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);

            // Delete confirmation dialog for physical files
            $wa = $this->getDocument()->getWebAssetManager();
            $wa->useScript('com_proclaim.delete-confirm');

            Text::script('JBS_DEL_PHYSICAL_FILES_TITLE');
            Text::script('JBS_DEL_PHYSICAL_FILES_WARNING');
            Text::script('JBS_DEL_PHYSICAL_FILES_COUNT');
            Text::script('JBS_DEL_DELETE_EVERYTHING');
            Text::script('JBS_DEL_RECORDS_ONLY');
            Text::script('JCANCEL');
        }
        $toolbar->help('mediafiles', true);
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
            'study.studytitle'     => Text::_('JBS_CMN_STUDY_TITLE'),
            'mediatype.media_text' => Text::_('JBS_MED_MEDIA_TYPE'),
            'mediafile.ordering'   => Text::_('JGRID_HEADING_ORDERING'),
            'mediafile.published'  => Text::_('JSTATUS'),
            'mediafile.id'         => Text::_('JGRID_HEADING_ID'),
        ];
    }
}
