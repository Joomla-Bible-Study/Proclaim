<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
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
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Supports a modal location picker.
 *
 * @since  1.6
 */
class LocationField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    protected $type = 'Modal_Location';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @throws \Exception
     * @since   7.0.0
     */
    #[\Override]
    protected function getInput(): string
    {
        $allowNew       = ((string)$this->element['new'] === 'true');
        $allowEdit      = ((string)$this->element['edit'] === 'true');
        $allowClear     = ((string)$this->element['clear'] !== 'false');
        $allowSelect    = ((string)$this->element['select'] !== 'false');
        $allowPropagate = ((string)$this->element['propagate'] === 'true');

        $languages = LanguageHelper::getContentLanguages([0, 1], false);

        // Load language
        Factory::getApplication()->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);

        // The active location id field.
        $value = (int)$this->value ?: '';

        // Create the modal id.
        $modalId = 'Cwmlocation_' . $this->id;

        /** @var WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();

        // Add the modal field script to the document head.
        $wa->useScript('field.modal-fields');

        // Script to proxy the select modal function to the modal-fields.js file.
        if ($allowSelect) {
            static $scriptSelect = null;

            if (\is_null($scriptSelect)) {
                $scriptSelect = [];
            }

            if (!isset($scriptSelect[$this->id])) {
                $wa->addInlineScript(
                    '
				window.jSelectCwmlocation_' . $this->id . " = function (id, location_text) {
					window.processModalSelect('Cwmlocation', '" . $this->id . "', id, location_text);
				}",
                    [],
                    ['type' => 'module']
                );

                Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

                $scriptSelect[$this->id] = true;
            }
        }

        // Setup variables for display.
        $linkLocations = 'index.php?option=com_proclaim&amp;view=cwmlocations&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';
        $linkLocation  = 'index.php?option=com_proclaim&amp;view=cwmlocation&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken() . '=1';

        if (isset($this->element['language'])) {
            $linkLocations .= '&amp;forcedLanguage=' . $this->element['language'];
            $linkLocation .= '&amp;forcedLanguage=' . $this->element['language'];
            $modalTitle   = Text::_('JBS_CMN_SELECT_LOCATION') . ' &#8212; ' . $this->element['label'];
        } else {
            $modalTitle = Text::_('JBS_CMN_SELECT_LOCATION');
        }

        $urlSelect = $linkLocations . '&amp;function=jSelectCwmlocation_' . $this->id;
        $urlEdit   = $linkLocation . '&amp;task=cwmlocation.edit&amp;id=\' + document.getElementById(&quot;' . $this->id . '_id&quot;).value + \'';
        $urlNew    = $linkLocation . '&amp;task=cwmlocation.add';

        $title = null;

        if ($value) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('location_text'))
                ->from($db->quoteName('#__bsms_locations'))
                ->where($db->quoteName('id') . ' = :value')
                ->bind(':value', $value, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $title = $db->loadResult();
            } catch (\RuntimeException $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        $title = empty($title) ? Text::_('JBS_CMN_SELECT_LOCATION') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        // The current location display field.
        $html = '';

        if ($allowSelect || $allowNew || $allowEdit || $allowClear) {
            $html .= '<span class="input-group">';
        }

        $html .= '<input class="form-control" id="' . $this->id . '_name" type="text" value="' . $title . '" readonly size="35">';

        // Select location button
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

        // New location button
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

        // Edit location button
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

        // Clear location button
        if ($allowClear) {
            $html .= '<button'
                . ' class="btn btn-secondary' . ($value ? '' : ' hidden') . '"'
                . ' id="' . $this->id . '_clear"'
                . ' type="button"'
                . ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
                . '<span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR')
                . '</button>';
        }

        // Propagate location button
        if ($allowPropagate && \count($languages) > 2) {
            // Strip off language tag at the end
            $tagLength            = (int)\strlen($this->element['language']);
            $callbackFunctionStem = substr("jSelectCwmlocation_" . $this->id, 0, -$tagLength);

            $html .= '<button'
                . ' class="btn btn-primary' . ($value ? '' : ' hidden') . '"'
                . ' type="button"'
                . ' id="' . $this->id . '_propagate"'
                . ' title="' . Text::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_TIP') . '"'
                . ' onclick="Joomla.propagateAssociation(\'' . $this->id . '\', \'' . $callbackFunctionStem . '\');">'
                . '<span class="icon-sync" aria-hidden="true"></span> ' . Text::_(
                    'JGLOBAL_ASSOCIATIONS_PROPAGATE_BUTTON'
                )
                . '</button>';
        }

        if ($allowSelect || $allowNew || $allowEdit || $allowClear) {
            $html .= '</span>';
        }

        // Select location modal
        if ($allowSelect) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalSelect' . $modalId,
                [
                    'title'      => $modalTitle,
                    'url'        => $urlSelect,
                    'height'     => '400px',
                    'width'      => '800px',
                    'bodyHeight' => 70,
                    'modalWidth' => 80,
                    'footer'     => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
                ]
            );
        }

        // New location modal
        if ($allowNew) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalNew' . $modalId,
                [
                    'title'       => Text::_('JACTION_CREATE') . ' ' . Text::_('JBS_CMN_LOCATION'),
                    'backdrop'    => 'static',
                    'keyboard'    => false,
                    'closeButton' => false,
                    'url'         => $urlNew,
                    'height'      => '400px',
                    'width'       => '800px',
                    'bodyHeight'  => 70,
                    'modalWidth'  => 80,
                    'footer'      => '<button type="button" class="btn btn-secondary"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'cwmlocation\', \'cancel\', \'item-form\'); return false;">'
                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                        . '<button type="button" class="btn btn-primary"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'cwmlocation\', \'save\', \'item-form\'); return false;">'
                        . Text::_('JSAVE') . '</button>'
                        . '<button type="button" class="btn btn-success"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'cwmlocation\', \'apply\', \'item-form\'); return false;">'
                        . Text::_('JAPPLY') . '</button>',
                ]
            );
        }

        // Edit location modal
        if ($allowEdit) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalEdit' . $modalId,
                [
                    'title'       => Text::_('JACTION_EDIT') . ' ' . Text::_('JBS_CMN_LOCATION'),
                    'backdrop'    => 'static',
                    'keyboard'    => false,
                    'closeButton' => false,
                    'url'         => $urlEdit,
                    'height'      => '400px',
                    'width'       => '800px',
                    'bodyHeight'  => 70,
                    'modalWidth'  => 80,
                    'footer'      => '<button type="button" class="btn btn-secondary"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'cwmlocation\', \'cancel\', \'item-form\'); return false;">'
                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                        . '<button type="button" class="btn btn-primary"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'cwmlocation\', \'save\', \'item-form\'); return false;">'
                        . Text::_('JSAVE') . '</button>'
                        . '<button type="button" class="btn btn-success"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'cwmlocation\', \'apply\', \'item-form\'); return false;">'
                        . Text::_('JAPPLY') . '</button>',
                ]
            );
        }

        // Note: class='required' for client side validation.
        $class = $this->required ? ' class="required modal-value"' : '';

        $html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class . ' data-required="' . (int)$this->required . '" name="' . $this->name
            . '" data-text="' . htmlspecialchars(
                Text::_('JBS_CMN_SELECT_LOCATION'),
                ENT_COMPAT,
                'UTF-8'
            ) . '" value="' . $value . '">';

        return $html;
    }

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   3.4
     */
    #[\Override]
    protected function getLabel(): string
    {
        return str_replace($this->id, $this->id . '_name', parent::getLabel());
    }
}
