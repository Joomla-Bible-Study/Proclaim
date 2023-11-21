<?php

/**
 * @package         Joomla.Plugins
 * @subpackage      Task.CheckFiles
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Plugin\Task\Proclaim\Extension;

use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Task plugin with routines that offer checks on files.
 * At the moment, offers a single routine to check and resize image files in a directory.
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
    protected const TASKS_MAP = [
        'proclaim.backup'  => [
            'langConstPrefix' => 'PLG_TASK_PROCLAIM_BACKUP',
            'form'            => 'backup',
            'method'          => 'backup',
        ],
        'proclaim.podcast' => [
            'langConstPrefix' => 'PLG_TASK_PROCLAIM_PODCAST',
            'form'            => 'podcast',
            'method'          => 'podcast',
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
     * The root directory path
     *
     * @var    string
     * @since  4.2.0
     */
    private string $rootDirectory;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher     The dispatcher
     * @param   array                $config         An optional associative array of configuration settings
     * @param   string               $rootDirectory  The root directory to look for images
     *
     * @since   4.2.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, string $rootDirectory)
    {
        parent::__construct($dispatcher, $config);

        // Always load CWM API if it exists.
        $api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

        if (file_exists($api)) {
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
    public function podcast(ExecuteTaskEvent $event): int
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
    public function backup(ExecuteTaskEvent $event): int
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
}
