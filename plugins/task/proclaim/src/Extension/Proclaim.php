<?php

/**
 * @package         Proclaim.Plugins
 * @subpackage      Task.Proclaim
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @link            https://www.christianwebministries.org
 */

namespace CWM\Plugin\Task\Proclaim\Extension;

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Helper\CwmanalyticsHelper;
use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use CWM\Component\Proclaim\Administrator\Model\CwmanalyticsModel;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Task plugin with routines that offer checks on files.
 * At the moment, it offers a single routine to check and resize image files in a directory.
 *
 * @since  4.1.0
 */
final class Proclaim extends CMSPlugin implements SubscriberInterface
{
    use TaskPluginTrait;

    /**
     * @var string[]
     *
     * @since 4.1.0
     */
    private const TASKS_MAP = [
        'proclaim.backup' => [
            'langConstPrefix' => 'PLG_TASK_PROCLAIM_BACKUP',
            'form'            => 'backup',
            'method'          => 'backup',
        ],
        'proclaim.podcast' => [
            'langConstPrefix' => 'PLG_TASK_PROCLAIM_PODCAST',
            'form'            => 'podcast',
            'method'          => 'podcast',
        ],
        'proclaim.archive' => [
            'langConstPrefix' => 'PLG_TASK_PROCLAIM_ARCHIVE',
            'form'            => 'archive',
            'method'          => 'archive',
        ],
        'proclaim.publish' => [
            'langConstPrefix' => 'PLG_TASK_PROCLAIM_PUBLISH',
            'form'            => 'publish',
            'method'          => 'publish',
        ],
        'proclaim.analytics' => [
            'langConstPrefix' => 'PLG_TASK_PROCLAIM_ANALYTICS',
            'form'            => 'analytics',
            'method'          => 'analyticsTask',
        ],
        'proclaim.platformstats' => [
            'langConstPrefix' => 'PLG_TASK_PROCLAIM_PLATFORMSTATS',
            'form'            => 'platformstats',
            'method'          => 'platformStatsTask',
        ],
    ];

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since 4.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList'    => 'advertiseRoutines',
            'onExecuteTask'        => 'standardRoutineHandler',
            'onContentPrepareForm' => 'enhanceTaskItemForm',
        ];
    }

    /**
     * @var bool
     * @since 4.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher     The dispatcher
     * @param   array                $config         An optional associative array of configuration settings
     *
     * @since   4.2.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config)
    {
        parent::__construct($dispatcher, $config);

        // Always load CWM API if it exists.
        $api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

        if (!\defined('CWM_LOADED')) {
            require_once $api;
        }
    }


    /**
     * Podcast RSS Feed generator
     *
     * @param   ExecuteTaskEvent  $event
     *
     * @return int
     *
     * @throws \Exception
     * @since   1.5
     */
    private function podcast(ExecuteTaskEvent $event): int
    {
        // Load the parameters.
        $forcedLanguage = $event->getArgument('params')->language_override ?? '';

        $jLanguage = $this->getApplication()->getLanguage();
        $jLanguage->load('plg_task_proclaim', JPATH_ADMINISTRATOR, 'en-GB', true, true);
        $jLanguage->load('plg_task_proclaim', JPATH_ADMINISTRATOR, null, true, false);

        // Then try loading the preferred (forced) language
        if (!empty($forcedLanguage)) {
            $jLanguage->load('plg_task_proclaim', JPATH_ADMINISTRATOR, $forcedLanguage, true, false);
        }

        try {
            (new Cwmpodcast())->makePodcasts();
        } catch (\Exception $exception) {
            try {
                $this->logTask($jLanguage->_($exception->getMessage()));
            } catch (\Exception $exception) {
                return Status::KNOCKOUT;
            }
        }

        return Status::OK;
    }


    /**
     * Do the backup
     *
     * @param   ExecuteTaskEvent  $event
     *
     * @return int
     *
     * @since 7.1.0
     */
    private function backup(ExecuteTaskEvent $event): int
    {
        // Load the parameters.
        $forcedLanguage = $event->getArgument('params')->language_override ?? '';

        $jLanguage = $this->getApplication()->getLanguage();
        $jLanguage->load('plg_task_proclaim', JPATH_ADMINISTRATOR, 'en-GB', true, true);
        $jLanguage->load('plg_task_proclaim', JPATH_ADMINISTRATOR, null, true, false);

        // Then try loading the preferred (forced) language
        if (!empty($forcedLanguage)) {
            $jLanguage->load('plg_task_proclaim', JPATH_ADMINISTRATOR, $forcedLanguage, true, false);
        }

        try {
            (new Cwmbackup())->exportdb(2);
        } catch (\Exception $e) {
            try {
                $this->logTask($jLanguage->_($e->getMessage()));
            } catch (\Exception $exception) {
                return Status::KNOCKOUT;
            }
        }

        return Status::OK;
    }

    /**
     * Archive old messages
     *
     * @param   ExecuteTaskEvent  $event
     *
     * @return int
     *
     * @since 10.2.0
     */
    private function archive(ExecuteTaskEvent $event): int
    {
        $params = ComponentHelper::getParams('com_proclaim');

        if (!$params->get('archive_auto')) {
            return Status::OK;
        }

        $timeframe = (int) ($params->get('archive_timeframe', 1));
        $interval  = $params->get('archive_interval', 'year');

        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);

            // Calculate cutoff date
            $cutoffDate = Factory::getDate()->modify('-' . $timeframe . ' ' . $interval . 's')->toSql();

            // Update query
            $query->update($db->quoteName('#__bsms_studies'))
                ->set($db->quoteName('published') . ' = 2')
                ->where($db->quoteName('studydate') . ' < ' . $db->quote($cutoffDate))
                ->where($db->quoteName('published') . ' = 1');

            $db->setQuery($query);
            $db->execute();
            $affected = $db->getAffectedRows();

            $this->logTask(Text::sprintf('PLG_TASK_PROCLAIM_ARCHIVE_SUCCESS', $affected));

        } catch (\Exception $e) {
            try {
                $this->logTask($e->getMessage());
            } catch (\Exception $exception) {
                return Status::KNOCKOUT;
            }
            return Status::KNOCKOUT;
        }

        return Status::OK;
    }

    /**
     * Scheduled publishing: auto-publish and auto-unpublish/archive
     * messages and series based on publish_up / publish_down dates.
     *
     * @param   ExecuteTaskEvent  $event
     *
     * @return int
     *
     * @since 10.1.0
     */
    private function publish(ExecuteTaskEvent $event): int
    {
        $params            = $event->getArgument('params');
        $publishDownAction = (int) ($params->publish_down_action ?? 0);

        // Validate: only 0 (unpublish) or 2 (archive) are valid
        if ($publishDownAction !== 0 && $publishDownAction !== 2) {
            $publishDownAction = 0;
        }

        try {
            $db       = Factory::getContainer()->get('DatabaseDriver');
            $nowDate  = Factory::getDate()->toSql();
            $nullDate = $db->getNullDate();

            $totalPublished = 0;
            $totalExpired   = 0;

            $tables = ['#__bsms_studies', '#__bsms_series'];

            foreach ($tables as $table) {
                // Auto-publish: unpublished items whose publish_up has passed
                $query = $db->getQuery(true)
                    ->update($db->quoteName($table))
                    ->set($db->quoteName('published') . ' = 1')
                    ->where($db->quoteName('published') . ' = 0')
                    ->where($db->quoteName('publish_up') . ' != ' . $db->quote($nullDate))
                    ->where($db->quoteName('publish_up') . ' <= ' . $db->quote($nowDate));

                $db->setQuery($query);
                $db->execute();
                $totalPublished += $db->getAffectedRows();

                // Auto-unpublish/archive: published items whose publish_down has passed
                $query = $db->getQuery(true)
                    ->update($db->quoteName($table))
                    ->set($db->quoteName('published') . ' = ' . $publishDownAction)
                    ->where($db->quoteName('published') . ' = 1')
                    ->where($db->quoteName('publish_down') . ' != ' . $db->quote($nullDate))
                    ->where($db->quoteName('publish_down') . ' <= ' . $db->quote($nowDate));

                $db->setQuery($query);
                $db->execute();
                $totalExpired += $db->getAffectedRows();
            }

            $this->logTask(Text::sprintf('PLG_TASK_PROCLAIM_PUBLISH_SUCCESS', $totalPublished, $totalExpired));
        } catch (\Exception $e) {
            try {
                $this->logTask($e->getMessage());
            } catch (\Exception $exception) {
                return Status::KNOCKOUT;
            }

            return Status::KNOCKOUT;
        }

        return Status::OK;
    }

    /**
     * Analytics rollup, purge, and optional email report.
     *
     * @param   ExecuteTaskEvent  $event
     *
     * @return int
     *
     * @since   10.1.0
     */
    private function analyticsTask(ExecuteTaskEvent $event): int
    {
        $params        = $event->getArgument('params');
        $enableRollup  = (bool) ($params->enable_rollup ?? true);
        $enablePurge   = (bool) ($params->enable_purge ?? true);
        $retentionDays = (int) ($params->retention_days ?? 90);

        $jLanguage = $this->getApplication()->getLanguage();
        $jLanguage->load('plg_task_proclaim', JPATH_ADMINISTRATOR, 'en-GB', true, true);

        try {
            if ($enableRollup || $enablePurge) {
                $result = CwmanalyticsHelper::rollupAndPurge($retentionDays);
                $this->logTask(
                    Text::sprintf(
                        'PLG_TASK_PROCLAIM_ANALYTICS_ROLLUP_SUCCESS',
                        $result['rolled'],
                        $result['purged']
                    )
                );
            }

            $enableEmail = (bool) ($params->enable_email ?? false);
            $reportEmail = trim((string) ($params->report_email ?? ''));
            $reportDays  = (int) ($params->report_days ?? 30);

            if ($enableEmail && $reportEmail !== '' && filter_var($reportEmail, FILTER_VALIDATE_EMAIL)) {
                $start = date('Y-m-d', strtotime('-' . $reportDays . ' days'));
                $end   = date('Y-m-d');

                /** @var CwmanalyticsModel $analyticsModel */
                $analyticsModel = Factory::getApplication()
                    ->bootComponent('com_proclaim')
                    ->getMVCFactory()
                    ->createModel('Cwmanalytics', 'Administrator');

                $kpi = $analyticsModel->getKpiTotals($start, $end);
                $top = $analyticsModel->getTopStudies($start, $end, 5);

                $body  = '<h2>' . Text::_('PLG_TASK_PROCLAIM_ANALYTICS_EMAIL_TITLE') . '</h2>';
                $body .= '<p>' . Text::sprintf('PLG_TASK_PROCLAIM_ANALYTICS_EMAIL_PERIOD', $start, $end) . '</p>';
                $body .= '<table border="1" cellpadding="5"><tr>';
                $body .= '<th>' . Text::_('JBS_ANA_TOTAL_VIEWS') . '</th>';
                $body .= '<th>' . Text::_('JBS_ANA_TOTAL_PLAYS') . '</th>';
                $body .= '<th>' . Text::_('JBS_ANA_TOTAL_DOWNLOADS') . '</th></tr><tr>';
                $body .= '<td>' . number_format($kpi['views']) . '</td>';
                $body .= '<td>' . number_format($kpi['plays']) . '</td>';
                $body .= '<td>' . number_format($kpi['downloads']) . '</td></tr></table>';

                if (!empty($top)) {
                    $body .= '<h3>' . Text::_('JBS_ANA_TOP_SERMONS') . '</h3><ol>';

                    foreach ($top as $row) {
                        $title = htmlspecialchars((string) ($row['title'] ?? 'ID #' . $row['study_id']), ENT_QUOTES);
                        $body .= '<li>' . $title . ' (' . (int) ($row['total'] ?? 0) . ')</li>';
                    }

                    $body .= '</ol>';
                }

                try {
                    $mailer = Factory::getMailer();
                    $mailer->addRecipient($reportEmail);
                    $mailer->setSubject(Text::_('PLG_TASK_PROCLAIM_ANALYTICS_EMAIL_SUBJECT'));
                    $mailer->setBody($body);
                    $mailer->isHTML(true);
                    $mailer->Send();
                    $this->logTask(Text::sprintf('PLG_TASK_PROCLAIM_ANALYTICS_EMAIL_SENT', $reportEmail));
                } catch (\Exception $mailException) {
                    $this->logTask('Analytics email failed: ' . $mailException->getMessage());
                }
            }
        } catch (\Exception $e) {
            try {
                $this->logTask($e->getMessage());
            } catch (\Exception $exception) {
                return Status::KNOCKOUT;
            }

            return Status::KNOCKOUT;
        }

        return Status::OK;
    }

    /**
     * Sync video statistics from external platforms (YouTube, Vimeo, Wistia, etc.).
     *
     * Discovers all stats-capable servers and calls their fetchPlatformStats() method.
     *
     * @param   ExecuteTaskEvent  $event
     *
     * @return  int
     *
     * @since   10.1.0
     */
    private function platformStatsTask(ExecuteTaskEvent $event): int
    {
        $jLanguage = $this->getApplication()->getLanguage();
        $jLanguage->load('plg_task_proclaim', JPATH_ADMINISTRATOR, 'en-GB', true, true);

        $params     = $event->getArgument('params') ?? new \stdClass();
        $batchLimit = (int) ($params->batch_limit ?? 50);

        try {
            $servers = CWMAddon::getStatsCapableServers();

            if (empty($servers)) {
                $this->logTask(Text::_('PLG_TASK_PROCLAIM_PLATFORMSTATS_NO_SERVERS'));

                return Status::OK;
            }

            $totalSynced    = 0;
            $totalRemaining = 0;
            $errors         = [];

            foreach ($servers as $srv) {
                try {
                    $addon  = CWMAddon::getInstance($srv['type']);
                    $result = $addon->fetchPlatformStats((int) $srv['id'], $batchLimit);

                    if ($result['success']) {
                        $totalSynced += $result['synced'];
                    }

                    $totalRemaining += ($result['remaining'] ?? 0);

                    if (!empty($result['errors'])) {
                        $errors = array_merge($errors, $result['errors']);
                    }
                } catch (\Exception $e) {
                    $errors[] = ($srv['server_name'] ?? $srv['type']) . ': ' . $e->getMessage();
                }
            }

            $this->logTask(Text::sprintf('PLG_TASK_PROCLAIM_PLATFORMSTATS_RESULT', $totalSynced, \count($errors)));

            if ($totalRemaining > 0) {
                $this->logTask(Text::sprintf('PLG_TASK_PROCLAIM_PLATFORMSTATS_REMAINING', $totalRemaining));
            }

            if (!empty($errors)) {
                foreach ($errors as $err) {
                    $this->logTask('  - ' . $err);
                }
            }
        } catch (\Exception $e) {
            try {
                $this->logTask($e->getMessage());
            } catch (\Exception $exception) {
                return Status::KNOCKOUT;
            }

            return Status::KNOCKOUT;
        }

        return Status::OK;
    }
}
