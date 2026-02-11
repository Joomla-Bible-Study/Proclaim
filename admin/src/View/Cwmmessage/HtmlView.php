<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmmessage;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Administrator\Helper\BibleStructure;
use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * View class for Message
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
     * Media Files
     *
     * @var array
     * @since    7.0.0
     */
    protected array $mediafiles = [];

    /**
     * Admin Params
     *
     * @var Registry
     * @since    7.0.0
     */
    protected $admin_params;

    /**
     * Simple mode object
     *
     * @var   object
     * @since 9.2.3
     */
    protected $simple;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   11.1
     * @see     fetch()
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $this->form       = $this->get("Form");
        $this->item       = $this->get("Item");
        $this->canDo      = ContentHelper::getActions('com_proclaim', 'message', (int)$this->item->id);
        $input            = Factory::getApplication()->getInput();
        $option           = $input->get('option', '', 'cmd');
        $this->mediafiles = $this->get('MediaFiles');
        $this->state      = $this->get('State');

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Set some variables for use by the modal mediafile entry form from a study
        $app = Factory::getApplication();
        $app->setUserState($option . 'sid', $this->item->id);
        $app->setUserState($option . 'sdate', $this->item->studydate);
        $this->admin = Cwmparams::getAdmin();
        $registry    = new Registry();
        $registry->loadString($this->admin->params);
        $this->admin_params = $registry;
        $this->document     = Factory::getApplication()->getDocument();

        $this->simple = Cwmhelper::getSimpleView();

        // Load scripture autocomplete assets
        $document = Factory::getApplication()->getDocument();
        $wa       = $document->getWebAssetManager();
        $wa->useScript('com_proclaim.scripture-autocomplete');
        $document->addScriptOptions('com_proclaim.books', CwmscriptureHelper::getAllBooks());
        $document->addScriptOptions('com_proclaim.bibleStructure', BibleStructure::getStructureForJs());
        $document->addScriptOptions(
            'com_proclaim.defaultBibleVersion',
            $this->admin_params->get('default_bible_version', 'kjv')
        );

        // Push translatable strings to JS
        Text::script('JBS_STY_SEARCH_VERSIONS');

        // Set the toolbar
        $this->addToolbar();

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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);
        $isNew = ($this->item->id === 0);
        $title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
        ToolbarHelper::title(
            Text::_('JBS_CMN_STUDIES') . ': <small><small>[ ' . $title . ' ]</small></small>',
            'book book'
        );

        if ($isNew && $this->canDo->get('core.create', 'com_proclaim')) {
            ToolbarHelper::apply('cwmmessage.apply');
            ToolbarHelper::save('cwmmessage.save');
            ToolbarHelper::save2new('cwmmessage.save2new');
            ToolbarHelper::cancel('cwmmessage.cancel');
        } else {
            if ($this->canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::apply('cwmmessage.apply');
                ToolbarHelper::save('cwmmessage.save');

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($this->canDo->get('core.create', 'com_proclaim')) {
                    ToolbarHelper::save2new('cwmmessage.save2new');
                }
            }

            // If checked out, we can still save
            if ($this->canDo->get('core.create', 'com_proclaim')) {
                ToolbarHelper::save2copy('cwmmessage.save2copy');
            }

            ToolbarHelper::cancel('cwmmessage.cancel', 'JTOOLBAR_CLOSE');

            if ($this->canDo->get('core.edit', 'com_proclaim')) {
                ToolbarHelper::divider();
                ToolbarHelper::custom('resetHits', 'reset.png', 'Reset Hits', 'JBS_STY_RESET_HITS', false);
            }
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('message', true);
    }
}
