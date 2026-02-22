<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmserie;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Model\CwmserieModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * HtmlView class for Serie
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
    public object $canDo;

    /**
     * Form
     *
     * @var object
     * @since    7.0.0
     */
    protected object $form;

    /**
     * Item
     *
     * @var object
     * @since    7.0.0
     */
    protected object $item;

    /**
     * Admin
     *
     * @var Registry
     * @since    7.0.0
     */
    protected Registry $admin_params;

    /**
     * Messages belonging to this series
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
     * @throws  \Exception
     * @since   7.0.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmserieModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->canDo = ContentHelper::getActions('com_proclaim', 'serie', (int)$this->item->id);
        $admin       = Cwmparams::getAdmin();
        $registry    = new Registry();
        $registry->loadString($admin->params);
        $this->admin_params = $registry;

        // Load messages belonging to this series (only for existing records)
        if (!empty($this->item->id) && $this->item->id > 0) {
            $this->messages = $model->getMessages();
        }

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Set the toolbar
        $this->addToolbar();

        $isNew = ($this->item->id < 1);

        // Display the template
        parent::display($tpl);
    }

    /**
     * Add Toolbar
     *
     * @return void
     *
     * @throws \Exception
     * @since  7.0.0
     */
    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);
        $isNew = ($this->item->id === 0);
        $title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
        ToolbarHelper::title(
            Text::_('JBS_CMN_SERIES') . ': <small><small>[' . $title . ']</small></small>',
            'tree tree'
        );

        if ($isNew && $this->canDo->get('core.create', 'com_proclaim')) {
            ToolbarHelper::apply('cwmserie.apply');
            ToolbarHelper::save('cwmserie.save');
            ToolbarHelper::cancel('cwmserie.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::apply('cwmserie.apply');
                ToolbarHelper::save('cwmserie.save');
            }

            ToolbarHelper::cancel('cwmserie.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('serie', true);
    }
}
