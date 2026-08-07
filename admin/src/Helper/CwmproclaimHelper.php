<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Proclaim Helper class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmproclaimHelper
{
    /**
     * Admin Params
     *
     * @var ?object
     *
     * @since 1.5
     */
    public static ?object $admin_params = null;

    /**
     * Set extension
     *
     * @var string
     *
     * @since 1.5
     */
    public static string $extension = 'com_proclaim';

    /**
     * Cached installed component version.
     *
     * @var  ?string
     *
     * @since __DEPLOY_VERSION__
     */
    private static ?string $version = null;

    /**
     * Get the installed Proclaim version.
     *
     * Reads `#__extensions.manifest_cache`, which every Joomla application
     * (site, admin, API, CLI) can query. This replaces the old
     * BIBLESTUDY_VERSION constant, which admin/api.php only populated by
     * parsing proclaim.xml off the filesystem, and only when the current
     * client was 'administrator' — everywhere else, including the API
     * application, it was an empty string. See #1429.
     *
     * @return  string  Semver version string, or '0.0.0' if unavailable
     *
     * @since __DEPLOY_VERSION__
     */
    public static function getVersion(): string
    {
        if (self::$version !== null) {
            return self::$version;
        }

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select($db->quoteName('manifest_cache'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'));
            $db->setQuery($query);
            $cache = $db->loadResult();

            $manifest      = $cache ? json_decode($cache, true) : null;
            self::$version = $manifest['version'] ?? '0.0.0';
        } catch (\Throwable) {
            self::$version = '0.0.0';
        }

        return self::$version;
    }

    /**
     * Define the legacy BIBLESTUDY_* path constants, if not already defined.
     *
     * Declared here rather than with `const` in admin/api.php, which only the
     * administrator and site applications include — the API application never
     * loads it, so code reachable from the API would see the constants as
     * undefined and fatal. ProclaimComponent::boot() calls this and runs in
     * every application.
     *
     * admin/api.php still calls this too, guarded, for the paths that reach
     * that file without booting the component (e.g. postinstall messages).
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    public static function defineLegacyPathConstants(): void
    {
        if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
            \define('BIBLESTUDY_COMPONENT_NAME', 'com_proclaim');
        }

        if (!\defined('BIBLESTUDY_COMPONENT_RELPATH')) {
            \define('BIBLESTUDY_COMPONENT_RELPATH', 'components' . DIRECTORY_SEPARATOR . BIBLESTUDY_COMPONENT_NAME);
        }

        if (!\defined('BIBLESTUDY_ROOT_PATH')) {
            \define('BIBLESTUDY_ROOT_PATH', JPATH_ROOT);
        }

        if (!\defined('BIBLESTUDY_ROOT_PATH_ADMIN')) {
            \define('BIBLESTUDY_ROOT_PATH_ADMIN', JPATH_ADMINISTRATOR);
        }

        if (!\defined('BIBLESTUDY_MEDIA_PATH')) {
            \define(
                'BIBLESTUDY_MEDIA_PATH',
                JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_proclaim'
            );
        }

        if (!\defined('BIBLESTUDY_PATH_ADMIN')) {
            \define('BIBLESTUDY_PATH_ADMIN', BIBLESTUDY_ROOT_PATH_ADMIN . DIRECTORY_SEPARATOR . BIBLESTUDY_COMPONENT_RELPATH);
        }
    }

    /**
     * Update View and Controller to work with Namespace Case-Sensitive
     *
     * @param   string  $defaultController  Default Controller
     *
     * @return void
     * @throws \Exception
     * @since    10.0.0
     */
    public static function applyViewAndController(string $defaultController): void
    {
        $input      = Factory::getApplication()->getInput();
        $controller = $input->getCmd('controller');
        $view       = $input->getCmd('view');
        $task       = $input->getCmd('task', 'display');

        if (str_contains($task, '.')) {
            // Explode the controller.task command.
            [$controller, $task] = explode('.', $task);
        }

        if (empty($controller) && empty($view)) {
            $controller = $defaultController;
            $view       = $defaultController;
        } elseif (!empty($controller) && empty($view)) {
            $view = $controller;
        }

        $input->set('view', $view);
        $input->set('controller', $controller);
        $input->set('task', $task);
    }

    /**
     * Applies the content tag filters to arbitrary text as per settings for the current user group.
     * If no Proclaim-specific filters are configured, it falls back to Joomla's global text filtering.
     *
     * @param   string  $text  The string to filter
     *
     * @return string The filtered string
     *
     * @throws \Exception
     * @since 1.5
     */
    public static function filterText(string $text): string
    {
        // Filter settings from com_proclaim
        $config     = ComponentHelper::getParams('com_proclaim');
        $user       = Factory::getApplication()->getIdentity();
        $userGroups = Access::getGroupsByUser($user->id);

        $filters = $config->get('filters');

        // If no Proclaim-specific filters are configured, use Joomla's global text filtering
        if (empty($filters)) {
            return ComponentHelper::filterText($text);
        }

        $blackListTags       = [];
        $blackListAttributes = [];

        $whiteListTags       = [];
        $whiteListAttributes = [];

        $whiteList  = false;
        $blackList  = false;
        $unfiltered = false;

        // Track if any filter was found for the user's groups
        $hasFilterForUser = false;

        // Cycle through each user group the user is in.
        // Remember, they are also included in the Public group.
        foreach ($userGroups as $groupId) {
            // May have added a group by not saving the filters.
            if (!isset($filters->$groupId)) {
                continue;
            }

            $hasFilterForUser = true;

            // Each group the user is in could have different filtering properties.
            $filterData = $filters->$groupId;
            $filterType = strtoupper($filterData->filter_type);

            if ($filterType !== 'NH') {
                if ($filterType === 'NONE') {
                    // No HTML filtering.
                    $unfiltered = true;
                } else {
                    // Black or whitelist.
                    // Prepossess the tags and attributes.
                    $tags           = explode(',', $filterData->filter_tags);
                    $attributes     = explode(',', $filterData->filter_attributes);
                    $tempTags       = [];
                    $tempAttributes = [];

                    foreach ($tags as $tag) {
                        $tag = trim($tag);

                        if ($tag) {
                            $tempTags[] = $tag;
                        }
                    }

                    foreach ($attributes as $attribute) {
                        $attribute = trim($attribute);

                        if ($attribute) {
                            $tempAttributes[] = $attribute;
                        }
                    }

                    // Collect the black- or whitelisted tags and attributes.
                    // Each list is cumulative.
                    // $tempTags/$tempAttributes are flat arrays of strings.
                    // Spreading them passed each string as a positional
                    // argument to array_merge(), which requires arrays -- a
                    // TypeError as soon as any group configured a tag. Merging
                    // the two arrays also makes the lists cumulative across
                    // groups, which is what the comment above already claimed
                    // and the overwrite did not do. Matches core's
                    // ComponentHelper::filterText().
                    if ($filterType == 'BL') {
                        $blackList           = true;
                        $blackListTags       = array_merge($blackListTags, $tempTags);
                        $blackListAttributes = array_merge($blackListAttributes, $tempAttributes);
                    } elseif ($filterType == 'WL') {
                        $whiteList           = true;
                        $whiteListTags       = array_merge($whiteListTags, $tempTags);
                        $whiteListAttributes = array_merge($whiteListAttributes, $tempAttributes);
                    }
                }
            }
        }

        // If no filter settings are found for the user's groups, fall back to Joomla's global filtering
        if (!$hasFilterForUser) {
            return ComponentHelper::filterText($text);
        }

        // Remove duplicates before processing (since the blacklist uses both sets of arrays).
        $blackListTags       = array_unique($blackListTags);
        $blackListAttributes = array_unique($blackListAttributes);
        $whiteListTags       = array_unique($whiteListTags);
        $whiteListAttributes = array_unique($whiteListAttributes);

        // Unfiltered assumes first priority.
        if ($unfiltered) {
            $filter = InputFilter::getInstance([], [], 1, 1, 0);
        } elseif ($blackList) {
            // Remove the whitelisted attributes from the blacklist.
            $filter = InputFilter::getInstance(
                // Blacklisted tags
                array_diff($blackListTags, $whiteListTags),
                // Blacklisted attributes
                array_diff($blackListAttributes, $whiteListAttributes),
                // Blacklist tags
                1,
                // Blacklist attributes
                1
            );
        } elseif ($whiteList) {
            // Turn off XSS auto clean
            $filter = InputFilter::getInstance($whiteListTags, $whiteListAttributes, 0, 0, 0);
        } else {
            // Fall back to Joomla's global text filtering
            return ComponentHelper::filterText($text);
        }

        return $filter->clean($text, 'html');
    }

    /**
     * Debug switch state from the Admin Settings page
     *
     * @return int '1' is on, '0' is off
     *
     * @since 7.1.0
     */
    public static function debug(): int
    {
        if (!CwmdbHelper::getInstallState()) {
            try {
                self::$admin_params = Cwmparams::getAdmin();
            } catch (\Exception $e) {
                return 0;
            }

            if (!isset(self::$admin_params->debug)) {
                self::$admin_params        = new \stdClass();
                self::$admin_params->debug = 1;
            }

            return self::$admin_params->debug;
        }

        return 0;
    }

    /**
     * Media Years
     *
     * @return array  Returns an array of years from media files based on creation date
     *
     * @throws \Exception
     * @since 8.0.0
     */
    public static function getMediaYears(): array
    {
        $options = [];
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->createQuery();

        $query->select('DISTINCT YEAR(' . $db->quoteName('createdate') . ') as value, YEAR(' . $db->quoteName('createdate') . ') as text');
        $query->from($db->quoteName('#__bsms_mediafiles'));
        $query->order($db->quoteName('value'));

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        return $options;
    }

    /**
     * Message Types
     *
     * @return array  Returns a list of message types
     *
     * @throws \Exception
     * @since 8.0.0
     */
    public static function getMessageTypes(): array
    {
        $options = [];
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->createQuery();

        $query->select($db->quoteName('messageType.id', 'value') . ', ' . $db->quoteName('messageType.message_type', 'text'));
        $query->from($db->quoteName('#__bsms_message_type', 'messageType'));
        $query->join(
            'INNER',
            $db->quoteName('#__bsms_studies', 'study') . ' ON ' . $db->quoteName('study.messagetype') . ' = ' . $db->quoteName('messageType.id')
        );
        $query->group($db->quoteName('messageType.id'));
        $query->order($db->quoteName('messageType.message_type'));

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        return $options;
    }

    /**
     * Study Years
     *
     * @return array Returns an array of years from studies based on the study date
     *
     * @throws \Exception
     * @since 8.0.0
     */
    public static function getStudyYears(): array
    {
        $options = [];
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->createQuery();

        $query->select('DISTINCT YEAR(' . $db->quoteName('studydate') . ') as value, YEAR(' . $db->quoteName('studydate') . ') as text');
        $query->from($db->quoteName('#__bsms_studies'));
        $query->order($db->quoteName('value') . ' DESC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        return $options;
    }

    /**
     * Teachers
     *
     * @return array  Returns an array of Teachers
     *
     * @throws \Exception
     * @since 8.0.0
     */
    public static function getTeachers(): array
    {
        $options = [];
        // The container returns the driver itself. Neither DatabaseInterface
        // nor DatabaseDriver declares getDriver() -- that exists only as a
        // static on DatabaseFactory -- so the extra hop was a fatal on the
        // first call. Every sibling method here uses the container result
        // directly.
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->createQuery();

        $query->select($db->quoteName('teacher.id', 'value') . ', ' . $db->quoteName('teacher.teachername', 'text'));
        $query->from($db->quoteName('#__bsms_teachers', 'teacher'));
        $query->join(
            'INNER',
            $db->quoteName('#__bsms_study_teachers', 'stj') . ' ON ' . $db->quoteName('stj.teacher_id') . ' = ' . $db->quoteName('teacher.id')
        );
        $query->join(
            'INNER',
            $db->quoteName('#__bsms_studies', 'study') . ' ON ' . $db->quoteName('study.id') . ' = ' . $db->quoteName('stj.study_id')
        );
        $query->group($db->quoteName('teacher.id'));
        $query->order($db->quoteName('value') . ' ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        return $options;
    }

    /**
     * Study Books
     *
     * @return array  Returns an array of books
     *
     * @throws \Exception
     * @since 8.0.0
     */
    public static function getStudyBooks(): array
    {
        $options = [];
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->createQuery();

        $query->select(
            $db->quoteName('book.booknumber', 'value') . ', ' . $db->quoteName('book.bookname', 'text') . ', ' . $db->quoteName('book.id')
        );
        $query->from($db->quoteName('#__bsms_books', 'book'));
        $query->join(
            'INNER',
            $db->quoteName('#__bsms_studies', 'study') . ' ON ' . $db->quoteName('study.booknumber') . ' = ' . $db->quoteName('book.booknumber')
        );
        $query->group($db->quoteName('book.id'));
        $query->order($db->quoteName('value') . ' ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        foreach ($options as $option) {
            $option->text = Text::_($option->text);
        }

        return $options;
    }

    /**
     * Study Media Types
     *
     * @return array  Returns an array of books
     *
     * @throws \Exception
     * @since 8.0.0
     */
    public static function getStudyMediaTypes(): array
    {
        $options = [];
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->createQuery();

        $query->select($db->quoteName('messageType.id', 'value') . ', ' . $db->quoteName('messageType.message_type', 'text'));
        $query->from($db->quoteName('#__bsms_message_type', 'messageType'));
        $query->join(
            'INNER',
            $db->quoteName('#__bsms_studies', 'study') . ' ON ' . $db->quoteName('study.messagetype') . ' = ' . $db->quoteName('messageType.id')
        );
        $query->group($db->quoteName('messageType.id'));
        $query->order($db->quoteName('text') . ' ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        return $options;
    }

    /**
     * Study Locations
     *
     * @return array  Returns an array of books
     *
     * @throws \Exception
     * @since 8.0.0
     */
    public static function getStudyLocations(): array
    {
        $options = [];
        $db      = Factory::getContainer()->get(DatabaseInterface::class);
        $query   = $db->createQuery();

        $query->select($db->quoteName('id', 'value') . ', ' . $db->quoteName('location_text', 'text'));
        $query->from($db->quoteName('#__bsms_locations'));
        $query->order($db->quoteName('location_text') . ' ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        return $options;
    }

    /**
     * Sorting the array by Column
     *
     * @param   array   $arr  Array to sort
     * @param   string  $col  Sort column
     * @param   int     $dir  Direction to sort
     *
     * @return void applied back to the array
     *
     * @since 1.5
     */
    public static function arraySortByColumn(array &$arr, string $col, int $dir = SORT_ASC): void
    {
        $sort_col = [];

        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }

        array_multisort($sort_col, $dir, $arr);
    }

    /**
     * Debug stop
     *
     * @param   string  $msg  Message to send.
     *
     * @return void
     *
     * @throws \Exception
     *
     * @since 1.5
     */
    public static function stop(string $msg = ''): void
    {
        echo $msg;
        Factory::getApplication()->close();
    }

    /**
     * Get half of the array count
     *
     * @param   object|array  $array  Array or Object to count
     *
     * @return \stdClass
     *
     * @since 9.1.7
     */
    public static function halfarray(object|array $array): \stdClass
    {
        $count = \count($array);

        $return        = new \stdClass();
        $return->half  = floor($count / 2);
        $return->count = $count;

        return $return;
    }
}
