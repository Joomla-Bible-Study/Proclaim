<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMComment;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for Comment
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
     *
     * @since 9.0.0
     */
    public $canDo;

    /**
     * Form Data
     *
     * @var object
     *
     * @since 9.0.0
     */
    protected $form;

    /**
     * Item
     *
     * @var object
     *
     * @since 9.0.0
     */
    protected $item;

    /**
     * State
     *
     * @var object
     *
     * @since 9.0.0
     */
    protected $state;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void  A string if successful, otherwise a Error object.
     *
     * @throws \Exception
     * @since  9.0.0
     */
    public function display($tpl = null): void
    {
        $this->form  = $this->get("Form");
        $this->item  = $this->get("Item");
        $this->state = $this->get("State");
        $this->canDo = ContentHelper::getActions('com_proclaim', 'comment', (int)$this->item->id);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Set the toolbar
        $this->addToolbar();

        // Display the template
        parent::display($tpl);
    }

    /**
     * Adds ToolBar
     *
     * @return void
     *
     * @throws \Exception
     * @since  7.0
     */
    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);
        $isNew = ((int) $this->item->id === 0);
        $title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
        ToolbarHelper::title(
            Text::_('JBS_CMN_COMMENTS') . ': <small><small>[ ' . $title . ' ]</small></small>',
            'comment comment'
        );

        if ($isNew && $this->canDo->get('core.create', 'com_proclaim')) {
            ToolbarHelper::apply('cwmcomment.apply');
            ToolbarHelper::save('cwmcomment.save');
            ToolbarHelper::save2new('cwmcomment.save2new');
            ToolbarHelper::cancel('cwmcomment.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::apply('cwmcomment.apply');
                ToolbarHelper::save('cwmcomment.save');
            }

            ToolbarHelper::cancel('cwmcomment.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();

        $help_url = 'https://www.christianwebministries.org/index.php?option=com_content&view=article&id=31:comment-editing-help&catid=20&Itemid=315&tmpl=component';
        ToolbarHelper::help('proclaim', false, $url = $help_url, 'com_proclaim');
    }
}
