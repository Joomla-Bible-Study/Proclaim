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

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Mime Type List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class MimeTypeField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $type = 'MimeType';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array   An array of JHtml options.
     *
     * @since 7.0
     */
    protected function getOptions(): array
    {
        $MediaHelper = new Cwmmedia();
        $mimetypes   = $MediaHelper->getMimetypes();

        $options = array();

        foreach ($mimetypes as $key => $message) {
            $options[] = HTMLHelper::_('select.option', $message, $key);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
