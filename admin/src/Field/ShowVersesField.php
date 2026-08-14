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
 * Show Verses dropdown — single source of truth for verse display options.
 *
 * Used in template.xml and mod_proclaim.xml.
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class ShowVersesField extends PredefinedlistField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.2.0
     */
    protected $type = 'ShowVerses';

    /**
     * A fixed set of options, not DB-backed.
     *
     * @var  array
     *
     * @since 10.5.6
     */
    protected $predefinedOptions = [
        '0' => 'JBS_TPL_SHOW_ONLY_CHAPTERS',
        '1' => 'JBS_TPL_SHOW_VERSES_AND_CHAPTERS',
        '2' => 'JBS_TPL_SHOW_ONLY_BOOKS',
    ];
}
