<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Lib;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Reads and writes the import-set manifest (`#__bsms_import_manifest`).
 *
 * A tagged import (e.g. an opt-in demo content set) records every row
 * and file it creates here as it creates it, so the set can be found again
 * and cleanly removed later without touching content the site owner created
 * themselves.
 *
 * @package  Proclaim.Admin
 * @since    __DEPLOY_VERSION__
 */
class Cwmimportmanifest
{
    /**
     * Has anything already been recorded under this tag?
     *
     * The importer refuses to run a second time under the same tag rather
     * than silently doubling the set — call this before importing anything.
     *
     * @param   string  $tag  The import tag to check.
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function exists(string $tag): bool
    {
        $db = self::db();

        $count = $db->setQuery(
            $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_import_manifest'))
                ->where($db->quoteName('import_tag') . ' = :tag')
                ->bind(':tag', $tag, ParameterType::STRING)
        )->loadResult();

        return (int) $count > 0;
    }

    /**
     * Record a database row created by a tagged import.
     *
     * @param   string  $tag        The import tag.
     * @param   string  $tableName  Unprefixed table name (e.g. `#__bsms_teachers`).
     * @param   int     $rowId      The new row's primary key.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function recordRow(string $tag, string $tableName, int $rowId): void
    {
        // 'created' is deliberately omitted — the column defaults to
        // CURRENT_TIMESTAMP, and Factory::getDate() needs a fully-booted
        // application's language to format safely (this class is written to
        // need neither, so callers like this repository's own bare test
        // harness can use it directly).
        $row = (object) [
            'import_tag'  => $tag,
            'entity_type' => 'row',
            'table_name'  => $tableName,
            'row_id'      => $rowId,
            'file_path'   => null,
        ];

        self::db()->insertObject('#__bsms_import_manifest', $row);
    }

    /**
     * Record a file created by a tagged import.
     *
     * @param   string  $tag       The import tag.
     * @param   string  $filePath  Site-root-relative path to the file.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function recordFile(string $tag, string $filePath): void
    {
        $row = (object) [
            'import_tag'  => $tag,
            'entity_type' => 'file',
            'table_name'  => null,
            'row_id'      => null,
            'file_path'   => $filePath,
        ];

        self::db()->insertObject('#__bsms_import_manifest', $row);
    }

    /**
     * Every row recorded under a tag, grouped by table.
     *
     * @param   string  $tag  The import tag.
     *
     * @return  array<string, int[]>  Table name => row ids.
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function rowsForTag(string $tag): array
    {
        $db = self::db();

        $results = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName(['table_name', 'row_id']))
                ->from($db->quoteName('#__bsms_import_manifest'))
                ->where($db->quoteName('import_tag') . ' = :tag')
                ->where($db->quoteName('entity_type') . ' = ' . $db->quote('row'))
                ->bind(':tag', $tag, ParameterType::STRING)
        )->loadObjectList();

        $byTable = [];

        foreach ($results as $result) {
            $byTable[$result->table_name][] = (int) $result->row_id;
        }

        return $byTable;
    }

    /**
     * Every file path recorded under a tag.
     *
     * @param   string  $tag  The import tag.
     *
     * @return  string[]
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function filesForTag(string $tag): array
    {
        $db = self::db();

        return $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName('file_path'))
                ->from($db->quoteName('#__bsms_import_manifest'))
                ->where($db->quoteName('import_tag') . ' = :tag')
                ->where($db->quoteName('entity_type') . ' = ' . $db->quote('file'))
                ->bind(':tag', $tag, ParameterType::STRING)
        )->loadColumn();
    }

    /**
     * Remove one row's manifest entry — call once the row itself is
     * actually gone (#2174), not before, so a failure partway through a
     * removal still agrees with what the manifest says exists.
     *
     * @param   string  $tag        The import tag.
     * @param   string  $tableName  Unprefixed table name, as recorded by {@see recordRow()}.
     * @param   int     $rowId      The row's primary key.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function clearRow(string $tag, string $tableName, int $rowId): void
    {
        $db = self::db();

        $db->setQuery(
            $db->createQuery()
                ->delete($db->quoteName('#__bsms_import_manifest'))
                ->where($db->quoteName('import_tag') . ' = :tag')
                ->where($db->quoteName('entity_type') . ' = ' . $db->quote('row'))
                ->where($db->quoteName('table_name') . ' = :table')
                ->where($db->quoteName('row_id') . ' = :id')
                ->bind(':tag', $tag, ParameterType::STRING)
                ->bind(':table', $tableName, ParameterType::STRING)
                ->bind(':id', $rowId, ParameterType::INTEGER)
        )->execute();
    }

    /**
     * Remove one file's manifest entry — call once the file itself is
     * actually gone (#2174), not before.
     *
     * @param   string  $tag       The import tag.
     * @param   string  $filePath  As recorded by {@see recordFile()}.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function clearFile(string $tag, string $filePath): void
    {
        $db = self::db();

        $db->setQuery(
            $db->createQuery()
                ->delete($db->quoteName('#__bsms_import_manifest'))
                ->where($db->quoteName('import_tag') . ' = :tag')
                ->where($db->quoteName('entity_type') . ' = ' . $db->quote('file'))
                ->where($db->quoteName('file_path') . ' = :path')
                ->bind(':tag', $tag, ParameterType::STRING)
                ->bind(':path', $filePath, ParameterType::STRING)
        )->execute();
    }

    /**
     * @return  DatabaseInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    private static function db(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }
}
