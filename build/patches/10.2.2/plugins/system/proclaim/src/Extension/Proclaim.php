<?php

/**
 * @package     Proclaim
 * @subpackage  plg_system_proclaim
 *
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Plugin\System\Proclaim\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\SubscriberInterface;

/**
 * System plugin for Proclaim runtime checks.
 *
 * - Displays a PHP version warning across all admin pages when the server
 *   does not meet the minimum requirement.
 * - Hides admin submenu items when Simple Mode is enabled.
 * - Rate-limits rapid POST submissions to com_proclaim controllers.
 * - Loads keyboard shortcuts on com_proclaim admin pages.
 *
 * @since  10.1.0
 */
final class Proclaim extends CMSPlugin implements SubscriberInterface
{
    /**
     * Minimum PHP version ID required by Proclaim (8.3.0 = 80300).
     *
     * @var int
     * @since 10.1.0
     */
    private const int MIN_PHP_ID = 80300;

    /**
     * Minimum PHP version as a display string for error messages.
     *
     * @var string
     * @since 10.1.0
     */
    private const string MIN_PHP = '8.3.0';

    /**
     * Default maximum POST submissions per window.
     *
     * @var int
     * @since 10.1.0
     */
    private const int DEFAULT_RATE_LIMIT_MAX = 10;

    /**
     * Default rate limit window in seconds.
     *
     * @var int
     * @since 10.1.0
     */
    private const int DEFAULT_RATE_LIMIT_WINDOW = 60;

    /**
     * Views hidden from the admin submenu when Simple Mode is enabled.
     *
     * @var string[]
     * @since 10.1.0
     */
    private const array SIMPLE_MODE_HIDDEN_VIEWS = [
        'cwmmessagetypes',
        'cwmlocations',
        'cwmtopics',
        'cwmcomments',
        'cwmtemplates',
        'cwmtemplatecodes',
    ];

    /**
     * Map of old com_biblestudy view names to new com_proclaim equivalents.
     *
     * Used by the legacy URL redirect handler to transparently redirect
     * indexed/bookmarked URLs from the old Bible Study component.
     *
     * @var array<string, string>
     * @since 10.2.0
     */
    private const array LEGACY_VIEW_MAP = [
        'sermon'               => 'cwmsermon',
        'sermons'              => 'cwmsermons',
        'teacher'              => 'cwmteacher',
        'teachers'             => 'cwmteachers',
        'seriesdisplay'        => 'cwmseriesdisplay',
        'seriesdisplays'       => 'cwmseriesdisplays',
        'landing'              => 'cwmsermons',
        'landingpage'          => 'cwmlandingpage',
        'latest'               => 'cwmlatest',
        'popup'                => 'cwmpopup',
        'terms'                => 'cwmterms',
        'seriespodcastdisplay' => 'cwmseriespodcastdisplay',
        'seriespodcastlist'    => 'cwmseriespodcastlist',
    ];

    /**
     * Query parameters to carry over from old Bible Study URLs.
     *
     * @var string[]
     * @since 10.2.0
     */
    private const array LEGACY_CARRY_PARAMS = ['id', 't', 'mid', 'Itemid', 'filter', 'limit', 'start'];

    /**
     * Task names allowed to carry over from old com_biblestudy URLs.
     *
     * The view prefix is applied automatically (e.g., 'download' on view
     * 'sermon' becomes 'cwmsermon.download').
     *
     * @var string[]
     * @since 10.2.0
     */
    private const array LEGACY_ALLOWED_TASKS = ['download'];

    /**
     * Views hidden from the admin submenu for non-admin users (require core.admin).
     *
     * @var string[]
     * @since 10.1.0
     */
    private const array ADMIN_ONLY_VIEWS = [
        'cwmadmin',
        'cwmservers',
        'cwmlocations',
        'cwmmessagetypes',
        'cwmtopics',
        'cwmcomments',
    ];

    /**
     * Cached Simple Mode state for this request (null = not yet loaded).
     *
     * @var ?bool
     * @since 10.1.0
     */
    private ?bool $simpleMode = null;

    /**
     * Cached 9.x schema detection result for this request (null = not yet checked).
     *
     * @var ?bool
     * @since 10.1.0
     */
    private ?bool $has9xSchema = null;

