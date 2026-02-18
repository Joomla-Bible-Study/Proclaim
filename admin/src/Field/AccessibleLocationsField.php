<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * @since      10.1.0
 */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Location dropdown filtered to locations accessible to the current user.
 *
 * Super admins see all published locations.
 * Other users see only the locations returned by CwmlocationHelper::getUserLocations().
 * If the currently-saved location is inaccessible it is shown as a disabled option
 * so the record can still be saved without silently stripping the value.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class AccessibleLocationsField extends ListField
{
    /**
     * @var string
     * @since 10.1.0
     */
    protected $type = 'AccessibleLocations';

    /**
     * Build the option list filtered to the current user's accessible locations.
     *
     * @return  object[]  Array of stdClass option objects.
     *
     * @since   10.1.0
     */
    #[\Override]
    protected function getOptions(): array
    {
        $user    = Factory::getApplication()->getIdentity();
        $isAdmin = $user->authorise('core.admin');

        // Current value so we can keep it visible even if inaccessible
        $currentId = (int) $this->value;

        // Locations this user is allowed to see
        $allowedIds = $isAdmin ? [] : CwmlocationHelper::getUserAccessibleLocationsForEdit(0, $currentId);

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('location_text')])
            ->from($db->quoteName('#__bsms_locations'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('location_text'));

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        // Build option list
        $options = array_merge(
            parent::getOptions(),
            [HTMLHelper::_('select.option', '', Text::_('JBS_CMN_SELECT_LOCATION'))]
        );

        foreach ($rows as $row) {
            $id = (int) $row->id;

            // Skip locations the user cannot access (unless it is the current value)
            if (!$isAdmin && !empty($allowedIds) && !\in_array($id, $allowedIds, true)) {
                if ($id !== $currentId) {
                    continue;
                }

                // Show inaccessible current value as disabled
                $opt           = HTMLHelper::_('select.option', $id, $row->location_text);
                $opt->disable  = true;
                $options[]     = $opt;
                continue;
            }

            $options[] = HTMLHelper::_('select.option', $id, $row->location_text);
        }

        return $options;
    }
}
