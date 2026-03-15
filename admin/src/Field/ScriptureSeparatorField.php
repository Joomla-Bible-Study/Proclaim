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

/**
 * Scripture separator dropdown — single source of truth for separator options.
 *
 * Used in template.xml and mod_proclaim.xml.
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class ScriptureSeparatorField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.2.0
     */
    protected $type = 'ScriptureSeparator';

    /**
     * Get the field options.
     *
     * @return  array  Array of HTMLHelper option objects
     *
     * @since   10.2.0
     */
    protected function getOptions(): array
    {
        $options   = parent::getOptions();
        $options[] = HTMLHelper::_('select.option', 'newline', Text::_('JBS_TPL_SEPARATOR_STACKED'));
        $options[] = HTMLHelper::_('select.option', 'middot', Text::_('JBS_TPL_SEPARATOR_MIDDOT'));
        $options[] = HTMLHelper::_('select.option', 'pipe', Text::_('JBS_TPL_SEPARATOR_PIPE'));
        $options[] = HTMLHelper::_('select.option', 'semicolon', Text::_('JBS_TPL_SEPARATOR_SEMICOLON'));

        return $options;
    }
}
