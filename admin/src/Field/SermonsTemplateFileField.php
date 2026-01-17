<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;

/**
 * Message Type List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class SermonsTemplateFileField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $type = 'SermonsTemplateFile';

    /**
     * Cached template file options
     *
     * @var array|null
     * @since 9.0.0
     */
    private static ?array $cachedOptions = null;

    /**
     * Files to exclude from the template list
     *
     * @var array
     * @since 9.0.0
     */
    private const EXCLUDED_FILES = [
        'default.php',
        'default_easy.php',
        'default_main.php',
        'default_formfooter.php',
        'default_formheader.php',
        'default.xml',
    ];

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array   An array of JHtml options.
     *
     * @since 7.0
     */
    protected function getOptions(): array
    {
        // Return cached options if available
        if (self::$cachedOptions !== null) {
            return array_merge(parent::getOptions(), self::$cachedOptions);
        }

        self::$cachedOptions   = [];
        self::$cachedOptions[] = HTMLHelper::_('select.option', '0', Text::_('JBS_CMN_USE_DEFAULT'));

        $path = JPATH_SITE . '/components/com_proclaim/tmpl/cwmsermons';

        if (!is_dir($path)) {
            return array_merge(parent::getOptions(), self::$cachedOptions);
        }

        $files = Folder::files($path, '\.php$');

        if (!$files) {
            return array_merge(parent::getOptions(), self::$cachedOptions);
        }

        // Filter and process files in a single pass
        foreach ($files as $file) {
            if (\in_array($file, self::EXCLUDED_FILES, true)) {
                continue;
            }

            // Extract template name (remove .php and default_ prefix)
            $name                  = str_replace(['.php', 'default_'], '', $file);
            self::$cachedOptions[] = HTMLHelper::_('select.option', $name, $name);
        }

        return array_merge(parent::getOptions(), self::$cachedOptions);
    }
}
