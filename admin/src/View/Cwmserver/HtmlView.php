<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmserver;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Model\CwmserverModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for Server
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
     * Server form
     *
     * @var mixed
     * @since    7.0.0
     */
    protected mixed $server_form = null;

    /**
     * Item
     *
     * @var ?object
     * @since    7.0.0
     */
    protected ?object $item = null;

    /**
     * State
     *
     * @var ?object
     * @since    7.0.0
     */
    protected ?object $state = null;

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
        /** @var CwmserverModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->form        = $model->getForm();
        $this->state       = $model->getState();
        $this->item        = $model->getItem();
        $this->canDo       = ContentHelper::getActions('com_proclaim', 'server', (int)$this->item->id);
        $this->server_form = $model->getAddonServerForm();

        // For modalreturn layout, just load item data and render (no toolbar, no extras)
        if ($this->getLayout() === 'modalreturn') {
            parent::display($tpl);

            return;
        }

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $input          = Factory::getApplication()->getInput();
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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);
        $isNew = ($this->item->id < 1);
        $canDo = ContentHelper::getActions('com_proclaim');
        $title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
        ToolbarHelper::title(
            Text::_('JBS_CMN_SERVERS') . ': <small><small>[' . $title . ']</small></small>',
            'database database'
        );

        if ($isNew && $canDo->get('core.create', 'com_proclaim')) {
            ToolbarHelper::apply('cwmserver.apply');
            ToolbarHelper::save('cwmserver.save');
            ToolbarHelper::save2new('cwmserver.save2new');
            ToolbarHelper::cancel('cwmserver.cancel');
        } else {
            if ($canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::apply('cwmserver.apply');
                ToolbarHelper::save('cwmserver.save');

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canDo->get('core.create', 'com_proclaim')) {
                    ToolbarHelper::save2new('cwmserver.save2new');
                }
            }

            // If checked out, we can still save
            if ($canDo->get('core.create', 'com_proclaim')) {
                ToolbarHelper::save2copy('cwmserver.save2copy');
            }

            ToolbarHelper::cancel('cwmserver.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('server', true);
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
        $isNew   = ($this->item->id < 1);
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(
            Text::_('JBS_CMN_SERVERS') . ': <small><small>[' . ($isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT')) . ']</small></small>',
            'database database'
        );

        $canCreate = $isNew && $this->canDo->get('core.create', 'com_proclaim');
        $canEdit   = $this->canDo->get('core.edit', 'com_proclaim');

        if ($canCreate || $canEdit) {
            $toolbar->apply('cwmserver.apply');
            $toolbar->save('cwmserver.save');
        }

        $toolbar->cancel('cwmserver.cancel');
    }
}
