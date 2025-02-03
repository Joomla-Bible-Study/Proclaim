<?php

/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMLocation;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for Location
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
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
     * Defaults
     *
     * @var array
     * @since    7.0.0
     */
    protected $defaults;

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
     * @return  void  A string if successful, otherwise a Error object.
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
        $this->canDo = ContentHelper::getActions('com_proclaim', 'location', (int)$this->item->id);

        $this->setLayout("edit");

        // Set the toolbar
        $this->addToolbar();

        // Display the template
        parent::display($tpl);

        $isNew = ($this->item->id < 1);
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
        $isNew = ((int)$this->item->id === 0);
        $title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
        ToolbarHelper::title(
            Text::_('JBS_CMN_LOCATIONS') . ': <small><small>[' . $title . ']</small></small>',
            'home home'
        );

        if ($isNew && $this->canDo->get('core.create', 'com_proclaim')) {
            ToolbarHelper::apply('cwmlocation.apply');
            ToolbarHelper::save('cwmlocation.save');
            ToolbarHelper::save2new('cwmlocation.save2new');
            ToolbarHelper::cancel('cwmlocation.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::apply('cwmlocation.apply');
                ToolbarHelper::save('cwmlocation.save');

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($this->canDo->get('core.create', 'com_proclaim')) {
                    ToolbarHelper::save2new('cwmlocation.save2new');
                }
            }

            // If checked out, we can still save
            if ($this->canDo->get('core.create', 'com_proclaim')) {
                ToolbarHelper::save2copy('cwmlocation.save2copy');
            }

            ToolbarHelper::cancel('cwmlocation.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('proclaim', true);
    }
}
