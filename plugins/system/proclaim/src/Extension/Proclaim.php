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
use Joomla\Event\SubscriberInterface;

/**
 * System plugin for Proclaim runtime checks.
 *
 * - Displays a PHP version warning across all admin pages when the server
 *   does not meet the minimum requirement.
 * - Hides admin submenu items when Simple Mode is enabled.
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
        ];
    }

    /**
     * Check PHP version after routing and enqueue a warning if too low.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function onAfterRoute(): void
    {
        // Only check in the administrator application
        if (!$this->getApplication()->isClient('administrator')) {
            return;
        }

        // PHP version meets requirement — nothing to do
        if (version_compare(PHP_VERSION, self::MIN_PHP, '>=')) {
            return;
        }

        $this->getApplication()->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);

        $this->getApplication()->enqueueMessage(
            Text::sprintf('COM_PROCLAIM_ERROR_PHP_VERSION', self::MIN_PHP, PHP_VERSION),
            'error'
        );
    }

    /**
     * Inject CSS to hide admin submenu items when Simple Mode is enabled.
     *
     * Uses display:none on sidebar links matching hidden views.
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

        if (!$this->isSimpleModeEnabled()) {
            return;
        }

        // Build CSS selectors targeting each hidden view's sidebar link
        $selectors = [];

        foreach (self::SIMPLE_MODE_HIDDEN_VIEWS as $view) {
            $selectors[] = 'li:has(> a[href*="view=' . $view . '"])';
        }

        $css = implode(",\n", $selectors) . ' { display: none !important; }';

        $this->getApplication()->getDocument()->addStyleDeclaration($css);
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

        $this->simpleMode = false;

        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_admin'))
                ->where($db->quoteName('id') . ' = 1');
            $db->setQuery($query);
            $json = $db->loadResult();

            if ($json) {
                $params           = json_decode($json, true);
                $this->simpleMode = !empty($params['simple_mode']);
            }
        } catch (\Exception $e) {
            // Table may not exist (fresh install in progress) — default to off
        }

        return $this->simpleMode;
    }
}
