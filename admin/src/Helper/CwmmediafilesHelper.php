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

use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Shared query logic for the "check delete files" AJAX guard used before
 * deleting media files (directly, by id) or messages (cascading, by
 * study_id) — both need to warn the admin when the delete would also remove
 * physical files on a delete-enabled server.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmmediafilesHelper
{
    /**
     * Find media files, among the given ids, that have a physical file on a
     * delete-enabled server — the set a delete confirmation must warn about.
     *
     * @param   DatabaseInterface  $db        Database driver
     * @param   int[]              $ids       Record ids to check
     * @param   string             $idColumn  Which id the caller's $ids are: 'id' (media
     *                                        file ids, CwmmediafilesController) or
     *                                        'study_id' (message/study ids, CwmmessagesController)
     *
     * @return  array<int, array{id: int, filename: string, message: string, server: string}>
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function findFilesForDelete(DatabaseInterface $db, array $ids, string $idColumn): array
    {
        if (empty($ids)) {
            return [];
        }

        $column = match ($idColumn) {
            'id', 'study_id' => $db->quoteName('mf.' . $idColumn),
            default          => throw new \InvalidArgumentException('Invalid id column: ' . $idColumn),
        };

        $query = $db->createQuery()
            ->select([
                $db->quoteName('mf.id'),
                $db->quoteName('mf.params', 'mf_params'),
                $db->quoteName('sv.server_name'),
                $db->quoteName('sv.params', 'sv_params'),
                $db->quoteName('st.studytitle'),
            ])
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->join('INNER', $db->quoteName('#__bsms_servers', 'sv') . ' ON ' . $db->quoteName('sv.id') . ' = ' . $db->quoteName('mf.server_id'))
            ->join('LEFT', $db->quoteName('#__bsms_studies', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('mf.study_id'))
            ->whereIn($column, $ids)
            ->where($db->quoteName('mf.server_id') . ' > 0');
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $files = [];

        foreach ($rows as $row) {
            $svParams = new Registry($row->sv_params ?: '{}');

            if (!(int) $svParams->get('delete_files', 0)) {
                continue;
            }

            $mfParams = new Registry($row->mf_params ?: '{}');
            $filename = $mfParams->get('filename', '');

            if ($filename === '') {
                continue;
            }

            $files[] = [
                'id'       => (int) $row->id,
                'filename' => $filename,
                'message'  => $row->studytitle ?: '',
                'server'   => $row->server_name ?: '',
            ];
        }

        return $files;
    }
}
