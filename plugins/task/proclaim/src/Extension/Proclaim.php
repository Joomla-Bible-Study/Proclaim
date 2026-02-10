<?php

/**
 * @package         Proclaim.Plugins
 * @subpackage      Task.Proclaim
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @link            https://www.christianwebministries.org
 */

namespace CWM\Plugin\Task\Proclaim\Extension;

use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
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
}
