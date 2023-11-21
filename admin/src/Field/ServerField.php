<?php

/**
 * Study field modal
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Database\ParameterType;

/**
 * Field class for Server
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class ServerField extends FormField
{
    /**
     * @var string
     * @since 9.0.0
     */
    protected $type = 'Modal_Server';

    /**
     * Get input form form
     *
     * @return string
     *
     * @throws \Exception
     * @since 7.0
     */
    protected function getInput(): string
    {
        $allowNew       = ((string)$this->element['new'] == 'true');
        $allowEdit      = ((string)$this->element['edit'] == 'true');
        $allowClear     = ((string)$this->element['clear'] != 'false');
        $allowSelect    = ((string)$this->element['select'] != 'false');
        $allowPropagate = ((string)$this->element['propagate'] == 'true');

        $languages = LanguageHelper::getContentLanguages(array(0, 1), false);

        // The active Server id field.
        $value = (int)$this->value ?: '';

        // Create the modal id.
        $modalId = 'Server_' . $this->id;

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
				window.jSelectServer_" . $this->id . " = function (id, title, object, url, language) {
					window.processModalSelect('Server', '" . $this->id . "', id, title, object, url, language);
				}",
                    [],
                    ['type' => 'module']
                );

                Text::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

                $scriptSelect[$this->id] = true;
            }
        }

        // Setup variables for display.
        $linkServers = 'index.php?option=com_proclaim&amp;view=cwmservers&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken(
        ) . '=1';
        $linkServer  = 'index.php?option=com_proclaim&amp;view=cwmserver&amp;layout=modal&amp;tmpl=component&amp;' . Session::getFormToken(
        ) . '=1';

        if (isset($this->element['language'])) {
            $linkServers .= '&amp;forcedLanguage=' . $this->element['language'];
            $linkServers .= '&amp;forcedLanguage=' . $this->element['language'];
            $modalTitle  = Text::_('COM_PROCLAIM_SELECT_AN_SERVER') . ' &#8212; ' . $this->element['label'];
        } else {
            $modalTitle = Text::_('COM_PROCLAIM_SELECT_AN_SERVER');
        }

        $urlSelect = $linkServers . '&amp;function=jSelectServer_' . $this->id;
        $urlEdit   = $linkServer . '&amp;task=cwmserver.edit&amp;id=\' + document.getElementById(&quot;' . $this->id . '_id&quot;).value + \'';
        $urlNew    = $linkServer . '&amp;task=cwmserver.add';

        if ($value) {
            // Get a reverse lookup of the server id to server name
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery('true');
            $query->select($db->quoteName('server_name'))
                ->from('#__bsms_servers')
                ->where($db->quoteName('id') . ' = :value')
                ->bind(':value', $value, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $title = $db->loadResult();
            } catch (\RuntimeException $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }

            if (empty($title)) {
                $value = '';
            }
        }

        $title = empty($title) ? Text::_('COM_PROCLAIM_SELECT_AN_SERVER') : htmlspecialchars(
            $title,
            ENT_QUOTES,
            'UTF-8'
        );

        // The current article display field.
        $html = "";

        if ($allowSelect || $allowNew || $allowEdit || $allowClear) {
            $html .= '<span class="input-group">';
        }

        $html .= '<input class="form-control" id="' . $this->id . '_name" type="text" value="' . $title . '" readonly size="35">';

        // Select server button
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

        // New server button
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

        // Edit server button
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

        // Clear server button
        if ($allowClear) {
            $html .= '<button'
                . ' class="btn btn-secondary' . ($value ? '' : ' hidden') . '"'
                . ' id="' . $this->id . '_clear"'
                . ' type="button"'
                . ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
                . '<span class="icon-times" aria-hidden="true"></span> ' . Text::_('JCLEAR')
                . '</button>';
        }

        // Propagate server button
        if ($allowPropagate && count($languages) > 2) {
            // Strip off language tag at the end
            $tagLength            = (int)strlen($this->element['language']);
            $callbackFunctionStem = substr("jSelectServer_" . $this->id, 0, -$tagLength);

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

        // Select server modal
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

        // New server modal
        if ($allowNew) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalNew' . $modalId,
                array(
                    'title'       => Text::_('COM_PROCLAIM_NEW_SERVER'),
                    'backdrop'    => 'static',
                    'keyboard'    => false,
                    'closeButton' => false,
                    'url'         => $urlNew,
                    'height'      => '400px',
                    'width'       => '800px',
                    'bodyHeight'  => 70,
                    'modalWidth'  => 80,
                    'footer'      => '<button type="button" class="btn btn-secondary"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'server\', \'cancel\', \'item-form\'); return false;">'
                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                        . '<button type="button" class="btn btn-primary"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'server\', \'save\', \'item-form\'); return false;">'
                        . Text::_('JSAVE') . '</button>'
                        . '<button type="button" class="btn btn-success"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'server\', \'apply\', \'item-form\'); return false;">'
                        . Text::_('JAPPLY') . '</button>',
                )
            );
        }

        // Edit server modal
        if ($allowEdit) {
            $html .= HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalEdit' . $modalId,
                array(
                    'title'       => Text::_('COM_PROCLAIM_EDIT_SERVER'),
                    'backdrop'    => 'static',
                    'keyboard'    => false,
                    'closeButton' => false,
                    'url'         => $urlEdit,
                    'height'      => '400px',
                    'width'       => '800px',
                    'bodyHeight'  => 70,
                    'modalWidth'  => 80,
                    'footer'      => '<button type="button" class="btn btn-secondary"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'server\', \'cancel\', \'item-form\'); return false;">'
                        . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
                        . '<button type="button" class="btn btn-primary"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'server\', \'save\', \'item-form\'); return false;">'
                        . Text::_('JSAVE') . '</button>'
                        . '<button type="button" class="btn btn-success"'
                        . ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'server\', \'apply\', \'item-form\'); return false;">'
                        . Text::_('JAPPLY') . '</button>',
                )
            );
        }

        // Note: class='required' for client side validation.
        $class = $this->required ? ' class="required modal-value"' : '';

        $html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class . ' data-required="' . (int)$this->required . '" name="' . $this->name
            . '" data-text="' . htmlspecialchars(
                Text::_('COM_PROCLAIM_SELECT_AN_SERVER'),
                ENT_COMPAT,
                'UTF-8'
            ) . '" value="' . $value . '" >';

        return $html;
    }

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   3.4
     */
    protected function getLabel()
    {
        return str_replace($this->id, $this->id . '_name', parent::getLabel());
    }
}
