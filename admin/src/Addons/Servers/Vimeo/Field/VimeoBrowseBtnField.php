<?php

/**
 * Vimeo Browse Button Field
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Vimeo\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Field to display the Vimeo Browse/Lookup Button
 *
 * Renders a button that opens a URL lookup panel. The user can paste a Vimeo
 * URL or video ID and have the title/thumbnail auto-populated via oEmbed.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class VimeoBrowseBtnField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  10.1.0
     */
    protected $type = 'VimeoBrowseBtn';

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
        $wa->useScript('com_proclaim.addon-vimeo-browser');

        return '<button type="button" class="btn btn-secondary" id="vimeo-browse-btn" onclick="Proclaim.VimeoBrowser.open()">' .
            '<span class="icon-search" aria-hidden="true"></span> ' . Text::_('JBS_ADDON_VIMEO_BROWSE') . '</button>';
    }
}
