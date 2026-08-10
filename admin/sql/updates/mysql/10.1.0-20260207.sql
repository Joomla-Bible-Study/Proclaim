--
-- Register Proclaim entity types with Joomla Action Logs
--
INSERT INTO `#__action_log_config` (`type_title`, `type_alias`, `id_holder`, `title_holder`, `table_name`, `text_prefix`)
VALUES
    ('message', 'com_proclaim.message', 'id', 'studytitle', '#__bsms_studies', 'COM_PROCLAIM'),
    ('teacher', 'com_proclaim.teacher', 'id', 'teachername', '#__bsms_teachers', 'COM_PROCLAIM'),
    ('server', 'com_proclaim.server', 'id', 'server_name', '#__bsms_servers', 'COM_PROCLAIM'),
    ('podcast', 'com_proclaim.podcast', 'id', 'title', '#__bsms_podcast', 'COM_PROCLAIM'),
    ('template', 'com_proclaim.template', 'id', 'title', '#__bsms_templates', 'COM_PROCLAIM');

-- Register Proclaim extension with Joomla Action Logs
INSERT IGNORE INTO `#__action_logs_extensions` (`extension`) VALUES ('com_proclaim');

--
-- Performance indexes based on query pattern analysis
-- Priority 1: High-impact composite indexes for core query patterns
--

-- Studies: Core list filtering (published + access + series + sort by date)
ALTER TABLE `#__bsms_studies` ADD KEY `idx_published_access_series` (`published`, `access`, `series_id`, `studydate`);

-- Studies: Teacher filter pattern
ALTER TABLE `#__bsms_studies` ADD KEY `idx_teacher_published` (`teacher_id`, `published`, `studydate`);

-- Studies: Location filter pattern
ALTER TABLE `#__bsms_studies` ADD KEY `idx_location_published` (`location_id`, `published`);

-- Studies: Temporal publish_up/down date range filtering
ALTER TABLE `#__bsms_studies` ADD KEY `idx_published_dates` (`published`, `publish_up`, `publish_down`);

-- Studies: Message type filter pattern
ALTER TABLE `#__bsms_studies` ADD KEY `idx_messagetype_published` (`messagetype`, `published`);

-- Studies: Book number filter pattern
ALTER TABLE `#__bsms_studies` ADD KEY `idx_booknumber_published` (`booknumber`, `published`);

-- Studies: Language filter for multilingual sites
ALTER TABLE `#__bsms_studies` ADD KEY `idx_language_published` (`language`, `published`);

-- Comments: Study + published composite (study_id was NOT indexed)
ALTER TABLE `#__bsms_comments` ADD KEY `idx_study_published` (`study_id`, `published`, `comment_date`);

-- Media files: Study + published aggregation covering index
ALTER TABLE `#__bsms_mediafiles` ADD KEY `idx_study_published` (`study_id`, `published`, `createdate`);

-- Media files: Podcast filter pattern
ALTER TABLE `#__bsms_mediafiles` ADD KEY `idx_podcast_published` (`podcast_id`, `published`);

--
-- Priority 2: Published + access composites for entity tables
--

-- Series: Published + access composite for access control queries
ALTER TABLE `#__bsms_series` ADD KEY `idx_published_access` (`published`, `access`);

-- Series: Teacher + published composite for teacher detail pages
ALTER TABLE `#__bsms_series` ADD KEY `idx_teacher_published` (`teacher`, `published`);

-- Teachers: Published + access composite
ALTER TABLE `#__bsms_teachers` ADD KEY `idx_published_access` (`published`, `access`);

-- Topics: Published + access composite
ALTER TABLE `#__bsms_topics` ADD KEY `idx_published_access` (`published`, `access`);

-- Locations: Published + access composite
ALTER TABLE `#__bsms_locations` ADD KEY `idx_published_access` (`published`, `access`);

-- Study-Topics junction: covering composite for dual-direction lookups.
--
-- The statement that created idx_study_topic is deliberately gone. 10.5.6
-- replaced it with the UNIQUE uq_study_topic over the same pair and dropped
-- this one as redundant, so on every site that has taken that update the index
-- named here no longer exists.
--
-- Joomla's schema check reads every historical file in this folder and tests it
-- against the CURRENT schema — it has no notion of a change being superseded.
-- Left in place, this line reported a permanent error in System → Maintenance →
-- Database on every upgraded site, for an index the component removed on
-- purpose. Removing the statement removes the check with it.
--
-- Safe to remove rather than merely comment out: a site replaying history from
-- an earlier version now skips straight to uq_study_topic, which covers the
-- same columns, and install.mysql.utf8.sql never created idx_study_topic
-- either.
