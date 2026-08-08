<?php

/**
 * Study field modal
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
use Joomla\CMS\Form\Field\ModalSelectField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;

/**
 * Supports a modal study picker using Joomla's ModalSelectField (PostMessage + JoomlaDialog).
 *
 * @since  10.5.6
 */
class StudyField extends ModalSelectField
{
    /**
     * The form field type.
     *
     * @var  string
     * @since    7.0.0
     */
    protected $type = 'Modal_Study';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     *
     * @return  bool  True on success.
     *
     * @since   10.5.6
     */
    #[\Override]
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        // Normalize legacy "no selection" values (-1, 0) to empty
        if ((int) $value <= 0) {
            $value = '';
        }

        $result = parent::setup($element, $value, $group);

        if (!$result) {
            return $result;
        }

        Factory::getApplication()->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);

        $language = (string) $this->element['language'];

        // Build URLs using Uri objects (no &amp; encoding issues)
        $linkMessages = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkMessages->setQuery([
            'option'                => 'com_proclaim',
            'view'                  => 'cwmmessages',
            'layout'                => 'modal',
            'tmpl'                  => 'component',
            Session::getFormToken() => 1,
        ]);
        $linkMessage = clone $linkMessages;
        $linkMessage->setVar('view', 'cwmmessage');

        if ($language) {
            $linkMessages->setVar('forcedLanguage', $language);
            $linkMessage->setVar('forcedLanguage', $language);

            $modalTitle = Text::_('JBS_CMN_SELECT_STUDY') . ' &#8212; ' . $this->getTitle();

            $this->dataAttributes['data-language'] = $language;
        } else {
            $modalTitle = Text::_('JBS_CMN_SELECT_STUDY');
        }

        $urlSelect = $linkMessages;
        $urlEdit   = clone $linkMessage;
        $urlEdit->setVar('layout', 'modal');
        $urlEdit->setVar('task', 'cwmmessage.edit');
        $urlNew    = clone $linkMessage;
        $urlNew->setVar('layout', 'modal');
        $urlNew->setVar('task', 'cwmmessage.add');

        $this->urls['select'] = (string) $urlSelect;
        $this->urls['new']    = (string) $urlNew;
        $this->urls['edit']   = (string) $urlEdit;

        // Modal titles
        $this->modalTitles['select'] = $modalTitle;
        $this->modalTitles['new']    = Text::_('JBS_STY_NEW_STUDY');
        $this->modalTitles['edit']   = Text::_('JBS_STY_EDIT_STUDY');

        $this->hint = $this->hint ?: Text::_('JBS_CMN_SELECT_STUDY');

        return $result;
    }

    /**
     * Method to retrieve the title of a selected item.
     *
     * @return int|string
     *
     * @throws \Exception
     * @since   10.5.6
     */
    #[\Override]
    protected function getValueTitle(): int|string
    {
        $value = (int) $this->value ?: '';
        $title = '';

        if ($value) {
            try {
                $db    = $this->getDatabase();
                $query = $db->createQuery()
                    ->select($db->quoteName('studytitle'))
                    ->from($db->quoteName('#__bsms_studies'))
                    ->where($db->quoteName('id') . ' = :value')
                    ->bind(':value', $value, ParameterType::INTEGER);
                $db->setQuery($query);

                $title = $db->loadResult();
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $title ?: $value;
    }
}
