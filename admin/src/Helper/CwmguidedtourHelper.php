<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseInterface;

/**
 * Helper class for creating and managing guided tours and announcements.
 *
 * This class provides a future-proof way to register guided tours
 * and post-install messages for new features.
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class CwmguidedtourHelper
{
    /**
     * Database driver
     *
     * @var DatabaseInterface
     * @since 10.2.0
     */
    protected DatabaseInterface $db;

    /**
     * Proclaim extension ID
     *
     * @var int
     * @since 10.2.0
     */
    protected int $extensionId;

    /**
     * Array of tour definitions.
     * Each tour has steps that guide users through the feature.
     *
     * @var array
     * @since 10.2.0
     */
    protected array $tours = [
        'whats_new_10_1' => [
            'title'       => 'COM_PROCLAIM_TOUR_WHATS_NEW_TITLE',
            'description' => 'COM_PROCLAIM_TOUR_WHATS_NEW_DESC',
            'url'         => 'administrator/index.php?option=com_proclaim&view=cwmcpanel',
            'extensions'  => ['com_proclaim'],
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'note'        => '',
            'uid'         => 'com_proclaim_whats_new_10_1',
            'steps'       => [
                // Welcome step
                [
                    'title'       => 'COM_PROCLAIM_TOUR_WELCOME_TITLE',
                    'description' => 'COM_PROCLAIM_TOUR_WELCOME_DESC',
                    'position'    => 'bottom',
                    'target'      => '',
                    'type'        => 0,
                    'url'         => 'administrator/index.php?option=com_proclaim&view=cwmcpanel',
                ],
                // Archived Messages Feature - Step 1
                [
                    'title'       => 'COM_PROCLAIM_TOUR_ARCHIVED_STEP1_TITLE',
                    'description' => 'COM_PROCLAIM_TOUR_ARCHIVED_STEP1_DESC',
                    'position'    => 'bottom',
                    'target'      => '',
                    'type'        => 0,
                    'url'         => '',
                ],
                // Archived Messages Feature - Step 2
                [
                    'title'       => 'COM_PROCLAIM_TOUR_ARCHIVED_STEP2_TITLE',
                    'description' => 'COM_PROCLAIM_TOUR_ARCHIVED_STEP2_DESC',
                    'position'    => 'bottom',
                    'target'      => '.sidebar-nav a[href*="cwmmessages"]',
                    'type'        => 1,
                    'url'         => '',
                ],
                // Archived Messages Feature - Step 3
                [
                    'title'       => 'COM_PROCLAIM_TOUR_ARCHIVED_STEP3_TITLE',
                    'description' => 'COM_PROCLAIM_TOUR_ARCHIVED_STEP3_DESC',
                    'position'    => 'bottom',
                    'target'      => '#filter_published',
                    'type'        => 1,
                    'url'         => 'administrator/index.php?option=com_proclaim&view=cwmmessages',
                ],
                // Archived Messages Feature - Step 4
                [
                    'title'       => 'COM_PROCLAIM_TOUR_ARCHIVED_STEP4_TITLE',
                    'description' => 'COM_PROCLAIM_TOUR_ARCHIVED_STEP4_DESC',
                    'position'    => 'bottom',
                    'target'      => '.sidebar-nav a[href*="cwmtemplates"]',
                    'type'        => 1,
                    'url'         => '',
                ],
                // Archived Messages Feature - Step 5
                [
                    'title'       => 'COM_PROCLAIM_TOUR_ARCHIVED_STEP5_TITLE',
                    'description' => 'COM_PROCLAIM_TOUR_ARCHIVED_STEP5_DESC',
                    'position'    => 'center',
                    'target'      => '',
                    'type'        => 0,
                    'url'         => '',
                ],
                // Layout Editor - Step 1
                [
                    'title'       => 'COM_PROCLAIM_TOUR_LAYOUT_STEP1_TITLE',
                    'description' => 'COM_PROCLAIM_TOUR_LAYOUT_STEP1_DESC',
                    'position'    => 'center',
                    'target'      => '',
                    'type'        => 0,
                    'url'         => '',
                ],
                // Layout Editor - Step 2
                [
                    'title'       => 'COM_PROCLAIM_TOUR_LAYOUT_STEP2_TITLE',
                    'description' => 'COM_PROCLAIM_TOUR_LAYOUT_STEP2_DESC',
                    'position'    => 'bottom',
                    'target'      => '#myTabTabs button[data-bs-target*="layout"]',
                    'type'        => 1,
                    'url'         => 'administrator/index.php?option=com_proclaim&view=cwmtemplate&layout=edit&id=1',
                ],
                // CPanel Improvements - Step 1
                [
                    'title'       => 'COM_PROCLAIM_TOUR_CPANEL_STEP1_TITLE',
                    'description' => 'COM_PROCLAIM_TOUR_CPANEL_STEP1_DESC',
                    'position'    => 'bottom',
                    'target'      => '.cpanel-links',
                    'type'        => 1,
                    'url'         => 'administrator/index.php?option=com_proclaim&view=cwmcpanel',
                ],
                // CPanel Improvements - Step 2 (Dark Mode)
                [
                    'title'       => 'COM_PROCLAIM_TOUR_CPANEL_STEP2_TITLE',
                    'description' => 'COM_PROCLAIM_TOUR_CPANEL_STEP2_DESC',
                    'position'    => 'center',
                    'target'      => '',
                    'type'        => 0,
                    'url'         => '',
                ],
                // Accessibility Improvements
                [
                    'title'       => 'COM_PROCLAIM_TOUR_A11Y_STEP1_TITLE',
                    'description' => 'COM_PROCLAIM_TOUR_A11Y_STEP1_DESC',
                    'position'    => 'center',
                    'target'      => '',
                    'type'        => 0,
                    'url'         => '',
                ],
                // Closing step
                [
                    'title'       => 'COM_PROCLAIM_TOUR_COMPLETE_TITLE',
                    'description' => 'COM_PROCLAIM_TOUR_COMPLETE_DESC',
                    'position'    => 'center',
                    'target'      => '',
                    'type'        => 0,
                    'url'         => '',
                ],
            ],
        ],
    ];

    /**
     * Array of post-install messages to register.
     *
     * @var array
     * @since 10.2.0
     */
    protected array $postInstallMessages = [
        'archived_messages' => [
            'title_key'          => 'COM_PROCLAIM_POSTINSTALL_ARCHIVED_TITLE',
            'description_key'    => 'COM_PROCLAIM_POSTINSTALL_ARCHIVED_DESC',
            'action_key'         => 'COM_PROCLAIM_POSTINSTALL_ARCHIVED_ACTION',
            'type'               => 'action',
            'action_file'        => 'admin://components/com_proclaim/postinstall/archivedmessages.php',
            'action'             => 'admin_postinstall_archivedmessages_action',
            'condition_file'     => 'admin://components/com_proclaim/postinstall/archivedmessages.php',
            'condition_method'   => 'admin_postinstall_archivedmessages_condition',
            'version_introduced' => '10.2.0',
        ],
    ];

    /**
     * Constructor
     *
     * @since 10.2.0
     */
    public function __construct()
    {
        $this->db          = Factory::getContainer()->get(DatabaseInterface::class);
        $this->extensionId = $this->getExtensionId();
    }

    /**
     * Get the Proclaim extension ID.
     *
     * @return int Extension ID
     *
     * @since 10.2.0
     */
    protected function getExtensionId(): int
    {
        $query = $this->db->getQuery(true)
            ->select('extension_id')
            ->from($this->db->quoteName('#__extensions'))
            ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_proclaim'))
            ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'));
        $this->db->setQuery($query);

        return (int) $this->db->loadResult();
    }

    /**
     * Register all guided tours and post-install messages.
     *
     * @return void
     *
     * @since 10.2.0
     */
    public function registerAll(): void
    {
        $this->registerPostInstallMessages();
        $this->registerGuidedTours();
    }

    /**
     * Register a specific tour by key.
     *
     * @param   string  $key  Tour key
     *
     * @return  bool  True on success
     *
     * @since   10.2.0
     */
    public function registerTour(string $key): bool
    {
        if (!isset($this->tours[$key])) {
            Log::add('Tour not found: ' . $key, Log::WARNING, 'com_proclaim');
            return false;
        }

        // Check if Joomla version supports guided tours (4.3+)
        if (!$this->supportsGuidedTours()) {
            Log::add('Joomla version does not support guided tours', Log::INFO, 'com_proclaim');
            return false;
        }

        return $this->insertTour($key, $this->tours[$key]);
    }

    /**
     * Register a specific post-install message by key.
     *
     * @param   string  $key  Message key
     *
     * @return  bool  True on success
     *
     * @since   10.2.0
     */
    public function registerPostInstallMessage(string $key): bool
    {
        if (!isset($this->postInstallMessages[$key])) {
            Log::add('Post-install message not found: ' . $key, Log::WARNING, 'com_proclaim');
            return false;
        }

        return $this->insertPostInstallMessage($this->postInstallMessages[$key]);
    }

    /**
     * Register all post-install messages.
     *
     * @return  int  Number of messages registered
     *
     * @since   10.2.0
     */
    public function registerPostInstallMessages(): int
    {
        $count = 0;

        foreach ($this->postInstallMessages as $key => $message) {
            if (!$this->postInstallMessageExists($message['title_key'])) {
                if ($this->insertPostInstallMessage($message)) {
                    $count++;
                    Log::add('Registered post-install message: ' . $key, Log::INFO, 'com_proclaim');
                }
            }
        }

        return $count;
    }

    /**
     * Register all guided tours.
     *
     * @return  int  Number of tours registered
     *
     * @since   10.2.0
     */
    public function registerGuidedTours(): int
    {
        if (!$this->supportsGuidedTours()) {
            Log::add('Guided tours not supported on this Joomla version', Log::INFO, 'com_proclaim');
            return 0;
        }

        $count = 0;

        foreach ($this->tours as $key => $tour) {
            if (!$this->tourExists($tour['uid'])) {
                if ($this->insertTour($key, $tour)) {
                    $count++;
                    Log::add('Registered guided tour: ' . $key, Log::INFO, 'com_proclaim');
                }
            }
        }

        return $count;
    }

    /**
     * Check if the current Joomla version supports guided tours.
     *
     * @return  bool  True if supported
     *
     * @since   10.2.0
     */
    public function supportsGuidedTours(): bool
    {
        $version = new Version();

        // Guided tours were introduced in Joomla 4.3
        if (version_compare($version->getShortVersion(), '4.3.0', '<')) {
            return false;
        }

        // Check if the guided tours table exists
        $tables = $this->db->getTableList();
        $prefix = $this->db->getPrefix();

        return \in_array($prefix . 'guidedtours', $tables, true);
    }

    /**
     * Check if a tour already exists.
     *
     * @param   string  $uid  Tour UID
     *
     * @return  bool  True if exists
     *
     * @since   10.2.0
     */
    protected function tourExists(string $uid): bool
    {
        if (!$this->supportsGuidedTours()) {
            return false;
        }

        $query = $this->db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__guidedtours'))
            ->where($this->db->quoteName('uid') . ' = ' . $this->db->quote($uid));
        $this->db->setQuery($query);

        return (int) $this->db->loadResult() > 0;
    }

    /**
     * Check if a post-install message already exists.
     *
     * @param   string  $titleKey  Message title key
     *
     * @return  bool  True if exists
     *
     * @since   10.2.0
     */
    protected function postInstallMessageExists(string $titleKey): bool
    {
        $query = $this->db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__postinstall_messages'))
            ->where($this->db->quoteName('title_key') . ' = ' . $this->db->quote($titleKey))
            ->where($this->db->quoteName('extension_id') . ' = ' . $this->extensionId);
        $this->db->setQuery($query);

        return (int) $this->db->loadResult() > 0;
    }

    /**
     * Insert a guided tour and its steps.
     *
     * @param   string  $key   Tour key
     * @param   array   $tour  Tour definition
     *
     * @return  bool  True on success
     *
     * @since   10.2.0
     */
    protected function insertTour(string $key, array $tour): bool
    {
        try {
            // Insert the tour
            $tourObj = (object) [
                'title'       => $tour['title'],
                'description' => $tour['description'],
                'extensions'  => json_encode($tour['extensions']),
                'url'         => $tour['url'],
                'published'   => $tour['published'],
                'access'      => $tour['access'],
                'language'    => $tour['language'],
                'note'        => $tour['note'],
                'uid'         => $tour['uid'],
                'created'     => Factory::getDate()->toSql(),
                'created_by'  => 0,
            ];

            $this->db->insertObject('#__guidedtours', $tourObj, 'id');
            $tourId = $this->db->insertid();

            // Insert the steps
            $ordering = 1;

            foreach ($tour['steps'] as $step) {
                $stepObj = (object) [
                    'tour_id'     => $tourId,
                    'title'       => $step['title'],
                    'description' => $step['description'],
                    'position'    => $step['position'],
                    'target'      => $step['target'],
                    'type'        => $step['type'],
                    'url'         => $step['url'],
                    'published'   => 1,
                    'language'    => '*',
                    'ordering'    => $ordering++,
                    'note'        => '',
                ];

                $this->db->insertObject('#__guidedtour_steps', $stepObj);
            }

            return true;
        } catch (\Exception $e) {
            Log::add('Error inserting tour: ' . $e->getMessage(), Log::ERROR, 'com_proclaim');
            return false;
        }
    }

    /**
     * Insert a post-install message.
     *
     * @param   array  $message  Message definition
     *
     * @return  bool  True on success
     *
     * @since   10.2.0
     */
    protected function insertPostInstallMessage(array $message): bool
    {
        try {
            $msgObj = (object) [
                'extension_id'       => $this->extensionId,
                'title_key'          => $message['title_key'],
                'description_key'    => $message['description_key'],
                'action_key'         => $message['action_key'] ?? '',
                'language_extension' => 'com_proclaim',
                'language_client_id' => 1,
                'type'               => $message['type'],
                'action_file'        => $message['action_file'] ?? '',
                'action'             => $message['action'] ?? '',
                'condition_file'     => $message['condition_file'] ?? '',
                'condition_method'   => $message['condition_method'] ?? '',
                'version_introduced' => $message['version_introduced'],
                'enabled'            => 1,
            ];

            $this->db->insertObject('#__postinstall_messages', $msgObj);

            return true;
        } catch (\Exception $e) {
            Log::add('Error inserting post-install message: ' . $e->getMessage(), Log::ERROR, 'com_proclaim');
            return false;
        }
    }

    /**
     * Remove all Proclaim guided tours.
     *
     * @return  int  Number of tours removed
     *
     * @since   10.2.0
     */
    public function removeAllTours(): int
    {
        if (!$this->supportsGuidedTours()) {
            return 0;
        }

        $count = 0;

        foreach ($this->tours as $tour) {
            $query = $this->db->getQuery(true)
                ->select('id')
                ->from($this->db->quoteName('#__guidedtours'))
                ->where($this->db->quoteName('uid') . ' = ' . $this->db->quote($tour['uid']));
            $this->db->setQuery($query);
            $tourId = $this->db->loadResult();

            if ($tourId) {
                // Delete steps first
                $query = $this->db->getQuery(true)
                    ->delete($this->db->quoteName('#__guidedtour_steps'))
                    ->where($this->db->quoteName('tour_id') . ' = ' . (int) $tourId);
                $this->db->setQuery($query);
                $this->db->execute();

                // Delete tour
                $query = $this->db->getQuery(true)
                    ->delete($this->db->quoteName('#__guidedtours'))
                    ->where($this->db->quoteName('id') . ' = ' . (int) $tourId);
                $this->db->setQuery($query);
                $this->db->execute();

                $count++;
            }
        }

        return $count;
    }

    /**
     * Add a new tour definition at runtime.
     *
     * @param   string  $key   Tour key
     * @param   array   $tour  Tour definition
     *
     * @return  self
     *
     * @since   10.2.0
     */
    public function addTour(string $key, array $tour): self
    {
        $this->tours[$key] = $tour;
        return $this;
    }

    /**
     * Add a new post-install message definition at runtime.
     *
     * @param   string  $key      Message key
     * @param   array   $message  Message definition
     *
     * @return  self
     *
     * @since   10.2.0
     */
    public function addPostInstallMessage(string $key, array $message): self
    {
        $this->postInstallMessages[$key] = $message;
        return $this;
    }
}
