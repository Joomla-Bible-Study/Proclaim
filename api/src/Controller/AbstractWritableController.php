<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Api\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmactionlogHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmlogHelper;
use Joomla\CMS\MVC\Controller\ApiController;

/**
 * Base controller for resources the API exposes for writing.
 *
 * Every create, update and delete made over HTTP is recorded in Joomla's Action
 * Logs. Without this the API is a blind spot: the same edit made in the admin UI
 * produces an audit entry, while one made through the API leaves no trace of who
 * changed what. That matters most for the API precisely because it is remote and
 * key-authenticated rather than performed by someone at a browser.
 *
 * Entries use the same COM_PROCLAIM_ACTION_LOG_ITEM_* keys as the admin
 * controllers, so there is one taxonomy rather than a parallel API one. Origin is
 * not passed by the caller: CwmactionlogHelper derives it from the running
 * application, so a change reads "… (via API)" or "… (via admin)" from a single
 * shared message and no call site can mislabel itself.
 *
 * Exactly one entry per change. Successful writes are recorded here and nowhere
 * else — operational logging for the API lives in {@see CwmlogHelper}, which
 * deliberately does not repeat content changes.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class AbstractWritableController extends ApiController
{
    /**
     * Entity type for the action log, e.g. 'topic'. Must have a matching
     * COM_PROCLAIM_ACTION_LOG_TYPE_* language key. Empty disables logging.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $logType = '';

    /**
     * Column holding the record's display title. Empty where the entity has none
     * (media files, for instance) — the log then identifies the record by id.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $logTitleField = '';

    /**
     * Save a record, then record the change in the action log.
     *
     * Both add() and edit() funnel through here, so one override covers create
     * and update. A null $recordKey means a create. The parent throws on failure,
     * so reaching the log call means the write actually landed.
     *
     * @param   integer|null  $recordKey  Key of the record being updated, null on create.
     *
     * @return  integer  The record id.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function save($recordKey = null)
    {
        $id = parent::save($recordKey);

        $this->logApiWrite($recordKey === null ? 'ADDED' : 'UPDATED', (int) $id);

        return $id;
    }

    /**
     * Delete a record, then record the deletion.
     *
     * The title is read before the delete, because afterwards there is no row to
     * read it from — a log entry saying only "deleted #7" is of little use during
     * an audit.
     *
     * @param   integer|null  $id  Record id, or null to read it from the request.
     *
     * @return  mixed
     *
     * @since   __DEPLOY_VERSION__
     */
    public function delete($id = null)
    {
        $recordId = (int) ($id ?? $this->input->get('id', 0, 'int'));
        $title    = $this->recordTitle($recordId);

        $result = parent::delete($id);

        $this->logApiWrite('DELETED', $recordId, $title);

        return $result;
    }

    /**
     * Write one action-log entry for an API change.
     *
     * @param   string       $verb   ADDED, UPDATED or DELETED.
     * @param   integer      $id     Record id.
     * @param   string|null  $title  Pre-read title, or null to read it now.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function logApiWrite(string $verb, int $id, ?string $title = null): void
    {
        if ($this->logType === '' || $id <= 0) {
            return;
        }

        $title ??= $this->recordTitle($id);

        // Same key family the admin controllers use. Origin is not passed in —
        // CwmactionlogHelper derives it from the running application and
        // substitutes {origin}, so an API change reads "… (via API)" and an admin
        // change "… (via admin)" from one shared message.
        CwmactionlogHelper::log(
            'COM_PROCLAIM_ACTION_LOG_ITEM_' . $verb,
            $title,
            $this->logType,
            $id
        );
    }

    /**
     * Best-effort display title for a record.
     *
     * Never throws: a failure to read a title must not turn a successful write
     * into an error response.
     *
     * @param   integer  $id  Record id.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function recordTitle(int $id): string
    {
        $fallback = '#' . $id;

        if ($this->logTitleField === '' || $id <= 0) {
            return $fallback;
        }

        try {
            $model = $this->getModel($this->logType);

            if (!$model) {
                return $fallback;
            }

            $table = $model->getTable();

            if (!$table->load($id)) {
                return $fallback;
            }

            $value = $table->{$this->logTitleField} ?? '';

            return $value === '' ? $fallback : (string) $value;
        } catch (\Throwable $e) {
            CwmlogHelper::debug(
                'Could not read title for ' . $this->logType . ' #' . $id . ': ' . $e->getMessage(),
                CwmlogHelper::CATEGORY_API
            );

            return $fallback;
        }
    }
}
