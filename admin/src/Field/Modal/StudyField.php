<?php

/**
 * Study field modal
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field\Modal;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Database\ParameterType;

/**
 * Field class for Modal_Study
 *
 * @package     Proclaim.Admin
 * @since       7.0.0
 */
class StudyField extends FormField
{
    /**
     * Set Modal_Study
     *
     * @var string
     *
     * @since 9.0.0
     */
    protected $type = 'Modal_Study';

    /**
     * Method to get the field input markup.
     *
     * @return    string  The field input markup.
     *
     * @throws \Exception
     * @since 9.0.0
     */
    protected function getInput(): string
    {
        $allowNew       = ((string)$this->element['new'] == 'true');
        $allowEdit      = ((string)$this->element['edit'] == 'true');
        $allowClear     = ((string)$this->element['clear'] != 'false');
        $allowSelect    = ((string)$this->element['select'] != 'false');
        $allowPropagate = ((string)$this->element['propagate'] == 'true');

        // Load language
        Factory::getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);

        // The active article id field.
        $value = (int)$this->value ?: '';

        // Create the modal id.
        $modalId = 'Messages_' . $this->id;

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        // Add the modal field script to the document head.
        $wa->useScript('field.modal-fields');

        // Script to proxy the select modal function to the modal-fields.js file.
        if ($allowSelect) {
            static $scriptSelect = null;

            if (is_null($scriptSelect)) {
                $scriptSelect = array();
            }

            if (!isset($scriptSelect[$this->id])) {
                $wa->addInlineScript(
                    "
				window.jSelectMessages_" . $this->id . " = function (id, title, catid, object, url, language) {
					window.processModalSelect('Messagess', '" . $this->id . "', id, title, catid, object, url, language);
				}",
                    [],
                    ['type' => 'module']
                );

                Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

                $scriptSelect[$this->id] = true;
            }
        }

