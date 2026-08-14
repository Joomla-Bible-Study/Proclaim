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
use CWM\Component\Proclaim\Administrator\Helper\CwmmediaProtectionHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmprotectedStorage;
use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use CWM\Component\Proclaim\Administrator\Model\CwmmediafilesModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Database\DatabaseInterface;

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
        $this->canDo         = Cwmassets::sectionActions('mediafile');
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        $this->warnIfProtectedStorageIsNotWorking();

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
        $canDo   = Cwmassets::sectionActions('mediafile');
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
                $user->authorise('core.create', 'com_proclaim.mediafile')
                && $user->authorise('core.edit', 'com_proclaim.mediafile')
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
            $this->getDocument()->addScriptOptions('com_proclaim.deleteConfirm', [
                'csrfToken' => Session::getFormToken(),
            ]);

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

    /**
     * Say so, once, when this site restricts media but the protected folder is
     * not actually being enforced here.
     *
     * ⚠️ Deliberately silent unless the site has restricted media. A site whose
     * library is entirely public has nothing to protect, and telling it that a
     * folder it does not use is unenforced is noise — the kind that teaches
     * administrators to dismiss warnings without reading them.
     *
     * The verdict itself is cached and only re-taken weekly, so this costs a
     * param read on an ordinary page load (#1783).
     *
     * @return  void
     *
     * @since   10.5.8
     */
    private function warnIfProtectedStorageIsNotWorking(): void
    {
        $status = CwmprotectedStorage::status();

        if ($status === CwmmediaProtectionHelper::PROTECTED) {
            return;
        }

        $guest  = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
        $levels = $guest->getAuthorisedViewLevels() ?: [0];
        $levels = array_map('intval', $levels);
        $list   = implode(',', $levels);

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $found = $db->setQuery(
            $db->createQuery()
                ->select('1')
                ->from($db->quoteName('#__bsms_mediafiles', 'm'))
                ->leftJoin(
                    $db->quoteName('#__bsms_studies', 'study')
                    . ' ON (' . $db->quoteName('study.id') . ' = ' . $db->quoteName('m.study_id') . ')'
                )
                ->leftJoin(
                    $db->quoteName('#__bsms_series', 'series')
                    . ' ON (' . $db->quoteName('series.id') . ' = ' . $db->quoteName('study.series_id') . ')'
                )
                ->where(
                    '(' . $db->quoteName('m.access') . ' NOT IN (' . $list . ') OR '
                    . $db->quoteName('study.access') . ' NOT IN (' . $list . ') OR '
                    . $db->quoteName('series.access') . ' NOT IN (' . $list . '))'
                ),
            0,
            1
        )->loadResult();

        if ($found === null) {
            return;
        }

        Factory::getApplication()->enqueueMessage(
            Text::_(
                $status === CwmmediaProtectionHelper::EXPOSED
                    ? 'JBS_MED_PROTECTED_FOLDER_EXPOSED'
                    : 'JBS_MED_PROTECTED_FOLDER_UNVERIFIED'
            ),
            'warning'
        );
    }
}
