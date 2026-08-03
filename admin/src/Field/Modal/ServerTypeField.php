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
use Joomla\CMS\Form\Field\ModalSelectField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

/**
 * Supports a modal server-type picker using Joomla's ModalSelectField (PostMessage + JoomlaDialog).
 *
 * The field's value is a lowercased server-addon type key (e.g. "youtube",
 * "local"), not a numeric id, so its title comes from
 * CwmserversModel::getTypeReverseLookup() rather than a SQL row lookup.
 *
 * @since  __DEPLOY_VERSION__
 */
class ServerTypeField extends ModalSelectField
{
    /**
     * The form field type.
     *
     * @var string
     *
     * @since 9.0.0
     */
    protected $type = 'Modal_ServerType';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     *
     * @return  bool  True on success.
     *
     * @since   __DEPLOY_VERSION__
     */
    #[\Override]
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        // The value is a server-addon type key, not a numeric id — normalize case only.
        $value = strtolower((string) $value);

        $result = parent::setup($element, $value, $group);

        if (!$result) {
            return $result;
        }

        Factory::getApplication()->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);

        $language = (string) $this->element['language'];
        $recordId = (int) $this->form->getValue('id');

        // Build URLs using Uri objects (no &amp; encoding issues)
        $linkSelect = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkSelect->setQuery([
            'option'                => 'com_proclaim',
            'view'                  => 'cwmservers',
            'layout'                => 'types',
            'tmpl'                  => 'component',
            'recordId'              => $recordId,
            Session::getFormToken() => 1,
        ]);
        $linkServer = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkServer->setQuery([
            'option'                => 'com_proclaim',
            'view'                  => 'cwmserver',
            'layout'                => 'modal',
            'tmpl'                  => 'component',
            'recordId'              => $recordId,
            Session::getFormToken() => 1,
        ]);

        if ($language) {
            $linkSelect->setVar('forcedLanguage', $language);
            $linkServer->setVar('forcedLanguage', $language);

            $modalTitle = Text::_('JBS_CMN_SELECT_SERVERTYPE') . ' &#8212; ' . $this->getTitle();

            $this->dataAttributes['data-language'] = $language;
        } else {
            $modalTitle = Text::_('JBS_CMN_SELECT_SERVERTYPE');
        }

        $urlSelect = $linkSelect;
        $urlEdit   = clone $linkServer;
        $urlEdit->setVar('task', 'cwmserver.edit');
        $urlNew    = clone $linkServer;
        $urlNew->setVar('task', 'cwmserver.add');

        $this->urls['select'] = (string) $urlSelect;
        $this->urls['new']    = (string) $urlNew;
        $this->urls['edit']   = (string) $urlEdit;

        // Modal titles
        $this->modalTitles['select'] = $modalTitle;
        $this->modalTitles['new']    = Text::_('JBS_STY_NEW_SERVERTYPE');
        $this->modalTitles['edit']   = Text::_('JBS_STY_EDIT_SERVERTYPE');

        $this->hint = $this->hint ?: Text::_('JBS_CMN_SELECT_SERVERTYPE');

        return $result;
    }

    /**
     * Method to retrieve the title of a selected item.
     *
     * Not a SQL lookup: the value is a server-addon type key resolved
     * against the addon-XML-derived reverse lookup built by
     * CwmserversModel::getTypeReverseLookup().
     *
     * @return int|string
     *
     * @throws \Exception
     * @since   __DEPLOY_VERSION__
     */
    #[\Override]
    protected function getValueTitle(): int|string
    {
        $value = (string) $this->value;

        if ($value === '') {
            return '';
        }

        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmserversModel $model */
        $model         = Factory::getApplication()->bootComponent('com_proclaim')
            ->getMVCFactory()->createModel('Cwmservers', 'Administrator');
        $reverseLookup = $model->getTypeReverseLookup();

        return ArrayHelper::getValue($reverseLookup, $value) ?: $value;
    }
}
