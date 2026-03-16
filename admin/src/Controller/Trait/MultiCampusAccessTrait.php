<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Controller\Trait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Multi-campus access level check for FormController::allowEdit().
 *
 * Verifies that non-admin users can only edit records within their
 * authorized Joomla view levels. This enforces campus-level content
 * isolation in multi-campus installations.
 *
 * Usage: `use MultiCampusAccessTrait;` in a FormController subclass
 * and set `protected string $accessTable = '#__bsms_tablename';`
 *
 * @since  10.3.0
 */
trait MultiCampusAccessTrait
{
    /**
     * The database table name to check access levels against.
     * Must be set by the using class.
     *
     * @var    string
     * @since  10.3.0
     */
    // protected string $accessTable = '#__bsms_tablename';

    /**
     * Check if a non-admin user has view-level access to the record.
     *
     * @param   int  $recordId  The record primary key
     *
     * @return  bool|null  False if denied, null if no restriction applies (caller should continue)
     *
     * @since   10.3.0
     */
    protected function checkRecordAccessLevel(int $recordId): ?bool
    {
        if ($recordId <= 0 || !isset($this->accessTable)) {
            return null;
        }

        $user = Factory::getApplication()->getIdentity();

        if ($user->authorise('core.admin')) {
            return null;
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('access'))
            ->from($db->quoteName($this->accessTable))
            ->where($db->quoteName('id') . ' = :rid')
            ->bind(':rid', $recordId, ParameterType::INTEGER);
        $db->setQuery($query);
        $access = (int) $db->loadResult();

        if ($access && !\in_array($access, $user->getAuthorisedViewLevels())) {
            return false;
        }

        return null;
    }
}
