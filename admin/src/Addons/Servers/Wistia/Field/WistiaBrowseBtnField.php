<?php

/**
 * Wistia Browse Button Field
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Wistia\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Field to display the Wistia Browse Button
 *
 * Renders a button that opens the Wistia video gallery browser. Users can
 * browse their Wistia account, search by name, filter by project, and select
 * a video to auto-fill the URL field. Falls back to URL lookup if no API token
 * is configured.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class WistiaBrowseBtnField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  10.1.0
     */
    protected $type = 'WistiaBrowseBtn';

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   10.1.0
     */
    protected function getLabel(): string
    {
        return '';
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   10.1.0
     */
    protected function getInput(): string
    {
        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->useScript('com_proclaim.addon-wistia-browser');

        return '<button type="button" class="btn btn-secondary" id="wistia-browse-btn" onclick="Proclaim.WistiaBrowser.open()">' .
            '<span class="icon-search" aria-hidden="true"></span> ' . Text::_('JBS_ADDON_WISTIA_BROWSE') . '</button>';
    }
}
