<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Model class for Latest Study
 *
 * @package  Proclaim.Site
 * @since    7.1.0
 */
class CwmlatestModel extends BaseDatabaseModel
{
    /**
     * Get the ID of the most recently published study
     *
     * @return int|null The ID of the latest study, or null if none found
     *
     * @since 7.1.0
     */
    public function getLatestStudyId(): ?int
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $query->select('id')
            ->from($db->quoteName('#__bsms_studies'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('studydate') . ' DESC')
            ->setLimit(1);

        $db->setQuery($query);

        $result = $db->loadResult();

        return $result !== null ? (int) $result : null;
    }
}
