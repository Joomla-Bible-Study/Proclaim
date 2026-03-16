<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\Field\CalendarField as JoomlaCalendarField;

/**
 * Calendar field subclass for Proclaim date fields.
 *
 * Extends Joomla's CalendarField to handle the case where seconds
 * may be absent from the submitted value (e.g., browser autofill or
 * copy-paste without seconds). Re-appends :00 before validation.
 *
 * Usage in XML: type="cwmcalendar" (with addfieldprefix set)
 *
 * @since  10.1.0
 */
class CwmcalendarField extends JoomlaCalendarField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  10.1.0
     */
    protected $type = 'Cwmcalendar';

    /**
     * Filter the input value — re-append seconds if missing.
     *
     * Joomla's CalendarField::filter() expects seconds when showtime is
     * enabled. If the submitted value has no seconds (H:i without :ss),
     * append :00 before passing to parent::filter().
     *
     * @param   mixed                       $value  The input value
     * @param   string                      $group  The field group
     * @param   ?\Joomla\Registry\Registry  $input  Input registry
     *
     * @return  mixed  The filtered value
     *
     * @since   10.3.0
     */
    public function filter($value, $group = null, ?\Joomla\Registry\Registry $input = null)
    {
        if (\is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }

        return parent::filter($value, $group, $input);
    }
}
