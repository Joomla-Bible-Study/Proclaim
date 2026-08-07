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

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Extension;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * This is for Retrieving Admin and Template db
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 *
 * @property $template->params Registry
 */
class Cwmparams
{
    /**
     * Extension Name
     *
     * @var string
     *
     * @since 1.5
     */
    public static string $extension = 'com_proclaim';

    /** @var  object Admin Table
     *
     * @since 1.5
     */
    public static object $admin;

    /** @var  object Template Table
     *
     * @since 1.5
     */
    public static object $templateTable;

    /** @var int Default template id and used to check if changed form from last query
     *
     * @since 1.5
     */
    public static int $templateId = 1;

    /**
     * Gets the settings from Admin
     *
     * @return object Return Admin table
     *
     * @since 7.0
     */
    public static function getAdmin(): object
    {
        if (!isset(self::$admin)) {
            // Initialised before the try: with no application (CLI, early
            // bootstrap) $app is never assigned, and the enqueueMessage() below
            // would then raise a second error while reporting the first.
            $app = null;

            try {
                $app = Factory::getApplication();
            } catch (\Exception $e) {
                echo $e->getMessage();
            }

            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery();
            $query->select('*')
                ->from($db->quoteName('#__bsms_admin'))
                ->where($db->quoteName('id') . ' = ' . 1);
            $db->setQuery($query);
            $admin = $db->loadObject();

            // The singleton #__bsms_admin row can be absent after an
            // interrupted restore or a migration that failed to reseed it.
            // loadObject() then returns null, and assigning that to the
            // non-nullable `public static object $admin` raised
            // "TypeError: Cannot assign null to property ... of type object",
            // breaking all 12+ Cwmparams::getAdmin()->params callers. Fall
            // back to a usable shape instead, mirroring what
            // getTemplateparams() already does for a deleted template.
            // See #1567.
            if (!$admin) {
                $admin         = new \stdClass();
                $admin->id     = 1;
                $admin->params = new Registry();

                $app?->enqueueMessage(
                    Text::_('JBS_CMN_ADMIN_ROW_MISSING'),
                    'warning'
                );
            } elseif (isset($admin->params)) {
                $registry = new Registry();

                // A malformed params column must not fatal the whole request.
                try {
                    $registry->loadString($admin->params);
                } catch (\Exception $e) {
                    $msg = $e->getMessage();
                    $app?->enqueueMessage('Can\'t load Admin Params - ' . $msg, 'error');
                }

                $admin->params = $registry;
            }

            // Add the current user id. getIdentity() may return null in CLI or
            // pre-auth contexts, so fall back to Guest (0). Set outside the
            // params branch above so a row with no params column still gets it.
            $user           = $app?->getIdentity();
            $admin->user_id = (int) ($user?->id ?? 0);

            self::$admin = $admin;
        }

        return self::$admin;
    }

