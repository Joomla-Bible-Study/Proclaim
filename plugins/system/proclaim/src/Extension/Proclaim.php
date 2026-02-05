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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

/**
 * System plugin for Proclaim runtime checks.
 *
 * Displays a PHP version warning across all admin pages
 * when the server does not meet the minimum requirement.
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
     * Returns the events this plugin subscribes to.
     *
     * @return  array
     *
     * @since   10.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterRoute' => 'onAfterRoute',
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

        $this->getApplication()->getLanguage()->load('plg_system_proclaim', JPATH_ADMINISTRATOR);

        $this->getApplication()->enqueueMessage(
            Text::sprintf('PLG_SYSTEM_PROCLAIM_PHP_VERSION_WARNING', self::MIN_PHP, PHP_VERSION),
            'error'
        );
    }
}
