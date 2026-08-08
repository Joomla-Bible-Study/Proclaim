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

use Joomla\CMS\Form\Field\PredefinedlistField;

/**
 * Scripture separator dropdown — single source of truth for separator options.
 *
 * Used in template.xml and mod_proclaim.xml.
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class ScriptureSeparatorField extends PredefinedlistField
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
     * A fixed set of options, not DB-backed. See #1464.
     *
     * @var  array
     *
     * @since 10.5.6
     */
    protected $predefinedOptions = [
        'newline'   => 'JBS_TPL_SEPARATOR_STACKED',
        'middot'    => 'JBS_TPL_SEPARATOR_MIDDOT',
        'pipe'      => 'JBS_TPL_SEPARATOR_PIPE',
        'semicolon' => 'JBS_TPL_SEPARATOR_SEMICOLON',
    ];
}
