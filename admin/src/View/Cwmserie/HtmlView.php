<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMSerie;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * JView class for Serie
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
    public $canDo;

    /**
     * Form
     *
     * @var object
     * @since    7.0.0
     */
    protected $form;

    /**
     * Item
     *
     * @var object
     * @since    7.0.0
     */
    protected $item;

    /**
     * Admin
     *
     * @var Registry
     * @since    7.0.0
     */
    protected $admin_params;

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
    public function display($tpl = null)
    {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->canDo = ContentHelper::getActions('com_proclaim', 'serie', (int)$this->item->id);
        $admin = Cwmparams::getAdmin();
        $registry = new Registry();
        $registry->loadString($admin->params);
        $this->admin_params = $registry;

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
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
    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
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
	    $help_url = 'https://www.christianwebministries.org/index.php?option=com_content&view=article&id=37:series-entry-screen-help&catid=20&Itemid=315&tmpl=component';
	    ToolbarHelper::help('proclaim', false, $url = $help_url, 'com_proclaim');

    }
}
