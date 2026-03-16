<?php

/**
 * Modal series picker field
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
 * Supports a modal series picker using Joomla's ModalSelectField (PostMessage + JoomlaDialog).
 *
 * @since  10.1.0
 */
class SeriesField extends ModalSelectField
{
    /**
     * The form field type.
     *
     * @var  string
     * @since    7.0.0
     */
    protected $type = 'Modal_Series';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     *
     * @return  bool  True on success.
     *
     * @since   10.1.0
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
        $linkSeries = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkSeries->setQuery([
            'option'                => 'com_proclaim',
            'view'                  => 'cwmseries',
            'layout'                => 'modal',
            'tmpl'                  => 'component',
            Session::getFormToken() => 1,
        ]);
        $linkSerie = clone $linkSeries;
        $linkSerie->setVar('view', 'cwmserie');

        if ($language) {
            $linkSeries->setVar('forcedLanguage', $language);
            $linkSerie->setVar('forcedLanguage', $language);

            $modalTitle = Text::_('JBS_CMN_SELECT_SERIES') . ' &#8212; ' . $this->getTitle();

            $this->dataAttributes['data-language'] = $language;
        } else {
            $modalTitle = Text::_('JBS_CMN_SELECT_SERIES');
        }

        $urlSelect = $linkSeries;
        $urlEdit   = clone $linkSerie;
        $urlEdit->setVar('layout', 'modal');
        $urlEdit->setVar('task', 'cwmserie.edit');
        $urlNew    = clone $linkSerie;
        $urlNew->setVar('layout', 'modal');
        $urlNew->setVar('task', 'cwmserie.add');

        $this->urls['select'] = (string) $urlSelect;
        $this->urls['new']    = (string) $urlNew;
        $this->urls['edit']   = (string) $urlEdit;

        // Modal titles
        $this->modalTitles['select'] = $modalTitle;
        $this->modalTitles['new']    = Text::_('JBS_SER_NEW_SERIES');
        $this->modalTitles['edit']   = Text::_('JBS_SER_EDIT_SERIES');

        $this->hint = $this->hint ?: Text::_('JBS_CMN_SELECT_SERIES');

        return $result;
    }

    /**
     * Method to retrieve the title of a selected item.
     *
     * @return int|string
     *
     * @throws \Exception
     * @since   10.1.0
     */
    #[\Override]
    protected function getValueTitle(): int|string
    {
        $value = (int) $this->value ?: '';
        $title = '';

        if ($value) {
            try {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select($db->quoteName('series_text'))
                    ->from($db->quoteName('#__bsms_series'))
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
