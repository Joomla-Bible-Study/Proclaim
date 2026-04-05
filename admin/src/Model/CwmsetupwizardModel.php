<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmsetupwizardHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Setup Wizard model — applies wizard configuration to admin settings,
 * creates default servers, optional sample content, and scheduled tasks.
 *
 * @since  10.3.0
 */
class CwmsetupwizardModel extends BaseDatabaseModel
{
    /**
     * Load the current admin params as a Registry.
     *
     * @return  Registry
     *
     * @since   10.3.0
     */
    private function loadAdminParams(): Registry
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_admin'))
            ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query, 0, 1);
        $json = $db->loadResult();

        return new Registry($json ?: '{}');
    }

    /**
     * Save admin params back to the database.
     *
     * @param   Registry  $params  The params to save.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private function saveAdminParams(Registry $params): void
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bsms_admin'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
            ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Get the current wizard state for pre-filling steps.
     *
     * @return  array  Current settings relevant to the wizard.
     *
     * @since   10.3.0
     */
    public function getCurrentState(): array
    {
        $params = $this->loadAdminParams();

        return [
            'setup_wizard_complete' => (int) $params->get('setup_wizard_complete', 0),
            'simple_mode'           => (int) $params->get('simple_mode', 0),
            'org_name'              => $params->get('org_name', '')
                ?: Factory::getApplication()->get('sitename', ''),
            'default_bible_version' => $params->get('default_bible_version', 'kjv'),
            'provider_getbible'     => (int) $params->get('provider_getbible', 1),
            'provider_api_bible'    => (int) $params->get('provider_api_bible', 0),
            'uploadpath'            => $params->get('uploadpath', '/images/biblestudy/media/'),
            'analytics_enabled'     => (int) $params->get('analytics_enabled', 1),
        ];
    }

    /**
     * Apply all wizard settings at once.
     *
     * @param   array  $data  Wizard form data from all steps.
     *
     * @return  array  Summary of what was configured.
     *
     * @throws  \RuntimeException  On failure.
     *
     * @since   10.3.0
     */
    public function applyWizard(array $data): array
    {
        $params  = $this->loadAdminParams();
        $preset  = CwmsetupwizardHelper::getPreset($data['ministry_style'] ?? 'simple');
        $summary = [];

        // Step 1: Ministry style
        if ($preset) {
            $params->set('simple_mode', $preset['simple_mode']);
            $summary['ministry_style'] = $data['ministry_style'];
        }

        // Step 2: Essential settings
        if (!empty($data['org_name'])) {
            $params->set('org_name', trim($data['org_name']));
        }

        if (!empty($data['default_bible_version'])) {
            $params->set('default_bible_version', $data['default_bible_version']);
        }

        $params->set('provider_getbible', (int) ($data['provider_getbible'] ?? 1));
        $params->set('provider_api_bible', (int) ($data['provider_api_bible'] ?? 0));

        if (!empty($data['api_bible_api_key'])) {
            $params->set('api_bible_api_key', $data['api_bible_api_key']);
        }

        if (!empty($data['uploadpath'])) {
            $params->set('uploadpath', trim($data['uploadpath']));
        }

        if (!empty($data['metadesc'])) {
            $params->set('metadesc', trim($data['metadesc']));
        }

        // Default placeholder images
        if (!empty($data['use_default_images'])) {
            $params->set('default_study_image', 'media/com_proclaim/images/proclaim.jpg');
            $params->set('default_series_image', 'media/com_proclaim/images/proclaim.jpg');
            $params->set('default_teacher_image', 'media/com_proclaim/images/speaker24.png');
        }

        // Step 3: Simple mode template choice
        if (($data['ministry_style'] ?? '') === 'simple') {
            if (!empty($data['simple_mode_template'])) {
                $params->set('simple_mode_template', $data['simple_mode_template']);
            }

            $params->set('simplegridtextoverlay', (int) ($data['simplegridtextoverlay'] ?? 1));
        }

        // Step 4: Media & integrations
        if (!empty($data['primary_media'])) {
            $serversCreated             = $this->createDefaultServers($data['primary_media'], $data);
            $summary['servers_created'] = $serversCreated;

            // Set the first created server as default
            if (!empty($serversCreated)) {
                $params->set('server', $serversCreated[0]['id']);
            }
        }

        if (!empty($data['enable_ai']) && !empty($data['ai_provider'])) {
            $params->set('ai_provider', $data['ai_provider']);
        }

        $params->set('analytics_enabled', (int) ($data['analytics_enabled'] ?? 1));

        // Create essential defaults (teacher, location) so users can start immediately
        $defaults                = $this->createEssentialDefaults($data, $params);
        $summary['defaults']     = $defaults;

        // Sample content (uses the defaults created above)
        if (!empty($data['create_sample_content'])) {
            $sampleIds                 = $this->createSampleContent($data, $defaults);
            $summary['sample_content'] = $sampleIds;
        }

        // Scheduled tasks — start with preset tasks, add optional ones if toggled
        $tasks = $preset ? $preset['tasks'] : [];

        if (!empty($data['enable_podcast'])) {
            $tasks[] = 'podcast';
        }

        if (!empty($data['enable_backup'])) {
            $tasks[] = 'backup';
        }

        if (!empty($tasks)) {
            $tasksRegistered             = $this->registerScheduledTasks($tasks);
            $summary['tasks_registered'] = $tasksRegistered;
        }

        // Multi-campus: enable location filtering
        if (($data['ministry_style'] ?? '') === 'multi_campus') {
            $params->set('enable_location_filtering', 1);
        }

        // Ensure default template is published and has working params
        $this->ensureDefaultTemplate();

        // Create frontend menu items so the site has entry points
        $menuItems                 = $this->createMenuItems($data);
        $summary['menu_items']     = $menuItems;

        // Update template filter visibility based on ministry style
        $this->updateTemplateFilters($data);

        // Mark wizard complete
        $params->set('setup_wizard_complete', 1);

        // Save everything
        $this->saveAdminParams($params);

        // Clear caches
        parent::cleanCache('com_proclaim');
        parent::cleanCache('mod_proclaim');

        return $summary;
    }

    /**
     * Dismiss the wizard without applying changes.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function dismiss(): void
    {
        $params = $this->loadAdminParams();
        $params->set('setup_wizard_complete', 1);
        $this->saveAdminParams($params);
    }

    /**
     * Create default server entries based on media source selection.
     *
     * Only creates servers that don't already exist (checks by type).
     *
     * @param   string  $primaryMedia  Primary media type: local, youtube, vimeo, direct
     * @param   array   $wizardData    Full wizard data (for API keys, channel IDs, etc.)
     *
     * @return  array  Array of created server info [{id, name, type}, ...]
     *
     * @since   10.3.0
     */
    private function createDefaultServers(string $primaryMedia, array $wizardData = []): array
    {
        $serverMap = [
            'local'   => ['Local Uploads', 'local'],
            'youtube' => ['YouTube', 'youtube'],
            'vimeo'   => ['Vimeo', 'vimeo'],
            'direct'  => ['Direct Links', 'direct'],
        ];

        // Always include local, plus the selected type
        $toCreate = ['local'];

        if ($primaryMedia !== 'local' && isset($serverMap[$primaryMedia])) {
            $toCreate[] = $primaryMedia;
        }

        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $now     = (new Date())->toSql();
        $userId  = Factory::getApplication()->getIdentity()->id ?? 0;
        $created = [];

        foreach ($toCreate as $type) {
            // Check if a server of this type already exists
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_servers'))
                ->where($db->quoteName('type') . ' = ' . $db->quote($type))
                ->where($db->quoteName('published') . ' >= 0');
            $db->setQuery($query);

            if ((int) $db->loadResult() > 0) {
                continue;
            }

            [$name, $serverType] = $serverMap[$type];

            // Build platform-specific server params from wizard data
            $serverParams = [];

            if ($type === 'youtube') {
                if (!empty($wizardData['youtube_api_key'])) {
                    $serverParams['api_key'] = $wizardData['youtube_api_key'];
                }

                if (!empty($wizardData['youtube_channel_id'])) {
                    $serverParams['channel_id'] = $wizardData['youtube_channel_id'];
                }
            }

            if ($type === 'vimeo' && !empty($wizardData['vimeo_access_token'])) {
                $serverParams['access_token'] = $wizardData['vimeo_access_token'];
            }

            $row = (object) [
                'server_name' => $name,
                'type'        => $serverType,
                'published'   => 1,
                'access'      => 1,
                'params'      => json_encode($serverParams) ?: '{}',
                'media'       => '{}',
                'created'     => $now,
                'created_by'  => $userId,
                'modified'    => $now,
                'modified_by' => $userId,
                'checked_out' => 0,
            ];

            $db->insertObject('#__bsms_servers', $row);
            $created[] = [
                'id'   => (int) $db->insertid(),
                'name' => $name,
                'type' => $serverType,
            ];
        }

        return $created;
    }

    /**
     * Create essential default records so the system is immediately usable.
     *
     * Creates a default teacher and (for full/multi-campus) a default location
     * if none exist yet. Sets the admin params to reference these defaults.
     *
     * @param   array     $data    Wizard data
     * @param   Registry  $params  Admin params (modified in place)
     *
     * @return  array  IDs of created defaults.
     *
     * @since   10.3.0
     */
    private function createEssentialDefaults(array $data, Registry $params): array
    {
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $now     = (new Date())->toSql();
        $userId  = Factory::getApplication()->getIdentity()->id ?? 0;
        $ids     = [];
        $orgName = trim($data['org_name'] ?? 'My Church');

        // Create default teacher if none exist
        $query = $db->getQuery(true)->select('COUNT(*)')->from($db->quoteName('#__bsms_teachers'));
        $db->setQuery($query);

        if ((int) $db->loadResult() === 0) {
            $teacher = (object) [
                'teachername' => 'Pastor',
                'alias'       => 'pastor',
                'title'       => '',
                'short'       => '',
                'published'   => 1,
                'access'      => 1,
                'language'    => '*',
                'ordering'    => 1,
                'address'     => '',
                'list_show'   => 1,
                'created'     => $now,
                'created_by'  => $userId,
                'modified'    => $now,
                'modified_by' => $userId,
                'checked_out' => 0,
            ];
            $db->insertObject('#__bsms_teachers', $teacher);
            $ids['teacher_id'] = (int) $db->insertid();
            $params->set('teacher_id', $ids['teacher_id']);
        }

        // Create default location for full_media and multi_campus styles
        $style = $data['ministry_style'] ?? 'simple';

        if ($style !== 'simple') {
            $query = $db->getQuery(true)->select('COUNT(*)')->from($db->quoteName('#__bsms_locations'));
            $db->setQuery($query);

            if ((int) $db->loadResult() === 0) {
                $location = (object) [
                    'location_text' => $orgName,
                    'address'       => trim($data['location_address'] ?? ''),
                    'suburb'        => trim($data['location_city'] ?? ''),
                    'state'         => trim($data['location_state'] ?? ''),
                    'postcode'      => trim($data['location_postcode'] ?? ''),
                    'telephone'     => trim($data['location_phone'] ?? ''),
                    'published'     => 1,
                    'access'        => 1,
                    'language'      => '*',
                    'ordering'      => 1,
                    'params'        => '{}',
                    'metakey'       => '',
                    'metadesc'      => '',
                    'metadata'      => '{}',
                    'xreference'    => '',
                    'sortname1'     => '',
                    'sortname2'     => '',
                    'sortname3'     => '',
                    'mobile'        => '',
                    'webpage'       => '',
                    'created'       => $now,
                    'created_by'    => $userId,
                    'modified'      => $now,
                    'modified_by'   => $userId,
                    'checked_out'   => 0,
                ];
                $db->insertObject('#__bsms_locations', $location);
                $ids['location_id'] = (int) $db->insertid();
                $params->set('location_id', $ids['location_id']);
            }
        }

        return $ids;
    }

    /**
     * Create sample content: one teacher, one series, one message.
     *
     * @param   array  $data      Wizard data (for org_name, defaults)
     * @param   array  $defaults  IDs from createEssentialDefaults
     *
     * @return  array  IDs of created content.
     *
     * @since   10.3.0
     */
    private function createSampleContent(array $data, array $defaults = []): array
    {
        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $now    = (new Date())->toSql();
        $userId = Factory::getApplication()->getIdentity()->id ?? 0;
        $ids    = [];

        // Use the default teacher created by createEssentialDefaults, or find existing
        $teacherId = $defaults['teacher_id'] ?? 0;

        if ($teacherId === 0) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__bsms_teachers'))
                ->where($db->quoteName('published') . ' = 1')
                ->order($db->quoteName('id') . ' ASC');
            $db->setQuery($query, 0, 1);
            $teacherId = (int) $db->loadResult();
        }

        $ids['teacher_id'] = $teacherId;

        // Create sample series
        $series = (object) [
            'series_text'  => 'Sample Series',
            'alias'        => 'sample-series',
            'description'  => '<p>This is a sample series. Edit or replace it with your real series.</p>',
            'published'    => 1,
            'access'       => 1,
            'language'     => '*',
            'ordering'     => 1,
            'pc_show'      => 1,
            'created'      => $now,
            'created_by'   => $userId,
            'modified'     => $now,
            'modified_by'  => $userId,
            'checked_out'  => 0,
            'publish_up'   => $now,
            'publish_down' => '0000-00-00 00:00:00',
        ];
        $db->insertObject('#__bsms_series', $series);
        $ids['series_id'] = (int) $db->insertid();

        // Create sample message using the default teacher
        $locationId = $defaults['location_id'] ?? 0;
        $message    = (object) [
            'studytitle'  => 'Welcome to Proclaim',
            'alias'       => 'welcome-to-proclaim',
            'studydate'   => $now,
            'studyintro'  => '<p>This is a sample message created by the setup wizard to help you see how content appears on your site.</p>',
            'teacher_id'  => $teacherId,
            'series_id'   => $ids['series_id'],
            'location_id' => $locationId,
            'messagetype' => 1,
            'booknumber'  => 101,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 1,
            'hits'        => 0,
            'checked_out' => 0,
            'asset_id'    => 0,
            'created'     => $now,
            'created_by'  => $userId,
            'modified'    => $now,
            'modified_by' => $userId,
        ];
        $db->insertObject('#__bsms_studies', $message);
        $ids['message_id'] = (int) $db->insertid();

        return $ids;
    }

    /**
     * Register Joomla scheduled tasks based on the ministry preset.
     *
     * Only creates tasks that don't already exist.
     *
     * @param   array  $taskKeys  Task keys from the preset (e.g., ['backup', 'podcast', 'analytics'])
     *
     * @return  array  Names of tasks registered.
     *
     * @since   10.3.0
     */
    private function registerScheduledTasks(array $taskKeys): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $taskMap = [
            'backup' => [
                'title'  => 'Proclaim Backup',
                'type'   => 'proclaim.backup',
                'params' => '{}',
                'cron'   => '0 2 * * 0',
            ],
            'podcast' => [
                'title'  => 'Proclaim Podcast Feed',
                'type'   => 'proclaim.podcast',
                'params' => '{}',
                'cron'   => '0 */6 * * *',
            ],
            'analytics' => [
                'title'  => 'Proclaim Analytics',
                'type'   => 'proclaim.analytics',
                'params' => '{}',
                'cron'   => '0 3 * * *',
            ],
        ];

        $registered = [];

        foreach ($taskKeys as $key) {
            if (!isset($taskMap[$key])) {
                continue;
            }

            $task = $taskMap[$key];

            // Check if this task type already exists
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__scheduler_tasks'))
                ->where($db->quoteName('type') . ' = ' . $db->quote($task['type']));
            $db->setQuery($query);

            if ((int) $db->loadResult() > 0) {
                continue;
            }

            $now = (new Date())->toSql();

            $row = (object) [
                'title'           => $task['title'],
                'type'            => $task['type'],
                'execution_rules' => json_encode([
                    'rule-type'       => 'cron-expression',
                    'cron-expression' => $task['cron'],
                ]),
                'params'         => $task['params'],
                'state'          => 1,
                'created'        => $now,
                'created_by'     => Factory::getApplication()->getIdentity()->id ?? 0,
                'last_execution' => null,
                'next_execution' => null,
                'times_executed' => 0,
                'times_failed'   => 0,
                'locked'         => null,
                'priority'       => 0,
                'ordering'       => 0,
                'note'           => 'Created by Proclaim Setup Wizard',
            ];

            try {
                $db->insertObject('#__scheduler_tasks', $row);
                $registered[] = $task['title'];
            } catch (\Throwable) {
                // Task scheduler table may not exist or schema differs
            }
        }

        return $registered;
    }

    /**
     * Ensure the default template (ID 1) exists and is published.
     *
     * The install SQL creates it, but this is a safety check.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private function ensureDefaultTemplate(): void
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_templates'))
            ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query);

        if ((int) $db->loadResult() > 0) {
            // Ensure it's published
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_templates'))
                ->set($db->quoteName('published') . ' = 1')
                ->where($db->quoteName('id') . ' = 1');
            $db->setQuery($query);
            $db->execute();

            return;
        }

        // Create a minimal default template
        $row = (object) [
            'id'          => 1,
            'type'        => 'tmplList',
            'title'       => 'Default',
            'tmpl'        => '',
            'published'   => 1,
            'params'      => '{}',
            'access'      => 1,
            'created'     => (new Date())->toSql(),
            'created_by'  => Factory::getApplication()->getIdentity()->id ?? 0,
            'checked_out' => 0,
        ];
        $db->insertObject('#__bsms_templates', $row);
    }

    /**
     * Create frontend menu items so the site has entry points.
     *
     * Creates items in the site's main menu. Only creates if no Proclaim
     * menu items exist yet.
     *
     * @param   array  $data  Wizard data (ministry_style determines which views)
     *
     * @return  array  List of created menu item titles.
     *
     * @since   10.3.0
     */
    private function createMenuItems(array $data): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Check if any Proclaim menu items already exist
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_proclaim%'))
            ->where($db->quoteName('client_id') . ' = 0');
        $db->setQuery($query);

        if ((int) $db->loadResult() > 0) {
            return [];
        }

        // Find the main menu type
        $query = $db->getQuery(true)
            ->select($db->quoteName('menutype'))
            ->from($db->quoteName('#__menu_types'))
            ->order($db->quoteName('id') . ' ASC');
        $db->setQuery($query, 0, 1);
        $menuType = $db->loadResult() ?: 'mainmenu';

        // Get component ID for com_proclaim
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
        $db->setQuery($query, 0, 1);
        $componentId = (int) $db->loadResult();

        if ($componentId === 0) {
            return [];
        }

        // Find the root menu item for parent_id
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('menutype') . ' = ' . $db->quote($menuType))
            ->where($db->quoteName('level') . ' = 0');
        $db->setQuery($query, 0, 1);
        $parentId = (int) $db->loadResult() ?: 1;

        $style = $data['ministry_style'] ?? 'simple';

        // Define menu items per style
        $items = [
            [
                'title' => 'Sermons',
                'alias' => 'sermons',
                'link'  => 'index.php?option=com_proclaim&view=cwmsermons&t=1',
            ],
        ];

        if ($style !== 'simple') {
            $items[] = [
                'title' => 'Teachers',
                'alias' => 'teachers',
                'link'  => 'index.php?option=com_proclaim&view=cwmteachers&t=1',
            ];
            $items[] = [
                'title' => 'Series',
                'alias' => 'series',
                'link'  => 'index.php?option=com_proclaim&view=cwmseriesdisplays&t=1',
            ];
        }

        $created  = [];
        $ordering = 0;

        foreach ($items as $item) {
            $ordering++;

            try {
                // Use Joomla's Table class for proper nested set handling
                $table               = new \Joomla\CMS\Table\Menu($db);
                $table->menutype     = $menuType;
                $table->title        = $item['title'];
                $table->alias        = $item['alias'];
                $table->link         = $item['link'];
                $table->type         = 'component';
                $table->published    = 1;
                $table->parent_id    = $parentId;
                $table->component_id = $componentId;
                $table->access       = 1;
                $table->language     = '*';
                $table->params       = '{}';
                $table->img          = '';
                $table->home         = 0;
                $table->ordering     = $ordering;

                $table->setLocation($parentId, 'last-child');

                if ($table->check() && $table->store()) {
                    $created[] = $item['title'];
                }
            } catch (\Throwable) {
                // Menu creation failed — non-fatal, user can create manually
            }
        }

        return $created;
    }

    /**
     * Update the default template's filter visibility based on ministry style.
     *
     * Simple mode hides topic, message type, and location filters.
     * Full/Multi-Campus shows everything.
     *
     * @param   array  $data  Wizard data
     *
     * @return  void
     *
     * @since   10.3.0
     */
    private function updateTemplateFilters(array $data): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Load current template params
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_templates'))
            ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query, 0, 1);
        $json = $db->loadResult();

        $params = new Registry($json ?: '{}');
        $style  = $data['ministry_style'] ?? 'simple';

        // Common filters for all styles
        $params->set('show_book_search', 1);
        $params->set('show_teacher_search', 1);
        $params->set('show_series_search', 1);
        $params->set('show_year_search', 1);
        $params->set('show_limit_search', 1);
        $params->set('show_fullordering_search', 1);

        // Style-specific filters
        $params->set('show_topic_search', $style !== 'simple' ? 1 : 0);
        $params->set('show_messagetype_search', $style !== 'simple' ? 1 : 0);
        $params->set('show_location_search', $style === 'multi_campus' ? 1 : 0);

        // Save
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bsms_templates'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params->toString()))
            ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query);
        $db->execute();
    }
}
