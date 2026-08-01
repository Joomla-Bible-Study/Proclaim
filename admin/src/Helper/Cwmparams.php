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

            if (isset($admin->params)) {
                $registry = new Registry();

                // Used to Catch Jason Error's
                try {
                    $registry->loadString($admin->params);
                } catch (\Exception $e) {
                    $msg = $e->getMessage();
                    $app->enqueueMessage('Can\'t load Admin Params - ' . $msg, 'error');
                }

                $admin->params = $registry;

                // Add the current user id. getIdentity() may return null
                // in CLI / pre-auth contexts, so fall back to Guest (0).
                $user           = $app->getIdentity();
                $admin->user_id = (int) ($user?->id ?? 0);
            }

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

        // Registry tolerates an empty or malformed params column; a raw
        // json_decode() with JSON_THROW_ON_ERROR turned that recoverable state
        // into a fatal.
        $params = new Registry($table->params);

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
            // value is committed; the worst case is the old behaviour.
        }
    }
}
