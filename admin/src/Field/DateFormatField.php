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
 * Date format dropdown — single source of truth for date display options.
 *
 * Used in template.xml and mod_proclaim.xml.
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class DateFormatField extends ListField
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
     * Get the field options.
     *
     * @return  array  Array of HTMLHelper option objects
     *
     * @since   10.2.0
     */
    protected function getOptions(): array
    {
        $options   = parent::getOptions();
        $options[] = HTMLHelper::_('select.option', '0', Text::_('JBS_TPL_DATE_FORMAT_MMM_D_YYYY'));
        $options[] = HTMLHelper::_('select.option', '1', Text::_('JBS_TPL_DATE_FORMAT_MMM_D'));
        $options[] = HTMLHelper::_('select.option', '2', Text::_('JBS_TPL_DATE_FORMAT_M_D_YYYY'));
        $options[] = HTMLHelper::_('select.option', '3', Text::_('JBS_TPL_DATE_FORMAT_M_D'));
        $options[] = HTMLHelper::_('select.option', '4', Text::_('JBS_TPL_DATE_FORMAT_WD_MMMM_D_YYYY'));
        $options[] = HTMLHelper::_('select.option', '5', Text::_('JBS_TPL_DATE_FORMAT_MMMM_D_YYYY'));
        $options[] = HTMLHelper::_('select.option', '6', Text::_('JBS_TPL_DATE_FORMAT_D_MMMM_YYYY'));
        $options[] = HTMLHelper::_('select.option', '7', Text::_('JBS_TPL_DATE_FORMAT_D_M_YYYY'));
        $options[] = HTMLHelper::_('select.option', '8', Text::_('JBS_TPL_DATE_FORMAT_USE_GLOBAL'));
        $options[] = HTMLHelper::_('select.option', '9', Text::_('JBS_TPL_DATE_FORMAT_YYYY_MM_DD'));

        return $options;
    }
}
