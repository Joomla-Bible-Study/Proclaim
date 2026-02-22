<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmteacher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Model\CwmteacherModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * View class for Teacher
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Item
     *
     * @var ?object
     * @since    7.0.0
     */
    public ?object $item = null;

    /**
     * State
     *
     * @var ?object
     * @since    7.0.0
     */
    public ?object $state = null;

    /**
     * Can Do
     *
     * @var ?object
     * @since    7.0.0
     */
    public ?object $canDo = null;

    /**
     * Form
     *
     * @var ?\Joomla\CMS\Form\Form
     * @since    7.0.0
     */
    public ?\Joomla\CMS\Form\Form $form = null;

    /**
     * Admin params
     *
     * @var Registry
     * @since 10.1.0
     */
    protected Registry $admin_params;

    /**
     * Messages belonging to this teacher
     *
     * @var array
     * @since 10.1.0
     */
    protected array $messages = [];

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
        /** @var CwmteacherModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->state = $model->getState();
        $this->canDo = ContentHelper::getActions('com_proclaim', 'teacher', (int)$this->item->id);

        // Load the Admin settings as Registry
        $admin    = Cwmparams::getAdmin();
        $registry = new Registry();
        $registry->loadString($admin->params);
        $this->admin_params = $registry;

        // Load messages belonging to this teacher (only for existing records)
        if (!empty($this->item->id) && $this->item->id > 0) {
            $this->messages = $model->getMessages();
        }

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->setLayout("edit");

        // We don't need toolbar in the modal window.
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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
        $title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
        ToolbarHelper::title(
            Text::_('JBS_CMN_TEACHER') . ': <small><small>[' . $title . ']</small></small>',
            'user user'
        );

        if ($isNew && $this->canDo->get('core.create', 'com_proclaim')) {
            ToolbarHelper::apply('cwmteacher.apply');
            ToolbarHelper::save('cwmteacher.save');
            ToolbarHelper::cancel('cwmteacher.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::apply('cwmteacher.apply');
                ToolbarHelper::save('cwmteacher.save');
            }

            ToolbarHelper::cancel('cwmteacher.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('teacher', true);
    }
}