    /**
     * Get Template Params
     *
     * @param   ?int  $pk  Id of Template to look for
     *
     * @return object Return active template info
     *
     * @throws \Exception
     * @since 7.0
     */
    public static function getTemplateparams(?int $pk = null): object
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$pk) {
            $pk = Factory::getApplication()->getInput()->getInt('t', '1');
        }

        if (self::$templateId !== $pk || !isset(self::$templateTable)) {
            self::$templateId = $pk;
            $query            = $db->createQuery();
            $query->select('*')
                ->from($db->quoteName('#__bsms_templates'))
                ->where($db->quoteName('published') . ' = 1')
                ->where($db->quoteName('id') . ' = ' . (int) self::$templateId);
            $db->setQuery($query);
            $template = $db->loadObject();

            // This is a fallback to the default template if the specified template has been deleted.
            if (!$template) {
                self::$templateId = 1;
                $query            = $db->createQuery();
                $query->select('*')
                    ->from($db->quoteName('#__bsms_templates'))
                    ->where($db->quoteName('published') . ' = 1')
                    ->where($db->quoteName('id') . ' = ' . (int) self::$templateId);
                $db->setQuery($query);
                $template = $db->loadObject();
            }

            if ($template) {
                $registry = new Registry();
                $registry->loadString($template->params);
                $template->params = $registry;
            } else {
                $template         = new \stdClass();
                $template->params = new Registry();
            }

            self::$templateTable = $template;
        }

        return self::$templateTable;
    }

    /**
     * Update Component Params
     *
     * @param   array  $paramArray  Array ('name' => 'params')
     *
     * @return void
     *
     * @throws \Exception
     * @since 9.1.5
     */
    public static function setCompParams(array $paramArray): void
    {
        if (\count($paramArray) === 0) {
            return;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        /** @var Extension $table */
        $table = new Extension($db);

        // Identify the row the way ComponentHelper does — element + type. The
        // previous implementation matched on `name`, which holds the manifest
        // <name> and is neither guaranteed to equal the element nor unique across
        // extension types.
        $id = (int) $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_proclaim'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
        )->loadResult();

        if ($id === 0 || !$table->load($id)) {
            throw new \RuntimeException(
                'Cannot save Proclaim component params: no com_proclaim component row in #__extensions.'
            );
        }

        // Registry does not tolerate every malformed params column. It shrugs
        // off a value it cannot recognise as JSON at all, but a truncated write
        // (disk full, interrupted store(), a hand-edit) leaves a string that
        // still starts with '{', and Json::stringToObject() throws
        // \RuntimeException('Error decoding JSON data: ...') for exactly that.
        //
        // The only caller is CwmlicenseController::accept(), which has no
        // try/catch, so an uncaught throw here hard-blocks every admin action
        // gated behind licence acceptance with no recovery short of editing the
        // database by hand. Fall back to an empty Registry so the write can
        // proceed and repair the row.
        try {
            $params = new Registry($table->params);
        } catch (\RuntimeException $e) {
            $params = new Registry();

            // Not silent: the fallback discards whatever was in the corrupt
            // blob, so the operator needs to know their stored settings were
            // unreadable and are being replaced rather than merged.
            Log::add(
                'com_proclaim params were unreadable and have been reset: ' . $e->getMessage(),
                Log::WARNING,
                'com_proclaim'
            );

            try {
                Factory::getApplication()->enqueueMessage(
                    Text::_('JBS_CMN_PARAMS_CORRUPT_RESET'),
                    'warning'
                );
            } catch (\Exception) {
                // No application (CLI) -- the log entry above still stands.
            }
        }

        foreach ($paramArray as $name => $value) {
            $params->set((string) $name, (string) $value);
        }

        $table->params = $params->toString();

        if (!$table->store()) {
            throw new \RuntimeException('Cannot save Proclaim component params: ' . $table->getError());
        }

        // Clear the component cache — the step whose absence caused the outage.
        //
        // ComponentHelper::load() caches every component's params in the _system
        // group, so ComponentHelper::getParams() keeps serving the pre-write value
        // until that cache is cleared. Writing straight to the database without
        // this looks like it worked and changes nothing observable.
        //
        // The symptom was severe: accepting the licence stored the flag, showed
        // its success message, redirected — and the dispatcher, reading the stale
        // cache, sent the administrator back to the licence screen. No error
        // anywhere, and no way out of the loop.
        //
        // com_config does the same thing after storing this table
        // (ComponentModel::save() -> cleanCache('_system')).
        self::cleanComponentCache();
    }

    /**
     * Clear the _system cache group so component params are re-read.
     *
     * @return  void
     *
     * @since   10.3.5
     */
    private static function cleanComponentCache(): void
    {
        try {
            Factory::getContainer()
                ->get(CacheControllerFactoryInterface::class)
                ->createCacheController('callback', ['defaultgroup' => '_system'])
                ->clean();
        } catch (\Throwable) {
            // A cache backend that cannot be cleared must not fail the save. The
            // value is committed either way; the worst case is that readers see
            // the stale cached copy until it expires.
        }
    }
}
