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

use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Removes a tagged import (#2145, #2174) by walking the #2172 manifest.
 *
 * Split into a read-only {@see plan()} and a mutating {@see execute()} on
 * purpose. `plan()` decides, for every manifest row, whether it is safe to
 * delete or must be kept, and why — that decision needs no models, no
 * plugins and no database writes, so it is fully testable without the
 * environment `execute()` needs. `execute()` trusts a plan it did not
 * necessarily compute itself, so a caller can show the plan to an admin
 * before acting on it.
 *
 * A row is kept, not deleted, when either is true:
 * - it has been edited since the import created it (`modified_by` is only
 *   ever set by an UPDATE — see the Model `prepareTable()` methods this
 *   mirrors), or
 * - something outside this import still depends on it (a message crediting
 *   a demo teacher, a series holding a demo teacher, a comment or media
 *   file on a demo message).
 *
 * Deleting a kept row anyway would destroy content the site owner has since
 * made their own, or — for a study with attached media — content
 * `CwmmessageTable::delete()`'s own cascade would take out along with it.
 *
 * @package  Proclaim.Admin
 * @since    __DEPLOY_VERSION__
 */
class Cwmimportremover
{
    /**
     * Table => the model name that owns it, in child-first delete order.
     *
     * @var array<string, string>
     * @since  __DEPLOY_VERSION__
     */
    private const array TABLE_MODELS = [
        '#__bsms_studies'  => 'Cwmmessage',
        '#__bsms_series'   => 'Cwmserie',
        '#__bsms_teachers' => 'Cwmteacher',
    ];

    /**
     * Models whose `canDelete()` requires the record to already be trashed
     * (`published == -2`) — {@see CwmteacherModel::canDelete()},
     * {@see CwmserieModel::canDelete()}. `CwmmessageModel` has no override
     * and uses core's ACL-only default, so it needs no trash step.
     *
     * @var string[]
     * @since  __DEPLOY_VERSION__
     */
    private const array REQUIRES_TRASH_FIRST = ['#__bsms_series', '#__bsms_teachers'];

    /**
     * Overrides `bootComponent('com_proclaim')->getMVCFactory()` — see the
     * identical parameter on {@see Cwmcontentimporter}.
     *
     * @since  __DEPLOY_VERSION__
     */
    private ?MVCFactoryInterface $factory;

    /**
     * @param   ?MVCFactoryInterface  $factory  Null uses the real component in production.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(?MVCFactoryInterface $factory = null)
    {
        $this->factory = $factory;
    }

    /**
     * Decide what removing a tag would do, without doing it.
     *
     * @param   string  $tag  The import tag to plan a removal for.
     *
     * @return  list<array{table: ?string, id: ?int, file: ?string, action: string, reason: ?string}>
     *          `action` is `delete` or `keep`. `table`/`id` are set for rows, `file` for files — never both.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function plan(string $tag): array
    {
        if (!Cwmimportmanifest::exists($tag)) {
            throw new \RuntimeException(\sprintf('No import tagged "%s" exists.', $tag));
        }

        $rows = Cwmimportmanifest::rowsForTag($tag);

        $plan = [];

        // Deletable ids collected as each table is decided, so the next
        // table's "still referenced?" check can tell a reference from a row
        // that is itself about to go from one that is being kept — a kept
        // row's references must count, or removal would strand what it
        // depends on. See #2174's design note on this exact trap.
        $deletableStudyIds = [];

        foreach ($rows['#__bsms_studies'] ?? [] as $id) {
            $reason = $this->wasModified('#__bsms_studies', $id) ?? $this->studyHasExternalContent($id);
            $plan[] = $this->decision('#__bsms_studies', $id, $reason);

            if ($reason === null) {
                $deletableStudyIds[] = $id;
            }
        }

        $deletableSerieIds = [];

        foreach ($rows['#__bsms_series'] ?? [] as $id) {
            $reason = $this->wasModified('#__bsms_series', $id)
                ?? $this->hasExternalReference('#__bsms_studies', 'series_id', $id, 'id', $deletableStudyIds, 'has a message outside this import');
            $plan[] = $this->decision('#__bsms_series', $id, $reason);

            if ($reason === null) {
                $deletableSerieIds[] = $id;
            }
        }

        foreach ($rows['#__bsms_teachers'] ?? [] as $id) {
            $reason = $this->wasModified('#__bsms_teachers', $id)
                ?? $this->hasExternalReference('#__bsms_study_teachers', 'teacher_id', $id, 'study_id', $deletableStudyIds, 'used by a message outside this import')
                ?? $this->hasExternalReference('#__bsms_series', 'teacher', $id, 'id', $deletableSerieIds, 'used by a series outside this import');
            $plan[] = $this->decision('#__bsms_teachers', $id, $reason);
        }

        foreach (Cwmimportmanifest::filesForTag($tag) as $path) {
            $plan[] = ['table' => null, 'id' => null, 'file' => $path, 'action' => 'delete', 'reason' => null];
        }

        return $plan;
    }

    /**
     * Carry out a plan from {@see plan()} (or an equivalent one).
     *
     * @param   string  $tag   The import tag being removed.
     * @param   array   $plan  A plan as returned by {@see plan()}.
     *
     * @return  array{removed: array<string, int>, kept: list<array{table: ?string, id: ?int, file: ?string, reason: ?string}>}
     *
     * @since  __DEPLOY_VERSION__
     */
    public function execute(string $tag, array $plan): array
    {
        $removed = ['#__bsms_studies' => 0, '#__bsms_series' => 0, '#__bsms_teachers' => 0, 'files' => 0];
        $kept    = [];

        foreach ($plan as $entry) {
            if ($entry['action'] === 'keep') {
                $kept[] = $entry;

                continue;
            }

            if ($entry['file'] !== null) {
                if ($this->deleteFile($entry['file'])) {
                    Cwmimportmanifest::clearFile($tag, $entry['file']);
                    $removed['files']++;
                }

                continue;
            }

            $table = $entry['table'];
            $id    = $entry['id'];

            if ($this->deleteRow($table, $id)) {
                Cwmimportmanifest::clearRow($tag, $table, $id);
                $removed[$table]++;
            } else {
                // The model refused (ACL, or a reference this plan could not
                // see) — report it as kept rather than silently leaving a
                // manifest entry for a row that in fact still exists.
                $kept[] = ['table' => $table, 'id' => $id, 'file' => null, 'reason' => 'refused by the model'];
            }
        }

        return ['removed' => $removed, 'kept' => $kept];
    }

    /**
     * Trash then delete one row, verifying by re-querying rather than
     * trusting the model's return value — {@see CwmteacherModel::delete()}
     * silently drops a refused id from its `$pks` batch instead of
     * returning false, so the return value alone cannot be trusted here.
     *
     * @param   string  $table  `#__`-prefixed table.
     * @param   int     $id     The row's id.
     *
     * @return  bool  True once the row is confirmed gone.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function deleteRow(string $table, int $id): bool
    {
        $model = $this->model(self::TABLE_MODELS[$table]);

        if (\in_array($table, self::REQUIRES_TRASH_FIRST, true)) {
            $published = $this->currentPublishedState($table, $id);

            $pks = [$id];
            $model->publish($pks, -2);

            if (!$this->rowExists($table, $id)) {
                return true;
            }

            $pks = [$id];
            $model->delete($pks);

            if ($this->rowExists($table, $id)) {
                // Refused (ACL, or something this plan could not see) —
                // restore state rather than leaving the row trashed.
                $restore = [$id];
                $model->publish($restore, $published);

                return false;
            }

            return true;
        }

        $pks = [$id];
        $model->delete($pks);

        return !$this->rowExists($table, $id);
    }

    /**
     * @param   string  $relativePath  Site-root-relative path, as recorded in the manifest.
     *
     * @return  bool  True if the file is gone (including if it already was).
     *
     * @since  __DEPLOY_VERSION__
     */
    private function deleteFile(string $relativePath): bool
    {
        // The manifest is DB data, and per #2172 restores from ordinary
        // backups — re-validate the path is still confined to Proclaim's
        // image roots before touching the filesystem, rather than trusting
        // a row that could in principle have been tampered with directly.
        $resolved = Cwmthumbnail::resolveWithinAllowedPaths($relativePath);

        if ($resolved === false) {
            return false;
        }

        if (!is_file($resolved)) {
            return true;
        }

        return @unlink($resolved);
    }

    /**
     * @param   string   $table   `#__`-prefixed table.
     * @param   int      $id      The row's id.
     * @param   ?string  $reason  Null means "safe to delete".
     *
     * @return  array{table: string, id: int, file: null, action: string, reason: ?string}
     *
     * @since  __DEPLOY_VERSION__
     */
    private function decision(string $table, int $id, ?string $reason): array
    {
        return [
            'table'  => $table,
            'id'     => $id,
            'file'   => null,
            'action' => $reason === null ? 'delete' : 'keep',
            'reason' => $reason,
        ];
    }

    /**
     * Has this row been edited since the import created it?
     *
     * `modified_by` is only ever set on an UPDATE (see the `prepareTable()`
     * methods on `CwmteacherModel`, `CwmserieModel` and `CwmmessageModel` —
     * all leave it at its insert default, 0, for a new row). A row this
     * importer created and nobody has touched since still reads 0.
     *
     * @param   string  $table  `#__`-prefixed table.
     * @param   int     $id     The row's id.
     *
     * @return  ?string  A reason to keep the row, or null.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function wasModified(string $table, int $id): ?string
    {
        $db = $this->db();

        $modifiedBy = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName('modified_by'))
                ->from($db->quoteName($table))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER)
        )->loadResult();

        return ((int) $modifiedBy !== 0) ? 'edited since import' : null;
    }

    /**
     * A study is not a leaf node — comments and media files point at it,
     * and neither is ever created by this importer, so either one existing
     * means real content depends on this study. `CwmmessageTable::delete()`
     * cascade-deletes attached media files, so missing this check would not
     * just leave a dangling reference, it would destroy the file.
     *
     * @param   int  $studyId  The study's id.
     *
     * @return  ?string  A reason to keep the study, or null.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function studyHasExternalContent(int $studyId): ?string
    {
        if ($this->countWhere('#__bsms_comments', 'study_id', $studyId) > 0) {
            return 'has comments';
        }

        if ($this->countWhere('#__bsms_mediafiles', 'study_id', $studyId) > 0) {
            return 'has media files';
        }

        return null;
    }

    /**
     * Does anything outside the ids already planned for deletion still
     * reference this id?
     *
     * @param   string  $refTable      Table doing the referencing.
     * @param   string  $refColumn     Its FK column.
     * @param   int     $id            The id being checked.
     * @param   string  $refIdColumn   `$refTable`'s own id column.
     * @param   int[]   $deletableIds  Ids of `$refTable`'s own kind already
     *                                 planned for deletion — excluded, since a
     *                                 reference from a row that is itself
     *                                 going away is not an external use.
     * @param   string  $reason        Returned when a reference is found.
     *
     * @return  ?string
     *
     * @since  __DEPLOY_VERSION__
     */
    private function hasExternalReference(
        string $refTable,
        string $refColumn,
        int $id,
        string $refIdColumn,
        array $deletableIds,
        string $reason
    ): ?string {
        $db = $this->db();

        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName($refTable))
            ->where($db->quoteName($refColumn) . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        if ($deletableIds !== []) {
            $query->whereNotIn($db->quoteName($refIdColumn), $deletableIds);
        }

        $count = (int) $db->setQuery($query)->loadResult();

        return $count > 0 ? $reason : null;
    }

    /**
     * @param   string  $table   `#__`-prefixed table.
     * @param   string  $column  Column to match on.
     * @param   int     $value   Value to match.
     *
     * @return  int
     *
     * @since  __DEPLOY_VERSION__
     */
    private function countWhere(string $table, string $column, int $value): int
    {
        $db = $this->db();

        return (int) $db->setQuery(
            $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName($table))
                ->where($db->quoteName($column) . ' = :val')
                ->bind(':val', $value, ParameterType::INTEGER)
        )->loadResult();
    }

    /**
     * @param   string  $table  `#__`-prefixed table.
     * @param   int     $id     The row's id.
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    private function rowExists(string $table, int $id): bool
    {
        return $this->countWhere($table, 'id', $id) > 0;
    }

    /**
     * @param   string  $table  `#__`-prefixed table.
     * @param   int     $id     The row's id.
     *
     * @return  int  Its current `published` value, for restoring on refusal.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function currentPublishedState(string $table, int $id): int
    {
        $db = $this->db();

        return (int) $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName('published'))
                ->from($db->quoteName($table))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER)
        )->loadResult();
    }

    /**
     * @return  DatabaseInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    private function db(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    /**
     * @param   string  $name  Model name, e.g. `Cwmteacher`.
     *
     * @return  AdminModel
     *
     * @since  __DEPLOY_VERSION__
     */
    private function model(string $name): AdminModel
    {
        $factory = $this->factory ?? Factory::getApplication()->bootComponent('com_proclaim')->getMVCFactory();

        $model = $factory->createModel($name, 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof AdminModel) {
            throw new \RuntimeException(\sprintf(
                'Could not create the "%s" model — the component may not be fully booted.',
                $name
            ));
        }

        return $model;
    }
}
