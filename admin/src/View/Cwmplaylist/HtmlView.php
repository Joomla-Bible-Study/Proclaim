<?php

/**
 * View html
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmplaylist;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmplaylistModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for Playlist
 *
 * @package  Proclaim.Admin
 * @since    10.3.3
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Item
     *
     * @var ?object
     * @since 10.3.3
     */
    protected ?object $item = null;

    /**
     * State
     *
     * @var ?object
     * @since 10.3.3
     */
    protected ?object $state = null;

    /**
     * Can Do
     *
     * @var ?object
     * @since 10.3.3
     */
    protected ?object $canDo = null;

    /**
     * Form
     *
     * @var ?\Joomla\CMS\Form\Form
     * @since 10.3.3
     */
    public ?\Joomla\CMS\Form\Form $form = null;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmplaylistModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->state = $model->getState();
        $this->canDo = ContentHelper::getActions('com_proclaim', 'playlist', (int) $this->item->id);

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add Toolbar
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);
        $isNew = ((int) $this->item->id === 0);
        $title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');

        ToolbarHelper::title(
            Text::_('JBS_CMN_PLAYLISTS') . ': <small><small>[' . $title . ']</small></small>',
            'list list'
        );

        if ($isNew && $this->canDo->get('core.create', 'com_proclaim')) {
            ToolbarHelper::apply('cwmplaylist.apply');
            ToolbarHelper::save('cwmplaylist.save');
            ToolbarHelper::save2new('cwmplaylist.save2new');
            ToolbarHelper::cancel('cwmplaylist.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::apply('cwmplaylist.apply');
                ToolbarHelper::save('cwmplaylist.save');

                if ($this->canDo->get('core.create', 'com_proclaim')) {
                    ToolbarHelper::save2new('cwmplaylist.save2new');
                }
            }

            if ($this->canDo->get('core.create', 'com_proclaim')) {
                ToolbarHelper::save2copy('cwmplaylist.save2copy');
            }

            ToolbarHelper::cancel('cwmplaylist.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('playlist', true);
    }
}