        // Setup variables for display.
        $linkSeries = 'index.php?option=com_proclaim&amp;view=cwmmessages&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken(
        ) . '=1';
        $linkSerie  = 'index.php?option=com_proclaim&amp;view=cwmmessage&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken(
        ) . '=1';

        if (isset($this->element['language'])) {
            $linkSeries .= '&amp;forcedLanguage=' . $this->element['language'];
            $linkSerie  .= '&amp;forcedLanguage=' . $this->element['language'];
            $modalTitle = Text::_('JBS_CMN_SELECT_STUDY') . ' &#8212; ' . $this->element['label'];
        } else {
            $modalTitle = Text::_('JBS_CMN_SELECT_STUDY');
        }

        $urlSelect = $linkSeries . '&amp;function=jSelectMessages_' . $this->id;
        $urlEdit   = $linkSerie . '&amp;task=cwmmessage.edit&amp;id=\' + document.getElementById(&quot;' . $this->id . '_id&quot;).value + \'';
        $urlNew    = $linkSerie . '&amp;task=cwmmessage.add';

        if ($value) {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select($db->quoteName('studytitle') . 'AS name')
                ->from($db->quoteName('#__bsms_studies'))
                ->where($db->quoteName('id') . ' = :value')
                ->bind(':value', $value, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $title = $db->loadResult();
            } catch (\RuntimeException $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        $title = empty($title) ? Text::_('JBS_CMN_SELECT_STUDY') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        // The current series display field.
        $html = '';

        if ($allowSelect || $allowNew || $allowEdit || $allowClear) {
            $html .= '<span class="input-group">';
        }

        $html .= '<input class="form-control" id="' . $this->id . '_name" type="text" value="' . $title . '" readonly size="35">';

        // Select Message button
        if ($allowSelect) {
            $html .= '<button'
                . ' class="btn btn-primary' . ($value ? ' hidden' : '') . '"'
                . ' id="' . $this->id . '_select"'
                . ' data-bs-toggle="modal"'
                . ' type="button"'
                . ' data-bs-target="#ModalSelect' . $modalId . '">'
                . '<span class="icon-file" aria-hidden="true"></span> ' . Text::_('JSELECT')
                . '</button>';
        }

        // New Message button
        if ($allowNew) {
            $html .= '<button'
                . ' class="btn btn-secondary' . ($value ? ' hidden' : '') . '"'
                . ' id="' . $this->id . '_new"'
                . ' data-bs-toggle="modal"'
                . ' type="button"'
                . ' data-bs-target="#ModalNew' . $modalId . '">'
                . '<span class="icon-plus" aria-hidden="true"></span> ' . Text::_('JACTION_CREATE')
                . '</button>';
        }

        // Edit message button
        if ($allowEdit) {
            $html .= '<button'
                . ' class="btn btn-primary' . ($value ? '' : ' hidden') . '"'
                . ' id="' . $this->id . '_edit"'
                . ' data-bs-toggle="modal"'
                . ' type="button"'
                . ' data-bs-target="#ModalEdit' . $modalId . '">'
                . '<span class="icon-pen-square" aria-hidden="true"></span> ' . Text::_('JACTION_EDIT')
                . '</button>';
        }

        // Clear message button
        if ($allowClear) {
            $html .= '<button'
                . ' class="btn btn-secondary' . ($value ? '' : ' hidden') . '"'
                . ' id="' . $this->id . '_clear"'
                . ' type="button"'
                . ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
                . '<span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR')
                . '</button>';
        }

        if ($allowSelect || $allowNew || $allowEdit || $allowClear) {
            $html .= '</span>';
        }

        // Select message modal
        if ($allowSelect) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalSelect' . $modalId,
                array(
                    'title'      => $modalTitle,
                    'url'        => $urlSelect,
                    'height'     => '400px',
                    'width'      => '800px',
                    'bodyHeight' => 70,
                    'modalWidth' => 80,
                    'footer'     => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
                )
            );
        }

        // New message modal
        if ($allowNew) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalNew' . $modalId,
                array(
                    'title'       => Text::_('JBS_STY_NEW_STUDY'),
                    'backdrop'    => 'static',
                    'keyboard'    => false,
                    'closeButton' => false,
                    'url'         => $urlNew,
                    'height'      => '400px',
                    'width'       => '800px',
                    'bodyHeight'  => 70,
                    'modalWidth'  => 80,
                    'footer'      => '<button type="button" class="btn btn-secondary"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'cwmmessage\', \'cancel\', \'item-form\'); return false;">'
                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                        . '<button type="button" class="btn btn-primary"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'cwmmessage\', \'save\', \'item-form\'); return false;">'
                        . Text::_('JSAVE') . '</button>'
                        . '<button type="button" class="btn btn-success"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'cwmmessage\', \'apply\', \'item-form\'); return false;">'
                        . Text::_('JAPPLY') . '</button>',
                )
            );
        }

        // Edit message modal
        if ($allowEdit) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalEdit' . $modalId,
                array(
                    'title'       => Text::_('JBS_STY_EDIT_STUDY'),
                    'backdrop'    => 'static',
                    'keyboard'    => false,
                    'closeButton' => false,
                    'url'         => $urlEdit,
                    'height'      => '400px',
                    'width'       => '800px',
                    'bodyHeight'  => 70,
                    'modalWidth'  => 80,
                    'footer'      => '<button type="button" class="btn btn-secondary"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'cwmmessage\', \'cancel\', \'item-form\'); return false;">'
                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                        . '<button type="button" class="btn btn-primary"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'cwmmessage\', \'save\', \'item-form\'); return false;">'
                        . Text::_('JSAVE') . '</button>'
                        . '<button type="button" class="btn btn-success"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'cwmmessage\', \'apply\', \'item-form\'); return false;">'
                        . Text::_('JAPPLY') . '</button>',
                )
            );
        }

        // Note: class='required' for client side validation.
        $class = $this->required ? ' class="required modal-value"' : '';

        $html .= '<input type="hidden" id="' . $this->id . '_id"' . $class . ' data-required="' . (int)$this->required . '" name="' . $this->name
            . '" data-text="' . htmlspecialchars(
                Text::_('JBS_CMN_SELECT_STUDY'),
                ENT_COMPAT,
                'UTF-8'
            ) . '" value="' . $value . '">';

        return $html;
    }
}
