<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use http\Exception\RuntimeException;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use PHPUnit\Runner\Exception;

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

    /** @var integer Default template id and used to check if changed form from last query
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
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->select('*')
                ->from('#__bsms_admin')
                ->where($db->qn('id') . ' = ' . 1);
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

                // Add the current user id
                $user           = $app->getIdentity();
                $admin->user_id = $user->id;
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
        $db = Factory::getContainer()->get('DatabaseDriver');

        if (!$pk) {
            $pk = Factory::getApplication()->input->getInt('t', '1');
        }

        if (self::$templateId !== $pk || !isset(self::$templateTable)) {
            self::$templateId = $pk;
            $query            = $db->getQuery(true);
            $query->select('*')
                ->from('#__bsms_templates')
                ->where('published = ' . (int)1)
                ->where('id = ' . (int)self::$templateId);
            $db->setQuery($query);
            $template = $db->loadObject();

            // This is a fall back to default template if specified template has been deleted.
            if (!$template) {
                self::$templateId = 1;
                $query            = $db->getQuery(true);
                $query->select('*')
                    ->from('#__bsms_templates')
                    ->where('published = ' . (int)1)
                    ->where('id = ' . (int)self::$templateId);
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
        if (count($paramArray) > 0) {
            // Read the existing component value(s)
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->select('params')
                ->from('#__extensions')
                ->where('name = ' . $db->q('com_proclaim'));
            $db->setQuery($query);
            $params = json_decode($db->loadResult(), true, 512, JSON_THROW_ON_ERROR);

            // Add the new variable(s) to the existing one(s)
            foreach ($paramArray as $name => $value) {
                $params[(string)$name] = (string)$value;
            }

            // Store the combined new and existing values back as a JSON string
            $paramsString = json_encode($params, JSON_THROW_ON_ERROR);
            $query->clear();
            $query->update('#__extensions')
                ->set('params = ' . $db->q($paramsString))
                ->where('name = ' . $db->q('com_proclaim'));
            $db->setQuery($query);
            $db->execute();
        }
    }
}
