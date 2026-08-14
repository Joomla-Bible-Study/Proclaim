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
 * Date format dropdown — single source of truth for date display options.
 *
 * Used in template.xml and mod_proclaim.xml.
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class DateFormatField extends PredefinedlistField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.2.0
     */
    protected $type = 'DateFormat';

    /**
     * A fixed set of options, not DB-backed.
     *
     * @var  array
     *
     * @since 10.5.6
     */
    protected $predefinedOptions = [
        '0' => 'JBS_TPL_DATE_FORMAT_MMM_D_YYYY',
        '1' => 'JBS_TPL_DATE_FORMAT_MMM_D',
        '2' => 'JBS_TPL_DATE_FORMAT_M_D_YYYY',
        '3' => 'JBS_TPL_DATE_FORMAT_M_D',
        '4' => 'JBS_TPL_DATE_FORMAT_WD_MMMM_D_YYYY',
        '5' => 'JBS_TPL_DATE_FORMAT_MMMM_D_YYYY',
        '6' => 'JBS_TPL_DATE_FORMAT_D_MMMM_YYYY',
        '7' => 'JBS_TPL_DATE_FORMAT_D_M_YYYY',
        '8' => 'JBS_TPL_DATE_FORMAT_USE_GLOBAL',
        '9' => 'JBS_TPL_DATE_FORMAT_YYYY_MM_DD',
    ];
}
