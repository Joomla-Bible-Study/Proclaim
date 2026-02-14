<?php

/**
 * Local Browse Button Field
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Addons\Servers\Local\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Field to display the Local File Browse Button
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class LocalBrowseBtnField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  10.1.0
     */
    protected $type = 'LocalBrowseBtn';

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
        $wa->useScript('com_proclaim.addon-local-browser');
        $wa->useStyle('com_proclaim.addon-local-browser');

        return '<button type="button" class="btn btn-secondary" id="local-browse-btn" onclick="Proclaim.LocalBrowser.open()">' .
            '<span class="icon-search" aria-hidden="true"></span> ' . Text::_('JBS_MED_BROWSE_LOCAL_FILES') . '</button>';
    }
}
