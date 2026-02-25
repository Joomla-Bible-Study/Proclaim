-- Ensure all Proclaim tables have PRIMARY KEY constraints.
-- Sites upgraded from older versions (v7/v8/v9) may lack PKs because
-- the install SQL uses CREATE TABLE IF NOT EXISTS which skips existing tables.
-- Joomla's schema updater catches errors, so these statements are safe
-- on sites that already have the correct PKs.

-- Core entity tables (id AUTO_INCREMENT)
ALTER TABLE `#__bsms_admin` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_books` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_comments` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_locations` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_mediafiles` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_message_type` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_podcast` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_series` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_servers` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_studies` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_studytopics` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_teachers` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_templatecode` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_templates` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_topics` ADD PRIMARY KEY (`id`);

-- Junction / relationship tables (id AUTO_INCREMENT)
ALTER TABLE `#__bsms_study_scriptures` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_study_teachers` ADD PRIMARY KEY (`id`);

-- Analytics tables (id AUTO_INCREMENT)
ALTER TABLE `#__bsms_analytics_events` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_analytics_monthly` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_platform_stats` ADD PRIMARY KEY (`id`);

-- Reference / cache tables
ALTER TABLE `#__bsms_bible_translations` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_bible_verses` ADD PRIMARY KEY (`id`);
ALTER TABLE `#__bsms_scripture_cache` ADD PRIMARY KEY (`id`);

-- Non-integer PK tables
ALTER TABLE `#__bsms_timeset` ADD PRIMARY KEY (`timeset`);
ALTER TABLE `#__bsms_storage` ADD PRIMARY KEY (`key`);
