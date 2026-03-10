<?php

/**
 * HtmlView for Podcast
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmpodcast;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Model\CwmpodcastModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for Podcast
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Form
     *
     * @var ?\Joomla\CMS\Form\Form
     * @since    7.0.0
     */
    protected ?\Joomla\CMS\Form\Form $form = null;

    /**
     * Item
     *
     * @var ?object
     * @since    7.0.0
     */
    protected ?object $item = null;

    /**
     * State
     *
     * @var ?object
     * @since    7.0.0
     */
    protected ?object $state = null;

    /**
     * Defaults
     *
     * @var ?array
     * @since    7.0.0
     */
    protected ?array $defaults = null;

    /**
     * Can Do
     *
     * @var ?object
     * @since    7.0.0
     */
    protected ?object $canDo = null;

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
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmpodcastModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->state = $model->getState();
        $this->canDo = ContentHelper::getActions('com_proclaim', 'podcast', (int)$this->item->id);
        $this->setLayout("edit");
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
    protected function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);
        $isNew = ((int)$this->item->id === 0);
        $title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
        ToolbarHelper::title(
            Text::_('JBS_CMN_PODCASTS') . ': <small><small>[' . $title . ']</small></small>',
            'feed feed'
        );

        if ($isNew && $this->canDo->get('core.create', 'com_proclaim')) {
            ToolbarHelper::apply('cwmpodcast.apply');
            ToolbarHelper::save('cwmpodcast.save');
            ToolbarHelper::save2new('cwmpodcast.save2new');
            ToolbarHelper::cancel('cwmpodcast.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::apply('cwmpodcast.apply');
                ToolbarHelper::save('cwmpodcast.save');

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($this->canDo->get('core.create', 'com_proclaim')) {
                    ToolbarHelper::save2new('cwmpodcast.save2new');
                }
            }

            // If checked out, we can still save
            if ($this->canDo->get('core.create', 'com_proclaim')) {
                ToolbarHelper::save2copy('cwmpodcast.save2copy');
            }

            ToolbarHelper::cancel('cwmpodcast.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('podcast', true);
    }
}
