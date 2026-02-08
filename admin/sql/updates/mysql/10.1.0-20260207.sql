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

-- Study-Topics junction: Covering composite for dual-direction lookups
ALTER TABLE `#__bsms_studytopics` ADD KEY `idx_study_topic` (`study_id`, `topic_id`);
