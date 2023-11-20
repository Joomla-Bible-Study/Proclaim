<?php
/**
 * Part of Proclaim Package
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

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

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
     * Method to get a list of options for a list input.
     *
     * @return  array   An array of JHtml options.
     *
     * @since 7.0
     */
    protected function getOptions(): array
    {
        $folder = Folder::files(JPATH_SITE . '/components/com_proclaim/tmpl/cwmsermons');

        foreach ($folder as $key => $value) {
            if ($value == 'default.php') {
                unset($folder[$key]);
            }

            if ($value == 'default_easy.php') {
                unset($folder[$key]);
            }

            if ($value == 'default_main.php') {
                unset($folder[$key]);
            }

            if ($value == 'default_formfooter.php') {
                unset($folder[$key]);
            }

            if ($value == 'default_formheader.php') {
                unset($folder[$key]);
            }

            if ($value == 'default.xml') {
                unset($folder[$key]);
            }
        }

        $folder    = str_replace(array('.php', 'default_'), '', $folder);
        $options   = array();
        $options[] = HtmlHelper::_('select.option', '0', Text::_('JBS_CMN_USE_DEFAULT'));
        if ($folder) {
            foreach ($folder as $file) {
                $options[] = HtmlHelper::_('select.option', $file, $file);
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
