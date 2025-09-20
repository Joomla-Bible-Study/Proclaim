<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMTeacher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for Teacher
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Form
     *
     * @var mixed
     * @since    7.0.0
     */
    //public mixed $form;

    /**
     * Item
     *
     * @var object
     * @since    7.0.0
     */
    public $item;

    /**
     * State
     *
     * @var object
     * @since    7.0.0
     */
    public $state;

    /**
     * Admin
     *
     * @var object
     * @since    7.0.0
     */
    public $admin;

    /**
     * Can Do
     *
     * @var object
     * @since    7.0.0
     */
    public $canDo;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void A string if successful, otherwise a JError object.
     *
     * @throws \Exception
     * @since   11.1
     * @see     fetch()
     */
    public function display($tpl = null)
    {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");
        $this->canDo = ContentHelper::getActions('com_proclaim', 'teacher', (int)$this->item->id);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        // Load the Admin settings
        $this->admin = Cwmparams::getAdmin();

        $this->setLayout("edit");

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        }

        $isNew = ($this->item->id < 1);

        // Display the template
        parent::display($tpl);
    }

    /**
     * Add Toolbar
     *
     * @return void
     *
     * @since 7.0.0
     */
    protected function addToolbar()
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
        $help_url = 'https://www.christianwebministries.org/index.php?option=com_content&view=article&id=34:teacher-entry-help&catid=20&Itemid=315&tmpl=component';
        ToolbarHelper::help('proclaim', false, $url = $help_url, 'com_proclaim');
    }
}