    /**
     * Cached admin params for this request (null = not yet loaded).
     *
     * @var ?array
     * @since 10.2.0
     */
    private ?array $adminParams = null;

    /**
     * Returns the events this plugin subscribes to.
     *
     * @return  array
     *
     * @since   10.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise'  => 'onAfterInitialise',
            'onAfterRoute'       => 'onAfterRoute',
            'onBeforeRender'     => 'onBeforeRender',
            'onAfterRender'      => 'onAfterRender',
            'onContentCleanCache' => 'onContentCleanCache',
        ];
    }

    /**
     * Intercept legacy com_biblestudy URLs before routing.
     *
     * Must run before onAfterRoute because Joomla's router throws a 404
     * for /component/biblestudy/ paths when com_biblestudy is not installed.
     *
     * @return  void
     *
     * @since   10.2.0
     */
    public function onAfterInitialise(): void
    {
        $this->redirectLegacyUrls();
    }

    /**
     * Check PHP version after routing, enforce rate limiting for POST requests.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function onAfterRoute(): void
    {
        $app = $this->getApplication();

        // Only check in the administrator application
        if (!$app->isClient('administrator')) {
            return;
        }

        // PHP version warning
        if (PHP_VERSION_ID < self::MIN_PHP_ID) {
            $app->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);

            $app->enqueueMessage(
                Text::sprintf('COM_PROCLAIM_ERROR_PHP_VERSION', self::MIN_PHP, PHP_VERSION),
                'error'
            );

            return;
        }

        // 9.x schema upgrade warning — shown on all admin pages until the upgrade wizard is run
        if ($this->has9xLegacySchema()) {
            $app->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);

            $upgradeUrl = Route::_('index.php?option=com_proclaim&view=cwmadmin', false);

            $app->enqueueMessage(
                Text::sprintf('COM_PROCLAIM_UPGRADE_9X_DETECTED', $upgradeUrl),
                'warning'
            );
        }

        // Rate limiting for POST requests to com_proclaim
        $this->checkRateLimit();
    }

    /**
     * Inject CSS/JS on admin pages: Simple Mode sidebar hiding + keyboard shortcuts.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function onBeforeRender(): void
    {
        // Component must be functional (PHP >= 8.3)
        if (PHP_VERSION_ID < self::MIN_PHP_ID) {
            return;
        }

        $option = $this->getApplication()->getInput()->getCmd('option', '');

        // Admin-only features below
        if (!$this->getApplication()->isClient('administrator')) {
            return;
        }

        if ($option === 'com_proclaim') {
            $this->getApplication()->getDocument()
                ->getWebAssetManager()
                ->useScript('com_proclaim.admin-shortcuts');
        }

        $hiddenViews = [];

        // Hide admin-only views from non-admin users (global Super Admin only)
        $user = $this->getApplication()->getIdentity();

        if ($user && !$user->authorise('core.admin')) {
            $hiddenViews = array_merge($hiddenViews, self::ADMIN_ONLY_VIEWS);
        }

        // Hide Simple Mode views
        if ($this->isSimpleModeEnabled()) {
            $hiddenViews = array_merge($hiddenViews, self::SIMPLE_MODE_HIDDEN_VIEWS);
        }

        $hiddenViews = array_unique($hiddenViews);

        if (empty($hiddenViews)) {
            return;
        }

        // Build CSS selectors targeting each hidden view's sidebar link.
        // Match both view= URLs (list views) and task= URLs (edit views like cwmadmin).
        $selectors = [];

        foreach ($hiddenViews as $view) {
            $selectors[] = 'li:has(> a[href*="view=' . $view . '"])';
            $selectors[] = 'li:has(> a[href*="task=' . $view . '."])';
        }

        $css = implode(",\n", $selectors) . ' { display: none !important; }';

        $this->getApplication()->getDocument()->getWebAssetManager()->addInlineStyle($css);
    }

    /**
     * Inject Content-Security-Policy header on frontend com_proclaim pages.
     *
     * Only active when the admin setting `enable_csp` is turned on.
     * Allows YouTube, Vimeo, and any extra domains specified in `csp_extra_sources`.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function onAfterRender(): void
    {
        $app = $this->getApplication();

        // Only apply on frontend com_proclaim pages
        if ($app->isClient('administrator')) {
            return;
        }

        if ($app->getInput()->getCmd('option', '') !== 'com_proclaim') {
            return;
        }

        $params = $this->getAdminParams();

        if (empty($params['enable_csp'])) {
            return;
        }

        // Build extra sources from admin setting (one domain per line)
        $extraRaw = trim($params['csp_extra_sources'] ?? '');
        $extra    = [];

        if ($extraRaw !== '') {
            foreach (preg_split('/[\r\n]+/', $extraRaw) as $line) {
                $line = trim($line);

                if ($line !== '') {
                    $extra[] = $line;
                }
            }
        }

        $extraStr = $extra ? ' ' . implode(' ', $extra) : '';

        $csp = implode('; ', [
            "default-src 'self'" . $extraStr,
            "script-src 'self' 'unsafe-inline'" . $extraStr,
            "style-src 'self' 'unsafe-inline'" . $extraStr,
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com" . $extraStr,
            "media-src 'self' blob: *",
            "img-src 'self' data: *",
            "font-src 'self' data:",
            "connect-src 'self'" . $extraStr,
        ]);

        $app->setHeader('Content-Security-Policy', $csp, true);
    }

    /**
     * Clear file-based YouTube video cache when Joomla cache is cleared.
     *
     * Triggered by System → Clear Cache and by component cache clear events.
     * Only clears the short-lived video cache (vcache_*) files — preserves
     * quota counters, throttle data, and last-known-video files which are
     * critical for API quota protection.
     *
     * @param   \Joomla\Event\Event  $event  The cache clean event
     *
     * @return  void
     *
     * @since   10.2.2
     */
    public function onContentCleanCache(\Joomla\Event\Event $event): void
    {
        $args  = $event->getArguments();
        $group = (string) ($args['group'] ?? ($args[0] ?? ''));

        // Clear on any Proclaim-related group, or when clearing all cache (empty group)
        if ($group === '' || str_contains($group, 'proclaim') || str_contains($group, 'mod_proclaim')) {
            $cacheClass = 'CWM\\Component\\Proclaim\\Administrator\\Helper\\CwmyoutubeFileCache';

            if (!class_exists($cacheClass)) {
                $file = JPATH_ADMINISTRATOR . '/components/com_proclaim/src/Helper/CwmyoutubeFileCache.php';

                if (is_file($file)) {
                    require_once $file;
                }
            }

            if (class_exists($cacheClass)) {
                \CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeFileCache::clearVideoCache();
            }
        }
    }

