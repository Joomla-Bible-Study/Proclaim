<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMTopic;

// No Direct Access
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for Topic
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
    protected mixed $form;

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
     * @var object
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
     * @return  void  A string if successful, otherwise a JError object.
     *
     * @throws  \Exception
     * @since   11.1
     * @see     fetch()
     */
    public function display($tpl = null): void
    {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");
        $this->canDo = ContentHelper::getActions('com_proclaim', 'topic', (int)$this->item->id);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $this->setLayout("edit");

        // Set the toolbar
        $this->addToolbar();

        $isNew = ($this->item->id < 1);

        // Display the template
        parent::display($tpl);
    }

    /**
     * Adds ToolBar
     *
     * @return void
     *
     * @since 7.0
     */
    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);
        $isNew = ((int)$this->item->id === 0);
        $title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
        ToolbarHelper::title(Text::_('JBS_CMN_TOPICS') . ': <small><small>[' . $title . ']</small></small>', 'tag tag');

        if ($isNew && $this->canDo->get('core.create', 'com_proclaim')) {
            ToolbarHelper::apply('cwmtopic.apply');
            ToolbarHelper::save('cwmtopic.save');
            ToolbarHelper::cancel('cwmtopic.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::apply('cwmtopic.apply');
                ToolbarHelper::save('cwmtopic.save');
            }

            ToolbarHelper::cancel('cwmtopic.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();
        $help_url = 'https://www.christianwebministries.org/index.php?option=com_content&view=article&id=39:topic-entry-screen-help&catid=20&Itemid=315&tmpl=component';
        ToolbarHelper::help('proclaim', false, $url = $help_url, 'com_proclaim');
    }
}
