<?php

/**
 * TemplateCode html
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2007 - 2012 CWM Team All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           https://www.christianwebministries.org
 * @since          7.1.0
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMTemplateCodes;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Extension\ProclaimComponent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for TemplateCodes
 *
 * @package  Proclaim.Admin
 * @since    7.1.0
 */
class HtmlView extends BaseHtmlView
{
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
     * Is this view an Empty State
     *
     * @var   boolean
     * @since 4.0.0
     */
    private $isEmptyState = false;

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

        $this->filterForm = $this->get('FilterForm');

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        foreach ($this->items as $item) {
            switch ($item->type) {
                case 1:
                    $item->typetext = Text::_('JBS_TPLCODE_SERMONLIST');
                    break;
                case 2:
                    $item->typetext = Text::_('JBS_TPLCODE_SERMON');
                    break;
                case 3:
                    $item->typetext = Text::_('JBS_TPLCODE_TEACHERS');
                    break;
                case 4:
                    $item->typetext = Text::_('JBS_TPLCODE_TEACHER');
                    break;
                case 5:
                    $item->typetext = Text::_('JBS_TPLCODE_SERIESDISPLAYS');
                    break;
                case 6:
                    $item->typetext = Text::_('JBS_TPLCODE_SERIESDISPLAY');
                    break;
                case 7:
                    $item->typetext = Text::_('JBS_TPLCODE_MODULE');
                    break;
            }
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        }

        $this->setDocumentTitle(Text::_('JBS_TITLE_TEMPLATECODES'));

        // Display the template
        parent::display($tpl);
    }

    /**
     * Add Toolbar
     *
     * @return void
     *
     * @since 7.1.0
     */
    protected function addToolbar(): void
    {
        $canDo = ContentHelper::getActions('com_proclaim');
        $user  = $this->getCurrentUser();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('JBS_TPLCODE_TPLCODES'), 'stack stack');

        if ($canDo->get('core.create')) {
            $toolbar->addNew('cwmtemplatecode.add');
        }

        if (!$this->isEmptyState && $canDo->get('core.edit.state')) {
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if ($canDo->get('core.edit.state')) {
                $childBar->publish('cwmtemplatecodes.publish');
                $childBar->unpublish('cwmtemplatecodes.unpublish');
                $childBar->archive('cwmtemplatecodes.archive');

                if ($this->state->get('filter.published') !== ProclaimComponent::CONDITION_TRASHED) {
                    $childBar->trash('cwmtemplatecodes.trash')->listCheck(true);
                }
            }
        }

        if (
            !$this->isEmptyState && $this->state->get(
                'filter.published'
            ) == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete')
        ) {
            $toolbar->delete('cwmtemplatecodes.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if ($user->authorise('core.admin', 'com_proclaim') || $user->authorise('core.options', 'com_proclaim')) {
            $toolbar->preferences('com_proclaim');
        }

        $toolbar->help('Messages', true);
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
            'mediafile.filename'   => Text::_('JBS_MED_FILENAME'),
            'mediafile.ordering'   => Text::_('JGRID_HEADING_ORDERING'),
            'mediafile.published'  => Text::_('JSTATUS'),
            'mediafile.id'         => Text::_('JGRID_HEADING_ID')
        );
    }
}
