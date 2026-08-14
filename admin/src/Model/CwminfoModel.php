<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Model backing the `/v1/proclaim/info` API resource.
 *
 * Not a database entity — a singleton item exposing metadata a caller cannot
 * otherwise reliably obtain over the API. ⚠️ The DB schema version is not a
 * substitute: it only advances on releases that ship DB changes, so external
 * tooling using it to guess the installed release gets stale answers.
 *
 * @since  10.5.6
 */
class CwminfoModel extends BaseDatabaseModel
{
    /**
     * Get the singleton info item.
     *
     * @param   ?int  $pk  Ignored — there is only one info item.
     *
     * @return  \stdClass
     *
     * @since   10.5.6
     */
    public function getItem($pk = null): \stdClass
    {
        $item          = new \stdClass();
        $item->id      = 1;
        $item->version = CwmproclaimHelper::getVersion();

        return $item;
    }
}
