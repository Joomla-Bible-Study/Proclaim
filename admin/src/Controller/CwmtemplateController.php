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

        $baseDatabaseModel = $this->getModel('template');
        $model             = &$baseDatabaseModel;

        if ($model->copy($cid)) {
            $msg = Text::_('JBS_TPL_TEMPLATE_COPIED');
        } else {
            $msg = $model->getError();
        }

        $this->setRedirect('index.php?option=com_proclaim&view=templates', $msg);
    }

    /**
     * Make Template Default
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0
     */
    public function makeDefault(): void
    {
        $app   = Factory::getApplication();
        $input = $app->input;
        $cid   = $input->get('cid', [0], 'array');

        if (!\is_array($cid) || \count($cid) < 1) {
            $app->enqueueMessage(Text::_('JBS_CMN_SELECT_ITEM_UNPUBLISH'), 'error');
        }

        $this->setRedirect('index.php?option=com_proclaim&view=cwmtemplates');
    }

    /**
     * Get Template Settings
     *
     * @param   string  $template  filename
     *
     * @return boolean|string
     *
     * @since      7.0
     *
     * @deprecated 8.0.0 Not used in scope bcc
     */
    public function getTemplate($template): bool|string
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('tc.id, tc.templatecode,tc.type,tc.filename');
        $query->from('#__bsms_templatecode as tc');
        $query->where('tc.filename ="' . $template . '"');
        $db->setQuery($query);

        if (!$object = $db->loadObject()) {
            return false;
        }

        $templatereturn = '
                        INSERT INTO `#__bsms_templatecode` SET `type` = ' . $db->q($object->type) . ',
                        `templatecode` = ' . $db->q($object->templatecode) . ',
                        `filename` = ' . $db->q($template) . ',
                        `published` = ' . $db->q('1');

        return $templatereturn;
    }

    /**
     * AJAX handler to load a fieldset for lazy-loading tabs
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public function loadFieldset(): void
    {
        // Check for request forgeries
        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'error' => 'Invalid token']);
            Factory::getApplication()->close();

            return;
        }

        $app       = Factory::getApplication();
        $input     = $app->input;
        $fieldset  = $input->getString('fieldset', '');
        $id        = $input->getInt('id', 0);

        if (empty($fieldset)) {
            echo json_encode(['success' => false, 'error' => 'No fieldset specified']);
            $app->close();

            return;
        }

        try {
            /** @var \CWM\Component\Proclaim\Administrator\Model\CwmtemplateModel $model */
            $model = $this->getModel('Cwmtemplate');

            // Load the form
            $form = $model->getForm([], true);

            if (!$form) {
                echo json_encode(['success' => false, 'error' => 'Could not load form']);
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
                echo json_encode(['success' => false, 'error' => 'Fieldset not found: ' . $fieldset]);
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

            echo json_encode(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }

        $app->close();
    }
}
