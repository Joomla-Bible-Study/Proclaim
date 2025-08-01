<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMServer;

// No Direct Access
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
     * @var object
     * @since    7.0.0
     */
    protected $form;

    /**
     * Server form
     *
     * @var string
     * @since    7.0.0
     */
    protected $server_form;

    /**
     * Item
     *
     * @var object
     * @since    7.0.0
     */
    protected $item;

    /**
     * State
     *
     * @var object
     * @since    7.0.0
     */
    protected $state;

    /**
     * Admin
     *
     * @var object
     * @since    7.0.0
     */
    protected $admin;

    /**
     * Can Do
     *
     * @var object
     * @since    7.0.0
     */
    protected $canDo;

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
        $this->form        = $this->get("form");
        $this->state       = $this->get("State");
        $this->item        = $this->get("Item");
        $this->canDo       = ContentHelper::getActions('com_proclaim', 'server', (int)$this->item->id);
        $this->server_form = $this->get('AddonServerForm');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->setLayout("edit");

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
     * @since 7.0.0
     */
    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);
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
	    $help_url = 'https://www.christianwebministries.org/index.php?option=com_content&view=article&id=40:server-entry-screen-help&catid=20&Itemid=315&tmpl=component';
	    ToolbarHelper::help('proclaim', false, $url = $help_url, 'com_proclaim');
    }
}
