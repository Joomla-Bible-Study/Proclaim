<?php

/**
 * YouTube Browse Button Field
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Youtube\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Field to display the YouTube Browse Button
 *
 * @package  Proclaim.Admin
 * @since    10.0.0
 */
class YoutubeBrowseBtnField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  10.0.0
     */
    protected $type = 'YoutubeBrowseBtn';

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   10.0.0
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
     * @throws \Exception
     * @since   10.0.0
     */
    protected function getInput(): string
    {
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->useScript('com_proclaim.addon-youtube-browser');

        // Output the button
        return '<button type="button" class="btn btn-secondary" id="youtube-browse-btn" onclick="Proclaim.YoutubeBrowser.open()">' .
            '<span class="icon-search" aria-hidden="true"></span> ' . Text::_('JBS_ADDON_YOUTUBE_BROWSE') . '</button>';
    }
}
