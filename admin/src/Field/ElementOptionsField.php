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
class ElementOptionsField extends PredefinedlistField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $type = 'ElementOptions';

    /**
     * A fixed set of options, not DB-backed.
     *
     * @var  array
     *
     * @since 10.5.6
     */
    protected $predefinedOptions = [
        '0' => 'JBS_CMN_NONE',
        '1' => 'JBS_TPL_PARAGRAPH',
        '2' => 'JBS_TPL_HEADER1',
        '3' => 'JBS_TPL_HEADER2',
        '4' => 'JBS_TPL_HEADER3',
        '5' => 'JBS_TPL_HEADER4',
        '6' => 'JBS_TPL_HEADER5',
        '7' => 'JBS_TPL_BLOCKQUOTE',
        '8' => 'JBS_TPL_DIV',
    ];
}
