<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmmessage;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\BibleStructure;
use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use CWM\Component\Proclaim\Administrator\Model\CwmmessageModel;
use CWM\Library\Scripture\Helper\ScriptureParamsHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * View class for Message
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Form
     *
     * @var ?\Joomla\CMS\Form\Form
     * @since    7.0.0
     */
    protected ?\Joomla\CMS\Form\Form $form = null;

    /**
     * Item
     *
     * @var ?object
     * @since    7.0.0
     */
    protected ?object $item = null;

    /**
     * Admin
     *
     * @var ?object
     * @since    7.0.0
     */
    protected ?object $admin = null;

    /**
     * Can Do
     *
     * @var ?object
     * @since    7.0.0
     */
    protected ?object $canDo = null;

    /**
     * Media Files
     *
     * @var array
     * @since    7.0.0
     */
    protected array $mediafiles = [];

    /**
     * Admin Params
     *
     * @var ?Registry
     * @since    7.0.0
     */
    protected ?Registry $admin_params = null;

    /**
     * Simple mode object
     *
     * @var   ?object
     * @since 9.2.3
     */
    protected ?object $simple = null;

    /**
     * State
     *
     * @var ?object
     * @since    7.0.0
     */
    protected ?object $state = null;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   11.1
     * @see     fetch()
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmmessageModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->form       = $model->getForm();
        $this->item       = $model->getItem();
        $this->canDo      = ContentHelper::getActions('com_proclaim', 'message', (int)$this->item->id);
        $input            = Factory::getApplication()->getInput();
        $option           = $input->get('option', '', 'cmd');
        $this->mediafiles = $model->getMediaFiles();
        $this->state      = $model->getState();

        // For modalreturn layout, just load item data and render (no toolbar, no extras)
        if ($this->getLayout() === 'modalreturn') {
            parent::display($tpl);

            return;
        }

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Set some variables for use by the modal mediafile entry form from a study
        $app = Factory::getApplication();
        $app->setUserState($option . 'sid', $this->item->id);
        $app->setUserState($option . 'sdate', $this->item->studydate);
        $this->admin = Cwmparams::getAdmin();
        $registry    = new Registry();
        $registry->loadString($this->admin->params);
        $this->admin_params = $registry;
        $this->document     = Factory::getApplication()->getDocument();

        $this->simple = Cwmhelper::getSimpleView();

        // Load scripture autocomplete assets
        $document = Factory::getApplication()->getDocument();
        $wa       = $document->getWebAssetManager();
        $wa->useScript('lib_cwmscripture.scripture-autocomplete');
        $document->addScriptOptions('cwmscripture.books', CwmscriptureHelper::getAllBooks());
        $document->addScriptOptions('cwmscripture.bibleStructure', BibleStructure::getStructureForJs());
        $document->addScriptOptions(
            'cwmscripture.defaultBibleVersion',
            ScriptureParamsHelper::getParams()->get('default_version', 'kjv')
        );

        // Push translatable strings to JS
        Text::script('JBS_STY_SEARCH_VERSIONS');

        $forcedLanguage = $input->get('forcedLanguage', '', 'cmd');

        // If we are forcing a language in modal (used for associations).
        if ($this->getLayout() === 'modal' && $forcedLanguage) {
            $this->form->setValue('language', null, $forcedLanguage);
            $this->form->setFieldAttribute('language', 'readonly', 'true');
        }

        // Set the toolbar
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        } else {
            $this->addModalToolbar();
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
        $input  = Factory::getApplication()->getInput();
        $layout = $input->get('layout', 'edit');
        $input->set('hidemainmenu', true);

        // Wizard layout gets a simplified toolbar
        if ($layout === 'wizard') {
            ToolbarHelper::title(
                Text::_('JBS_CMN_STUDIES') . ': <small><small>[ ' . Text::_('JBS_WIZ_TITLE') . ' ]</small></small>',
                'book book'
            );
            ToolbarHelper::cancel('cwmmessage.cancel');

            return;
        }

        $isNew = ($this->item->id === 0);
        $title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
        ToolbarHelper::title(
            Text::_('JBS_CMN_STUDIES') . ': <small><small>[ ' . $title . ' ]</small></small>',
            'book book'
        );

        $toolbar = $this->getDocument()->getToolbar();

        if ($isNew && $this->canDo->get('core.create', 'com_proclaim')) {
            $toolbar->apply('cwmmessage.apply');

            $toolbar->dropdownButton('save-group')->configure(
                static function (Toolbar $childBar) {
                    $childBar->save('cwmmessage.save');
                    $childBar->save2new('cwmmessage.save2new');
                }
            );

            $toolbar->cancel('cwmmessage.cancel');
        } else {
            $canDo = $this->canDo;

            if ($canDo->get('core.edit', 'com_proclaim')) {
                $toolbar->apply('cwmmessage.apply');
            }

            $toolbar->dropdownButton('save-group')->configure(
                static function (Toolbar $childBar) use ($canDo) {
                    if ($canDo->get('core.edit', 'com_proclaim')) {
                        $childBar->save('cwmmessage.save');

                        // Saving is allowed, but returning to a blank form is
                        // only useful with the create permission.
                        if ($canDo->get('core.create', 'com_proclaim')) {
                            $childBar->save2new('cwmmessage.save2new');
                        }
                    }

                    // A copy is a new record, so this stands on create alone.
                    if ($canDo->get('core.create', 'com_proclaim')) {
                        $childBar->save2copy('cwmmessage.save2copy');
                    }
                }
            );

            $toolbar->cancel('cwmmessage.cancel', 'JTOOLBAR_CLOSE');

            // Resetting the counter is destructive and unrelated to saving, so
            // it stays a button of its own rather than joining the save group.
            if ($canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::divider();
                $toolbar->confirmButton('undo', 'JBS_STY_RESET_HITS', 'resetHits')
                    ->message('JBS_STY_RESET_HITS_CONFIRM')
                    ->listCheck(false);
            }
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('message', true);
    }

    /**
     * Add toolbar for modal layout (Apply/Save/Cancel only).
     *
     * @return void
     *
     * @since  10.2.0
     */
    protected function addModalToolbar(): void
    {
        $isNew   = ($this->item->id === 0);
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(
            Text::_('JBS_CMN_STUDIES') . ': <small><small>[' . ($isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT')) . ']</small></small>',
            'book book'
        );

        $canCreate = $isNew && $this->canDo->get('core.create', 'com_proclaim');
        $canEdit   = $this->canDo->get('core.edit', 'com_proclaim');

        if ($canCreate || $canEdit) {
            $toolbar->apply('cwmmessage.apply');
            $toolbar->save('cwmmessage.save');
        }

        $toolbar->cancel('cwmmessage.cancel');
    }
}
