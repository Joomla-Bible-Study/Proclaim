<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// Always load Proclaim API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
    require_once $api;
}
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;



/**
 * This is a dummy form element to load the components language file
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class LoadLanguageFileField extends ListField
{
    /**
     * The form field type.
     *
     * @var  string
     * @since 9.0.0
     */
    protected $type = 'LoadLanguageFile';

    /**
     * The hidden state for the form field.
     *
     * @var    boolean
     * @since 9.0.0
     */
    protected $hidden = true;

    /**
     * Get Lable
     *
     * @return string;
     * @since 9.0.0
     */
    public function getLabel(): string
    {
        // Return an empty string; nothing to display
        return '';
    }

    /**
     * Method to load the laguage file; nothing to display.
     *
     * @return  string  The field input markup.
     * @since 9.0.0
     */
    protected function getInput(): string
    {
        // Get language file; english language as fallback
        $language = Factory::getLanguage();
        $language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, 'en-GB', true);
        $language->load('com_proclaim', BIBLESTUDY_PATH_ADMIN, null, true);

        // Return an empty string; nothing to display
        return '';
    }
}
