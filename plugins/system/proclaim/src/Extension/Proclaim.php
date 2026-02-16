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
     * Minimum PHP version required by Proclaim.
     *
     * @var string
     * @since 10.1.0
     */
    private const MIN_PHP = '8.3.0';

    /**
     * Default maximum POST submissions per window.
     *
     * @var int
     * @since 10.1.0
     */
    private const DEFAULT_RATE_LIMIT_MAX = 10;

    /**
     * Default rate limit window in seconds.
     *
     * @var int
     * @since 10.1.0
     */
    private const DEFAULT_RATE_LIMIT_WINDOW = 60;

    /**
     * Views hidden from the admin submenu when Simple Mode is enabled.
     *
     * @var string[]
     * @since 10.1.0
     */
    private const SIMPLE_MODE_HIDDEN_VIEWS = [
        'cwmmessagetypes',
        'cwmlocations',
        'cwmtopics',
        'cwmcomments',
        'cwmtemplates',
        'cwmtemplatecodes',
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
     * Returns the events this plugin subscribes to.
     *
     * @return  array
     *
     * @since   10.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterRoute'   => 'onAfterRoute',
            'onBeforeRender' => 'onBeforeRender',
            'onAfterRender'  => 'onAfterRender',
        ];
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
        if (version_compare(PHP_VERSION, self::MIN_PHP, '<')) {
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
        // Only apply in the administrator application
        if (!$this->getApplication()->isClient('administrator')) {
            return;
        }

        // Component must be functional (PHP >= 8.3)
        if (version_compare(PHP_VERSION, self::MIN_PHP, '<')) {
            return;
        }

        // Load keyboard shortcuts on any com_proclaim admin page
        $option = $this->getApplication()->getInput()->getCmd('option', '');

        if ($option === 'com_proclaim') {
            $this->getApplication()->getDocument()
                ->getWebAssetManager()
                ->useScript('com_proclaim.admin-shortcuts');
        }

        if (!$this->isSimpleModeEnabled()) {
            return;
        }

        // Build CSS selectors targeting each hidden view's sidebar link
        $selectors = [];

        foreach (self::SIMPLE_MODE_HIDDEN_VIEWS as $view) {
            $selectors[] = 'li:has(> a[href*="view=' . $view . '"])';
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
        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_admin'))
                ->where($db->quoteName('id') . ' = 1');
            $db->setQuery($query);
            $json = $db->loadResult();

            return $json ? (json_decode($json, true) ?: []) : [];
        } catch (\Exception $e) {
            return [];
        }
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
            $db        = Factory::getContainer()->get('DatabaseDriver');
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
