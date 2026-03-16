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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\CalendarField as JoomlaCalendarField;

/**
 * Calendar field that hides seconds from the displayed value.
 *
 * The DB stores DATETIME with seconds, and Joomla's CalendarField always
 * renders them. The calendar JS also rewrites the input value on init
 * using its format string (which includes %S). This subclass intercepts
 * the input element's value setter via Object.defineProperty so that
 * every write — PHP, JS init, date pick — has seconds stripped.
 *
 * Pair with timeformat="12" in XML for 12-hour AM/PM display in the
 * calendar popup.
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
     * Get the field input markup.
     *
     * Delegates to the core CalendarField, adds a data marker, and
     * registers JS that intercepts the value property setter on the
     * input element to strip :SS from every value assignment.
     *
     * @return  string  The field input markup.
     *
     * @since  10.1.0
     */
    #[\Override]
    protected function getInput(): string
    {
        $html = parent::getInput();

        // Mark the wrapper so JS can target these fields
        $html = str_replace(
            'class="field-calendar"',
            'class="field-calendar" data-cwm-no-seconds="1"',
            $html
        );

        // Register the value-interceptor JS once per page (external file,
        // no PHP data needed — the script targets [data-cwm-no-seconds] inputs)
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_proclaim');
        $wa->useScript('com_proclaim.cwm-calendar-noseconds');

        return $html;
    }

    /**
     * Filter the input value — re-append seconds if stripped by the JS.
     *
     * The cwm-calendar-noseconds JS removes :SS from the display value.
     * Joomla's CalendarField::filter() expects seconds when showtime is
     * enabled. Re-append :00 before passing to parent::filter().
     *
     * @param   mixed                $value  The input value
     * @param   string               $group  The field group
     * @param   ?\Joomla\Registry\Registry  $input  Input registry
     *
     * @return  mixed  The filtered value
     *
     * @since   10.3.0
     */
    public function filter($value, $group = null, ?\Joomla\Registry\Registry $input = null)
    {
        // Re-append :00 seconds if the value matches Y-m-d H:i without seconds
        if (\is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }

        return parent::filter($value, $group, $input);
    }
}
