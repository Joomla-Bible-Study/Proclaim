<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Helper for managing the many-to-many relationship between studies and topics
 * via the `#__bsms_studytopics` junction table.
 *
 * Unlike CwmstudyteacherHelper's junction table, `#__bsms_studytopics` has a
 * dedicated Table class (CwmstudytopicsTable) that participates in Joomla's
 * asset tree (custom _getAssetName()/_getAssetParentId(), asset row cleanup
 * on store). New rows are written via that Table so asset handling stays
 * correct; only the bulk delete (which never touched assets, even in the
 * original controller code) uses a raw query.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmstudytopicHelper
{
    /**
     * Look up an existing topic's ID by its text, case-insensitively.
     *
     * @param   string  $text  Topic text to match.
     *
     * @return  int  The matching topic ID, or 0 if none found.
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function findTopicIdByText(string $text): int
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_topics'))
            ->where('LOWER(' . $db->quoteName('topic_text') . ') = LOWER(' . $db->quote($text) . ')')
            ->setLimit(1);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Replace a study's topic associations with the given topic IDs.
     *
     * @param   int    $studyId   Study primary key.
     * @param   int[]  $topicIds  Topic IDs to associate (duplicates and non-positive values are skipped).
     *
     * @return  bool  True if every insert succeeded (a delete failure alone does not fail the call).
     *
     * @throws \Exception
     * @since  __DEPLOY_VERSION__
     */
    public static function saveTopics(int $studyId, array $topicIds): bool
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->delete($db->quoteName('#__bsms_studytopics'))
            ->where($db->quoteName('study_id') . ' = ' . $studyId);
        $db->setQuery($query);

        if (!$db->execute()) {
            Factory::getApplication()->enqueueMessage('error deleting topics', 'error');
        }

        $seen = [];

        foreach ($topicIds as $topicId) {
            $topicId = (int) $topicId;

            if ($topicId <= 0 || isset($seen[$topicId])) {
                continue;
            }

            $seen[$topicId] = true;

            $tagRow = Factory::getApplication()->bootComponent('com_proclaim')
                ->getMVCFactory()->createTable('Cwmstudytopics', 'Administrator');
            $tagRow->study_id = $studyId;
            $tagRow->topic_id = $topicId;

            if (!$tagRow->store()) {
                Factory::getApplication()->enqueueMessage('Error Storing Tags with Message', 'error');

                return false;
            }
        }

        return true;
    }
}
