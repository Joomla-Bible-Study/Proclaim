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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Model class for Latest Study
 *
 * @package  Proclaim.Site
 * @since    7.1.0
 */
class CwmlatestModel extends BaseDatabaseModel
{
    /**
     * Get the slug (id:alias) of the most recently published study.
     *
     * Returns the SEF-friendly "id:alias" form so the redirect lands on a
     * slugged URL; the cwmsermon view extracts the leading integer id anyway.
     *
     * @return string|null The "id:alias" slug of the latest study, or null if none found
     *
     * @since 10.3.3
     */
    public function getLatestStudySlug(): ?string
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery();

        $published = 1;
        $query->select(
            'CASE WHEN CHAR_LENGTH(' . $db->quoteName('alias') . ') THEN CONCAT_WS('
            . $db->quote(':') . ', ' . $db->quoteName('id') . ', ' . $db->quoteName('alias')
            . ') ELSE ' . $db->quoteName('id') . ' END AS ' . $db->quoteName('slug')
        )
            ->from($db->quoteName('#__bsms_studies'))
            ->where($db->quoteName('published') . ' = :published')
            ->bind(':published', $published, ParameterType::INTEGER)
            ->order($db->quoteName('studydate') . ' DESC')
            ->setLimit(1);

        $db->setQuery($query);

        $result = $db->loadResult();

        return $result !== null ? (string) $result : null;
    }
}
