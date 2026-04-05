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

        if (!empty($data['uploadpath'])) {
            $params->set('uploadpath', trim($data['uploadpath']));
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

        // Sample content
        if (!empty($data['create_sample_content'])) {
            $sampleIds                 = $this->createSampleContent($data);
            $summary['sample_content'] = $sampleIds;
        }

        // Scheduled tasks
        if ($preset) {
            $tasksRegistered             = $this->registerScheduledTasks($preset['tasks']);
            $summary['tasks_registered'] = $tasksRegistered;
        }

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
     * Create sample content: one teacher, one series, one message.
     *
     * @param   array  $data  Wizard data (for org_name, defaults)
     *
     * @return  array  IDs of created content.
     *
     * @since   10.3.0
     */
    private function createSampleContent(array $data): array
    {
        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $now    = (new Date())->toSql();
        $userId = Factory::getApplication()->getIdentity()->id ?? 0;
        $ids    = [];

        // Create sample teacher
        $teacher = (object) [
            'teachername' => 'Sample Teacher',
            'alias'       => 'sample-teacher',
            'title'       => 'Pastor',
            'short'       => '<p>This is a sample teacher created by the setup wizard. Edit or replace with your real teacher.</p>',
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

        // Create sample message
        $message = (object) [
            'studytitle'  => 'Welcome to Proclaim',
            'alias'       => 'welcome-to-proclaim',
            'studydate'   => $now,
            'studyintro'  => '<p>This is a sample message created by the setup wizard to help you see how content appears on your site.</p>',
            'teacher_id'  => $ids['teacher_id'],
            'series_id'   => $ids['series_id'],
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
}
