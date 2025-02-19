<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMTemplate;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for Template
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Pagination
     *
     * @var array
     * @since    7.0.0
     */
    protected $pagination;

    /**
     * State
     *
     * @var array
     * @since    7.0.0
     */
    protected $state;

    /**
     * Item
     *
     * @var object
     * @since    7.0.0
     */
    protected $item;

    /**
     * Types
     *
     * @var object
     * @since    7.0.0
     */
    protected $types;

    /**
     * Form
     *
     * @var object
     * @since    7.0.0
     */
    protected $form;

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
    public function display($tpl = null)
    {
        $this->item = $this->get('Item');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->types = $this->get('Types');
        $this->form = $this->get("Form");
        $this->canDo = ContentHelper::getActions('com_proclaim', 'template', (int)$this->item->id);

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
    protected function addToolbar(): void
    {
        Factory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id === 0);
        $title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
        ToolbarHelper::title(
            Text::_('JBS_CMN_TEMPLATE') . ': <small><small>[' . $title . ']</small></small>',
            'square square'
        );

        if ($isNew && $this->canDo->get('core.create', 'com_proclaim')) {
            ToolbarHelper::apply('cwmtemplate.apply');
            ToolbarHelper::save('cwmtemplate.save');
            ToolbarHelper::save2new('cwmtemplate.save2new');
            ToolbarHelper::cancel('cwmtemplate.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::apply('cwmtemplate.apply');
                ToolbarHelper::save('cwmtemplate.save');

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($this->canDo->get('core.create', 'com_proclaim')) {
                    ToolbarHelper::save2new('cwmtemplate.save2new');
                }
            }

            // If checked out, we can still save
            if ($this->canDo->get('core.create', 'com_proclaim')) {
                ToolbarHelper::save2copy('cwmtemplate.save2copy');
            }

            ToolbarHelper::cancel('cwmtemplate.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();
	    $help_url = 'https://www.christianwebministries.org/index.php?option=com_content&view=article&id=34:teacher-entry-help&catid=20&Itemid=315&tmpl=component';
	    ToolbarHelper::help('proclaim', false, $url = $help_url, 'com_proclaim');

    }
}
