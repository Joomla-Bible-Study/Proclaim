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

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Language\Text;

/**
 * Form Field class for the FileSize
 *
 * Renders a plain text input plus a converter button. Extends TextField so
 * the input itself (size/maxlength/class/readonly/disabled/onchange) renders
 * through core's own layout rather than hand-concatenated attribute strings.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class FilesizeField extends TextField
{
    /**
     *  Set Naming of type
     *
     * @var string
     *
     * @since 9.0.0
     */
    protected $type = 'Filesize';

    /**
     * Get impute of form
     *
     * @return string
     *
     * @since 1.5
     */
    #[\Override]
    protected function getInput(): string
    {
        return '<span class="input-group">' . parent::getInput() . ' ' . $this->sizeConverter() . '</span>';
    }

    /**
     * Returns converted size
     *
     * @return string
     *
     * @since 9.0.0
     */
    private function sizeConverter(): string
    {
        return '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#collapseModal">
            <span class="icon-checkbox-partial" aria-hidden="true"></span> ' . Text::_('JBS_MED_FILESIZE_CONVERTER') .
            '</button>';
    }
}
