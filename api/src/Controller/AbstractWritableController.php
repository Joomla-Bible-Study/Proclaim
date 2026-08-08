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
use Joomla\CMS\Access\Exception\NotAllowed;
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
 * @since  10.3.4
 */
abstract class AbstractWritableController extends ApiController
{
    /**
     * Params key marking a record the API force-unpublished pending review.
     *
     * Read by the control panel's review count, and cleared when the record is
     * published.
     *
     * @since 10.5.6
     */
    public const string PENDING_REVIEW_KEY = 'pending_review';

    /**
     * Entity type for the action log, e.g. 'topic'. Must have a matching
     * COM_PROCLAIM_ACTION_LOG_TYPE_* language key. Empty disables logging.
     *
     * @var    string
     * @since  10.3.4
     */
    protected $logType = '';

    /**
     * Column holding the record's display title. Empty where the entity has none
     * (media files, for instance) — the log then identifies the record by id.
     *
     * @var    string
     * @since  10.3.4
     */
    protected $logTitleField = '';

    /**
     * Save a record, then record the change in the action log.
     *
     * Both add() and edit() funnel through here, so one override covers create
     * and update. A null $recordKey means a create. The parent throws on failure,
     * so reaching the log call means the write actually landed.
     *
     * @param   int|null  $recordKey  Key of the record being updated, null on create.
     *
     * @return  int  The record id.
     *
     * @since   10.3.4
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
     * @param   int|null  $id  Record id, or null to read it from the request.
     *
     * @return  mixed
     *
     * @since   10.3.4
     */
    public function delete($id = null)
    {
        $recordId = (int) ($id ?? $this->input->get('id', 0, 'int'));
        $title    = $this->recordTitle($recordId);

        try {
            $result = parent::delete($id);
        } catch (NotAllowed $e) {
            $this->logDenied('delete', $recordId);

            throw $e;
        }

        $this->logApiWrite('DELETED', $recordId, $title);

        return $result;
    }

    /**
     * Create a record, recording the attempt if permission is refused.
     *
     * @return  mixed
     *
     * @since   10.3.4
     */
    public function add()
    {
        try {
            return parent::add();
        } catch (NotAllowed $e) {
            $this->logDenied('create', 0);

            throw $e;
        }
    }

    /**
     * Update a record, recording the attempt if permission is refused.
     *
     * @return  mixed
     *
     * @since   10.3.4
     */
    public function edit()
    {
        try {
            return parent::edit();
        } catch (NotAllowed $e) {
            $this->logDenied('update', $this->input->getInt('id', 0));

            throw $e;
        }
    }

    /**
     * Record an authenticated caller being refused an action.
     *
     * This is deliberately narrow. Failed authentication is NOT logged — an
     * anonymous caller with a bad key is unbounded noise and the web server's
     * access log already has it. What is worth recording is the opposite case: a
     * caller who authenticated successfully and then attempted something their
     * account is not permitted to do. That has a known identity, is bounded by the
     * number of real integrations, and is a genuine signal — an over-scoped
     * client, a buggy one, or a key being used for something it should not be.
     *
     * @param   string   $verb  create, update or delete.
     * @param   int  $id    Record id, 0 when creating.
     *
     * @return  void
     *
     * @since   10.3.4
     */
    private function logDenied(string $verb, int $id): void
    {
        try {
            $user   = $this->app->getIdentity();
            $target = $id > 0 ? $this->contentType . ' #' . $id : $this->contentType;

            CwmlogHelper::warning(
                \sprintf(
                    'Authenticated user %s (#%d) was refused permission to %s %s from %s',
                    $user?->username ?? 'unknown',
                    (int) ($user?->id ?? 0),
                    $verb,
                    $target,
                    $this->app->getInput()->server->getString('REMOTE_ADDR', 'unknown address')
                ),
                CwmlogHelper::CATEGORY_API
            );
        } catch (\Throwable) {
            // Logging must never mask the 403 the caller needs to receive.
        }
    }

    /**
     * Write one action-log entry for an API change.
     *
     * @param   string       $verb   ADDED, UPDATED or DELETED.
     * @param   int      $id     Record id.
     * @param   string|null  $title  Pre-read title, or null to read it now.
     *
     * @return  void
     *
     * @since   10.3.4
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
     * Remove fields that should not be set directly via API.
     *
     * Prevents mass assignment of ownership, internal state, and
     * system-managed fields. Published state requires core.edit.state.
     * Subclasses with extra protected fields of their own (Locations'
     * email_to/user_id/contact_id, Media's podcast_id) call this first and
     * layer their own unset()/normalization on top.
     *
     * @param   array  $data  The incoming data
     *
     * @return  array  The cleaned data
     *
     * @since   10.5.6
     */
    protected function stripProtectedFields(array $data): array
    {
        $user = $this->app->getIdentity();

        // Never allow setting internal system fields via API
        unset(
            $data['asset_id'],
            $data['checked_out'],
            $data['checked_out_time'],
            $data['modified_by'],
        );

        // Only admins can set created_by (creating on behalf of someone)
        if (isset($data['created_by']) && !$user->authorise('core.admin', 'com_proclaim')) {
            unset($data['created_by']);
        }

        // Restrict published state — users without core.edit.state default to
        // unpublished, and the record is marked as awaiting review.
        //
        // The mark is what separates a submission from an administrator
        // deliberately unpublishing something. Both leave published = 0, so
        // counting that alone reports drafts and retired sermons as pending
        // review and never reaches zero on a site that unpublishes routinely.
        if (!$user->authorise('core.edit.state', 'com_proclaim')) {
            $data['published'] = 0;
            $data['params']    = self::markPendingReview($data['params'] ?? null);
        }

        return $data;
    }

    /**
     * Best-effort display title for a record.
     *
     * Never throws: a failure to read a title must not turn a successful write
     * into an error response.
     *
     * @param   int  $id  Record id.
     *
     * @return  string
     *
     * @since   10.3.4
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

    /**
     * Flag a params payload as awaiting editorial review.
     *
     * Accepts the shapes params arrives in from the API -- a JSON string, an
     * array, or absent -- and returns an array, which the models bind and
     * Registry encodes.
     *
     * @param   mixed  $params  Existing params, if any
     *
     * @return  array
     *
     * @since   10.5.6
     */
    protected static function markPendingReview(mixed $params): array
    {
        if (\is_string($params) && $params !== '') {
            try {
                $params = json_decode($params, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $params = [];
            }
        }

        if (!\is_array($params)) {
            $params = [];
        }

        $params[self::PENDING_REVIEW_KEY] = 1;

        return $params;
    }
}