    /**
     * Redirect legacy com_biblestudy URLs to com_proclaim equivalents.
     *
     * Intercepts requests where option=com_biblestudy, maps the old view name
     * to the new Proclaim view, carries over relevant query parameters, and
     * issues a 301 (permanent) redirect. This ensures that search engine indexes,
     * bookmarks, and external links from the old Bible Study component continue
     * to work after migration.
     *
     * @return  void
     *
     * @since   10.2.0
     */
    private function redirectLegacyUrls(): void
    {
        $app = $this->getApplication();

        // Only process legacy URLs on the frontend
        if (!$app->isClient('site')) {
            return;
        }

        $input = $app->getInput();

        $oldView = '';
        $oldTask = '';
        $params  = [];

        // Method 1: Check parsed input (non-SEF or Joomla-resolved URLs)
        if ($input->getCmd('option', '') === 'com_biblestudy') {
            $oldView = strtolower($input->getCmd('view', 'sermons'));
            $oldTask = strtolower($input->getCmd('task', ''));
        }

        // Method 2: Check raw URI path for /component/biblestudy/ pattern
        // Joomla 5+ doesn't resolve this SEF pattern for uninstalled components
        if ($oldView === '') {
            $path = Uri::getInstance()->getPath();

            // Match /component/biblestudy/ or /component/biblestudy (with optional trailing segments)
            if (preg_match('#/component/biblestudy(?:/([a-z]+))?#i', $path, $matches)) {
                $oldView = !empty($matches[1]) ? strtolower($matches[1]) : 'sermons';

                // Parse query string params since Joomla may not have populated input
                $rawQuery = Uri::getInstance()->getQuery();
                parse_str($rawQuery, $params);
                $oldTask = strtolower($params['task'] ?? '');
            }
        }

        // Method 3: Check for legacy view segments in SEF URLs under a Proclaim menu
        // e.g., /resources/sermons/sermon/671-broken-but-not-destroyed/1
        if ($oldView === '') {
            $path = $path ?? Uri::getInstance()->getPath();
            $this->redirectLegacySefSegments($path);
        }

        if ($oldView === '') {
            return;
        }

        $newView = self::LEGACY_VIEW_MAP[$oldView] ?? 'cwmsermons';

        // Build the new URL with carried-over parameters
        $query = ['option' => 'com_proclaim', 'view' => $newView];

        // Map old task names (e.g., 'download' → 'cwmsermon.download')
        if ($oldTask !== '' && \in_array($oldTask, self::LEGACY_ALLOWED_TASKS, true)) {
            $query['task'] = $newView . '.' . $oldTask;
        }

        foreach (self::LEGACY_CARRY_PARAMS as $param) {
            // Check both parsed input and raw query params
            $value = $input->getString($param, '') ?: ($params[$param] ?? '');

            if ($value !== '') {
                $query[$param] = $value;
            }
        }

        // Find the correct menu item for this view so Joomla's router resolves properly
        if (!isset($query['Itemid'])) {
            $itemId = $this->findMenuItemId($newView);

            if ($itemId) {
                $query['Itemid'] = $itemId;
            }
        }

        $nonSef = 'index.php?' . http_build_query($query);

        // Try to build a SEF URL; fall back to non-SEF if router isn't ready
        try {
            $sef = Route::_($nonSef, false);
        } catch (\Exception $e) {
            $sef = $nonSef;
        }

        $app->redirect($sef, 301);
    }

