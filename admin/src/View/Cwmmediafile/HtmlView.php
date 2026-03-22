<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\Cwmmediafile;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/**
 * View class for MediaFile
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /** @var ?object
     * @since    7.0.0
     */
    public ?object $canDo = null;

    /** @var ?Registry
     * @since    7.0.0
     */
    public ?Registry $admin_params = null;

    /** @var mixed
     * @since    7.0.0
     */
    public mixed $media_form = null;

    /** @var ?object
     * @since    7.0.0
     */
    public ?object $item = null;
    /** @var mixed
     * @since    9.1.3
     */
    public mixed $addon = null;

    /** @var ?\Joomla\CMS\Form\Form
     * @since    10.2.0
     */
    public ?\Joomla\CMS\Form\Form $tracks_form = null;

    /**
     * Form
     *
     * @var ?\Joomla\CMS\Form\Form
     * @since    7.0.0
     */
    public ?\Joomla\CMS\Form\Form $form = null;
    /** @var ?Registry
     * @since    7.0.0
     */
    protected ?Registry $state = null;
    /** @var ?object
     * @since    7.0.0
     */
    protected ?object $admin = null;
    /** @var ?object
     * @since    7.0.0
     */
    protected ?object $options = null;

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
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmmediafileModel $model */
        $model = $this->getModel();
        $model->setUseExceptions(true);

        $app                = Factory::getApplication();
        $this->form         = $model->getForm();
        $media_form         = $model->getMediaForm();
        $this->item         = $model->getItem();
        $this->state        = $model->getState();
        $this->canDo        = ContentHelper::getActions('com_proclaim', 'mediafile', (int)$this->item->id);
        $this->admin_params = $this->state->get('administrator');

        // Get server params for default values
        $s_params = $this->state->get('s_params', []);

        if ($media_form !== null) {
            // For new items, bind server defaults to the media form before rendering
            $isNew = empty($this->item->id);

            if ($isNew && !empty($s_params)) {
                // Bind server defaults to form - this sets field values before rendering
                $media_form->bind(['params' => $s_params]);
            }

            // Wrap the media form with server params for addon default value handling (PHP 8.2+ compatible)
            $this->media_form = new class ($media_form, $s_params) {
                private $form;
                public array $s_params;

                public function __construct($form, array $s_params)
                {
                    $this->form     = $form;
                    $this->s_params = $s_params;
                }

                public function __call(string $name, array $args): mixed
                {
                    return $this->form->$name(...$args);
                }
            };

            // Load the addon
            $serverType  = $this->state->get('type');
            $this->addon = $serverType ? CWMAddon::getInstance($serverType) : null;
        } else {
            $this->media_form = null;
            $this->addon      = null;
        }

        $options       = $app->getInput()->get('options');
        $this->options = new \stdClass();

        $this->options->study_id   = null;
        $this->options->createdate = null;

        if ($options) {
            $options = explode('&', base64_decode($app->getInput()->get('options')));

            foreach ($options as $option_st) {
                $option_st = explode('=', $option_st);

                if ($option_st[0] === 'study_id') {
                    $this->options->study_id = $option_st[1];
                }

                if ($option_st[0] === 'createdate') {
                    $this->options->createdate = $option_st[1];
                }
            }
        }

        // Load the tracks form (chapters + subtitles)
        $this->tracks_form = $model->getTracksForm();

        // Needed to load the article field type for the article selector
        FormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_content/models/fields/modal');

        // Load the admin script for file size converter
        $app->getDocument()->getWebAssetManager()->useScript('com_proclaim.cwmcorejs-admin');

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

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

        $user       = Factory::getApplication()->getIdentity();
        $userId     = $user->id;
        $isNew      = ($this->item->id === 0);
        $checkedOut = !($this->item->checked_out === null || $this->item->checked_out == $userId);
        $title      = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
        $toolbar    = Toolbar::getInstance();

        ToolbarHelper::title(
            Text::_('JBS_CMN_MEDIA_FILES') . ': <small><small>[' . $title . ']</small></small>',
            'video video'
        );

        if ($isNew && $this->canDo->get('core.create', 'com_proclaim')) {
            $toolbar->apply('cwmmediafile.apply');

            $saveGroup = $toolbar->dropdownButton('save-group');
            $saveGroup->configure(
                function (Toolbar $childBar) use ($user) {
                    $childBar->save('cwmmediafile.save');

                    $childBar->save2new('cwmmediafile.save2new');
                }
            );
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $this->canDo->get('core.edit') || ($this->canDo->get('core.edit.own'));

            // Can't save the record if it's checked out and editable
            if (!$checkedOut && $itemEditable) {
                $toolbar->apply('cwmmediafile.apply');
            }

            $saveGroup = $toolbar->dropdownButton('save-group');

            $canDo = $this->canDo;

            $saveGroup->configure(
                function (Toolbar $childBar) use ($checkedOut, $itemEditable, $canDo, $user) {
                    // Can't save the record if it's checked out and editable
                    if (!$checkedOut && $itemEditable) {
                        $childBar->save('cwmmediafile.save');

                        // We can save this record, but check the create permission to see if we can return to make a new one.
                        if ($canDo->get('core.create')) {
                            $childBar->save2new('cwmmediafile.save2new');
                        }
                    }

                    // If checked out, we can still save2menu
                    if ($user->authorise('core.create', 'com_menus.menu')) {
                        $childBar->save('cwmmediafile.save2menu', 'JTOOLBAR_SAVE_TO_MENU');
                    }

                    // If checked out, we can still save
                    if ($canDo->get('core.create')) {
                        $childBar->save2copy('cwmmediafile.save2copy');
                    }
                }
            );
        }

        $toolbar->cancel('cwmmediafile.cancel');

        ToolbarHelper::divider();
        ToolbarHelper::help('mediafile', true);
    }
}
