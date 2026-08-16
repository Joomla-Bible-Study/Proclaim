--
-- Analytics: add series_id to raw events and monthly aggregate tables.
-- Enables tracking visits to series pages directly, and attributes all
-- message-level events (plays, downloads, page views) to their series.
--
-- One clause per statement. MysqlChangeItem reads a statement's meaning from its
-- third and fourth words only, so a compound ALTER is checked and repaired by its
-- first clause alone; everything after it is invisible to System > Database and
-- to the schema fix a restore runs. These were one ALTER each (#1664).
--

ALTER TABLE `#__bsms_analytics_events`
    ADD COLUMN `series_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'FK #__bsms_series' AFTER `study_id` /** CAN FAIL **/;

ALTER TABLE `#__bsms_analytics_events`
    ADD KEY `idx_series_created` (`series_id`, `created`) /** CAN FAIL **/;

ALTER TABLE `#__bsms_analytics_monthly`
    ADD COLUMN `series_id` INT UNSIGNED NULL DEFAULT NULL AFTER `study_id` /** CAN FAIL **/;

-- The uq_aggregate rework that used to sit here — DROP INDEX followed by a wider
-- ADD UNIQUE KEY over the same name — is now done by proclaim.script.php's
-- reconcileAnalyticsAggregateIndex(). It cannot be expressed here safely:
--
--   * MySQL has no DROP INDEX IF EXISTS, so a database missing the key stops the
--     statement with error 1091, and parseSchemaUpdates() aborts the whole update.
--   * Split into its own statement, the DROP becomes a check item that expects
--     zero matching indexes forever — and the key is re-added on the next line, so
--     it would read as unapplied on every check and be re-dropped by every fix.
--
-- The PHP reconcile has neither problem, and unlike any SQL formulation it can
-- also repair a key that exists with the wrong columns.
