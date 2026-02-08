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