    /**
     * Find a published menu item ID for the given Proclaim view.
     *
     * Searches the menu table for an item pointing to the specified view,
     * falling back to any com_proclaim menu item if no exact match is found.
     *
     * @param   string  $view  The Proclaim view name (e.g., 'cwmsermons').
     *
     * @return  int  The menu item ID, or 0 if not found.
     *
     * @since   10.2.0
     */
    private function findMenuItemId(string $view): int
    {
        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true);
            $query->select($db->quoteName('id'))
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('published') . ' = 1')
                ->where($db->quoteName('client_id') . ' = 0')
                ->where($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_proclaim&view=' . $view . '%'))
                ->setLimit(1);
            $db->setQuery($query);
            $itemId = (int) $db->loadResult();

            if ($itemId) {
                return $itemId;
            }

            // Fallback: any com_proclaim menu item
            $query = $db->getQuery(true);
            $query->select($db->quoteName('id'))
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('published') . ' = 1')
                ->where($db->quoteName('client_id') . ' = 0')
                ->where($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_proclaim%'))
                ->setLimit(1);
            $db->setQuery($query);

            return (int) $db->loadResult();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Detect and redirect legacy SEF URLs that contain old view segments
     * under a valid Proclaim menu item.
     *
     * Matches patterns like /resources/sermons/sermon/671-alias/1 where
     * "sermon" is a Proclaim 9.x / com_biblestudy view name embedded in
     * the SEF path. Extracts the numeric ID, looks up the current alias,
     * and 301-redirects to the correct modern URL.
     *
     * @param   string  $path  The raw URI path
     *
     * @return  void
     *
     * @since   10.2.0
     */
    private function redirectLegacySefSegments(string $path): void
    {
        // Build a regex alternation from legacy view names
        $legacyViews = implode('|', array_keys(self::LEGACY_VIEW_MAP));

        $app   = $this->getApplication();
        $input = $app->getInput();

        // Pattern A: /legacyview?task=download&mid=123 (no ID-alias, just query params)
        // The view name must be a standalone segment (preceded by /)
        if (preg_match('#/(' . $legacyViews . ')$#i', $path, $m)) {
            $oldView = strtolower($m[1]);
            $task    = strtolower($input->getCmd('task', ''));
            $mid     = $input->getInt('mid', 0);

            if ($task !== '' || $mid > 0) {
                $newView = self::LEGACY_VIEW_MAP[$oldView] ?? 'cwmsermons';

                // For task-based URLs (e.g. download), redirect to non-SEF URL directly.
                // Route::_() may not work at onAfterInitialise (router not initialised).
                $query = ['option' => 'com_proclaim', 'task' => $newView . '.' . $task];

                if ($mid > 0) {
                    $query['mid'] = $mid;
                }

                $itemId = $this->findMenuItemId($newView);

                if (!$itemId) {
                    $itemId = $this->findMenuItemId($newView . 's');
                }

                if ($itemId) {
                    $query['Itemid'] = $itemId;
                }

                $app->redirect(Uri::base(true) . '/index.php?' . http_build_query($query), 301);
            }

            return;
        }

        // Pattern C: /landing/filtertype/id/templateid (old landing page filter URLs)
        // e.g., /resources/sermons/landing/teacher/115/1?filter_languages=*
        //      → /resources/sermons?filter_teacher=115
        // Also handles /landing/sermons/1 (unfiltered paginated list)
        if (preg_match('#/landing/([a-z]+)/(\d+)(?:/(\d+))?$#i', $path, $m)) {
            $filterType = strtolower($m[1]);
            $filterId   = (int) $m[2];

            // Strip /landing/... from the path to get the base sermons list URL
            $cleanPath = preg_replace('#/landing/[a-z]+/\d+(?:/\d+)?$#i', '', $path);

            // Map old filter type names to current query parameter names
            $filterParamMap = [
                'teacher'     => 'filter_teacher',
                'series'      => 'filter_series',
                'book'        => 'filter_book',
                'topic'       => 'filter_topic',
                'messagetype' => 'filter_messagetype',
                'location'    => 'filter_location',
                'year'        => 'filter_year',
            ];

            $filterParam = $filterParamMap[$filterType] ?? '';

            if ($filterParam !== '' && $filterId > 0) {
                // Redirect with the specific filter applied
                $app->redirect(Uri::base(true) . $cleanPath . '?' . $filterParam . '=' . $filterId, 301);
            } else {
                // Generic landing page (e.g., /landing/sermons/1) — just redirect to sermons list
                $app->redirect(Uri::base(true) . $cleanPath, 301);
            }

            return;
        }

        // Pattern B: /legacyview/id-alias or /legacyview/id-alias/templateid
        if (!preg_match('#/(' . $legacyViews . ')/(\d+)[-:]([^/]+?)(?:/(\d+))?$#i', $path, $m)) {
            return;
        }

        $oldView = strtolower($m[1]);
        $id      = (int) $m[2];
        $alias   = $m[3];

        // Determine the database table for this view type
        $tableMap = [
            'sermon'               => '#__bsms_studies',
            'sermons'              => '#__bsms_studies',
            'latest'               => '#__bsms_studies',
            'teacher'              => '#__bsms_teachers',
            'teachers'             => '#__bsms_teachers',
            'seriesdisplay'        => '#__bsms_series',
            'seriesdisplays'       => '#__bsms_series',
            'seriespodcastdisplay' => '#__bsms_series',
            'seriespodcastlist'    => '#__bsms_series',
        ];

        $table = $tableMap[$oldView] ?? '';

        // Look up the current alias from the database
        $currentAlias = $alias;

        if ($table !== '' && $id > 0) {
            try {
                $db    = Factory::getContainer()->get(DatabaseInterface::class);
                $query = $db->getQuery(true);
                $query->select($db->quoteName('alias'))
                    ->from($db->quoteName($table))
                    ->where($db->quoteName('id') . ' = ' . $id);
                $db->setQuery($query);
                $result = $db->loadResult();

                if ($result) {
                    $currentAlias = $result;
                }
            } catch (\Exception $e) {
                // Fall through with original alias
            }
        }

        // Build the redirect URL by replacing the legacy segments in the path.
        // e.g., /resources/sermons/sermon/671-broken-but-not-destroyed/1
        //     → /resources/sermons/broken-but-not-destroyed
        // This avoids Route::_() which may not work before routing is initialised.
        $cleanPath = preg_replace(
            '#/(' . $legacyViews . ')/\d+[-:][^/]+?(?:/\d+)?$#i',
            '/' . $currentAlias,
            $path
        );

        $app->redirect(Uri::base(true) . $cleanPath, 301);
    }

    /**
     * Enforce session-based rate limiting for POST requests to com_proclaim.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function checkRateLimit(): void
    {
        $app   = $this->getApplication();
        $input = $app->getInput();

        // Only throttle POST requests to com_proclaim
        if ($input->getMethod() !== 'POST' || $input->getCmd('option', '') !== 'com_proclaim') {
            return;
        }

        // Skip AJAX/JSON requests — these are typically QuickIcon counts, not form submissions
        if ($input->getCmd('format', '') === 'json') {
            return;
        }

        // Skip XHR task endpoints — these are programmatic batch operations (Image Tools, etc.)
        $task = $input->getCmd('task', '');

        if (str_ends_with($task, 'XHR')) {
            return;
        }

        // Skip list navigation (pagination, filtering, sorting) — these are
        // read-only POST submissions that Joomla list views use by default
        if ($task === '' || $task === 'display') {
            return;
        }

        $params = $this->getAdminParams();
        $max    = (int) ($params['rate_limit_max'] ?? self::DEFAULT_RATE_LIMIT_MAX);
        $window = (int) ($params['rate_limit_window'] ?? self::DEFAULT_RATE_LIMIT_WINDOW);

        // Rate limiting disabled if max is 0
        if ($max <= 0) {
            return;
        }

        $session = $app->getSession();
        $now     = time();
        $key     = 'com_proclaim.rate_limit';

        /** @var array $timestamps */
        $timestamps = $session->get($key, []);

        // Clean up expired entries
        $timestamps = array_filter($timestamps, function (int $ts) use ($now, $window) {
            return ($now - $ts) < $window;
        });

        if (\count($timestamps) >= $max) {
            $app->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);

            $app->enqueueMessage(
                Text::sprintf('COM_PROCLAIM_RATE_LIMIT_EXCEEDED', $max, $window),
                'warning'
            );

            // Redirect back to prevent form processing
            $app->redirect(Route::_('index.php?option=com_proclaim', false));

            return;
        }

        // Record this submission
        $timestamps[] = $now;
        $session->set($key, $timestamps);
    }

    /**
     * Get admin params as an associative array.
     *
     * @return  array
     *
     * @since   10.1.0
     */
    private function getAdminParams(): array
    {
        if ($this->adminParams !== null) {
            return $this->adminParams;
        }

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_admin'))
                ->where($db->quoteName('id') . ' = 1');
            $db->setQuery($query);
            $json = $db->loadResult();

            $decoded            = $json ? json_decode($json, true, 512, JSON_THROW_ON_ERROR) : [];
            $this->adminParams  = $decoded ?: [];
        } catch (\Exception $e) {
            $this->adminParams = [];
        }

        return $this->adminParams;
    }

    /**
     * Detect whether a legacy 9.x schema exists in the database.
     *
     * Checks for `#__bsms_version` or `#__bsms_schemaversion` tables which
     * only exist in Proclaim 9.x and earlier. Uses direct DB queries to avoid
     * depending on component helper classes that may not be loaded yet.
     *
     * Result is cached for the request to avoid repeated table list lookups.
     *
     * @return  bool  True if 9.x legacy tables are detected.
     *
     * @since   10.1.0
     */
    private function has9xLegacySchema(): bool
    {
        if ($this->has9xSchema !== null) {
            return $this->has9xSchema;
        }

        try {
            $db        = Factory::getContainer()->get(DatabaseInterface::class);
            $prefix    = $db->getPrefix();
            $tableList = $db->getTableList();

            $this->has9xSchema = \in_array($prefix . 'bsms_version', $tableList, true)
                || \in_array($prefix . 'bsms_schemaversion', $tableList, true);
        } catch (\Exception $e) {
            $this->has9xSchema = false;
        }

        return $this->has9xSchema;
    }

    /**
     * Read the Simple Mode setting from the database.
     *
     * Reads directly from `#__bsms_admin` to avoid depending on
     * component classes that may not be loaded yet.
     *
     * @return  bool  True if Simple Mode is enabled.
     *
     * @since   10.1.0
     */
    private function isSimpleModeEnabled(): bool
    {
        if ($this->simpleMode !== null) {
            return $this->simpleMode;
        }

        $params           = $this->getAdminParams();
        $this->simpleMode = !empty($params['simple_mode']);

        return $this->simpleMode;
    }
}
