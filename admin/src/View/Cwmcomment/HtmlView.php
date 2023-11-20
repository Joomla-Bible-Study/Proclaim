<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
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
     * @param   string|null  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void  A string if successful, otherwise a Error object.
     *
     * @throws \Exception
     * @since  9.0.0
     */
    public function display($tpl = null): void
    {
        $this->form = $this->get("Form");
        $this->item = $this->get("Item");
        $this->state = $this->get("State");
        $this->canDo = ContentHelper::getActions('com_proclaim', 'comment', (int)$this->item->id);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Set the toolbar
        $this->addToolbar();

        $isNew = ($this->item->id == 0);
        $this->setDocumentTitle(
            $isNew ? Text::_('JBS_TITLE_COMMENT_CREATING') : Text::sprintf('JBS_TITLE_COMMENT_EDITING', $this->item->id)
        );

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
        Factory::getApplication()->input->set('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
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
        ToolbarHelper::help('proclaim', true);
    }
}
