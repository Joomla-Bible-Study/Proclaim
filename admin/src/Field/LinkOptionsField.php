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
class LinkOptionsField extends PredefinedlistField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $type = 'LinkOptions';

    /**
     * A fixed set of options, not DB-backed. See #1464.
     *
     * @var  array
     *
     * @since 10.5.6
     */
    protected $predefinedOptions = [
        '0'  => 'JBS_TPL_NO_LINK',
        '1'  => 'JBS_TPL_LINK_TO_DETAILS',
        '2'  => 'JBS_TPL_LINK_TO_MEDIA',
        '9'  => 'JBS_TPL_LINK_TO_DOWNLOAD',
        '3'  => 'JBS_TPL_LINK_TO_TEACHERS_PROFILE',
        '6'  => 'JBS_TPL_LINK_TO_FIRST_ARTICLE',
        '7'  => 'JBS_TPL_LINK_TO_VIRTUEMART',
        '8'  => 'JBS_TPL_LINK_TO_DOCMAN',
        '10' => 'JBS_TPL_LINK_TO_SERIES',
    ];
}
