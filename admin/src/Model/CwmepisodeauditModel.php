<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmepisodenumberHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Duplicate episode number audit report model.
 *
 * @package  Proclaim.Admin
 * @since    10.5.6
 */
class CwmepisodeauditModel extends BaseDatabaseModel
{
    /**
     * Every series with more than one message sharing the same episode number.
     *
     * @return  array
     *
     * @since   10.5.6
     */
    public function getDuplicates(): array
    {
        return CwmepisodenumberHelper::findAllDuplicates($this->getDatabase());
    }
}
