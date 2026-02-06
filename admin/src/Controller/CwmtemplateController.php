<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

/**
 * Template controller class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmtemplateController extends FormController
{
    /**
     * Copy Template
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0
     */
    public function copy(): void
    {
        $input = Factory::getApplication()->getInput();
        $cid   = $input->get('cid', '', 'array');
        ArrayHelper::toInteger($cid);

        $model = $this->getModel('Cwmtemplate');

        try {
            $model->copy($cid);
            $msg  = Text::_('JBS_TPL_TEMPLATE_COPIED');
            $type = 'message';
        } catch (\RuntimeException $e) {
            $msg  = $e->getMessage();
            $type = 'error';
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmtemplates', $msg, $type);
    }

    /**
     * AJAX handler to load a fieldset for lazy-loading tabs
     *
     * @return  void
     *
     * @throws \JsonException
     * @since   10.0.0
     */
    public function loadFieldset(): void
    {
        // Check for request forgeries
        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'error' => 'Invalid token'], JSON_THROW_ON_ERROR);
            Factory::getApplication()->close();

            return;
        }

        $app       = Factory::getApplication();
        $input     = $app->input;
        $fieldset  = $input->getString('fieldset', '');
        $id        = $input->getInt('id', 0);

        if (empty($fieldset)) {
            echo json_encode(['success' => false, 'error' => 'No fieldset specified'], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            /** @var \CWM\Component\Proclaim\Administrator\Model\CwmtemplateModel $model */
            $model = $this->getModel('Cwmtemplate');

            // Load the form
            $form = $model->getForm([], true);

            if (!$form) {
                echo json_encode(['success' => false, 'error' => 'Could not load form'], JSON_THROW_ON_ERROR);
                $app->close();

                return;
            }

            // If editing an existing template, bind the data
            if ($id > 0) {
                $item = $model->getItem($id);

                if ($item) {
                    $form->bind($item);
                }
            }

            // Get the fieldset
            $fields = $form->getFieldset($fieldset);

            if (empty($fields)) {
                echo json_encode(
                    ['success' => false, 'error' => 'Fieldset not found: ' . $fieldset],
                    JSON_THROW_ON_ERROR
                );
                $app->close();

                return;
            }

            // Render the fieldset HTML
            $html = '';

            foreach ($fields as $field) {
                $html .= '<div class="control-group">';
                $html .= '<div class="control-label">' . $field->label . '</div>';
                $html .= '<div class="controls">' . $field->input;
                $html .= '<br />' . Text::_($field->description);
                $html .= '</div></div>';
            }

            echo json_encode(['success' => true, 'html' => $html], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }

    /**
     * AJAX handler to load the Layout Editor tab content
     * This enables lazy-loading of the Layout Editor for faster initial page load
     *
     * @return  void
     *
     * @since   10.2.0
     */
    public function loadLayoutEditor(): void
    {
        $app   = Factory::getApplication();
        $input = $app->input;
        $id    = $input->getInt('id', 0);

        try {
            /** @var \CWM\Component\Proclaim\Administrator\Model\CwmtemplateModel $model */
            $model = $this->getModel('Cwmtemplate');

            // Load the form
            $form = $model->getForm([], true);

            if (!$form) {
                echo '<div class="alert alert-danger">Could not load form</div>';
                $app->close();

                return;
            }

            // Load the item data
            $item = $model->getItem($id);

            if ($item && $id > 0) {
                $form->bind($item);
            }

            // Capture output from edit_layout.php
            ob_start();

            // edit_layout.php expects: $this->form, $this->item, $this->getDocument()
            $layoutFile = JPATH_ADMINISTRATOR . '/components/com_proclaim/tmpl/cwmtemplate/edit_layout.php';

            if (file_exists($layoutFile)) {
                // Bind to an anonymous class that provides the expected context
                $context = new class ($app, $form, $item) {
                    public $form;
                    public $item;
                    private $app;

                    public function __construct($app, $form, $item)
                    {
                        $this->app  = $app;
                        $this->form = $form;
                        $this->item = $item;
                    }

                    public function getDocument()
                    {
                        return $this->app->getDocument();
                    }
                };

                // Include the layout file with proper context
                (function ($filePath) {
                    include $filePath;
                })->call($context, $layoutFile);
            } else {
                echo '<div class="alert alert-danger">Layout file not found</div>';
            }

            $html = ob_get_clean();

            // Output the HTML
            echo $html;
        } catch (\Exception $e) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
        }

        $app->close();
    }

    /**
     * Method to run batch operations.
     *
     * @param   object|null  $model  The model.
     *
     * @return  bool     True if successful, false otherwise and internal error is set.
     *
     * @throws \Exception
     * @since   1.6
     */
    public function batch($model = null): bool
    {
        $this->checkToken();

        // Preset the redirect
        $this->setRedirect(
            Route::_('index.php?option=com_proclaim&view=cwmtemplates' . $this->getRedirectToListAppend(), false)
        );

        return parent::batch($this->getModel());
    }
}
