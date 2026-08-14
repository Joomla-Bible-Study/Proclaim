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
 * Books List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.4
 */
class RowOptionsField extends PredefinedlistField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $type = 'RowOptions';

    /**
     * A fixed set of options, not DB-backed.
     *
     * @var  array
     *
     * @since 10.5.6
     */
    protected $predefinedOptions = [
        '0' => 'JBS_CMN_HIDE',
        '1' => 'JBS_TPL_ROW1',
        '2' => 'JBS_TPL_ROW2',
        '3' => 'JBS_TPL_ROW3',
        '4' => 'JBS_TPL_ROW4',
        '5' => 'JBS_TPL_ROW5',
        '6' => 'JBS_TPL_ROW6',
    ];
}
