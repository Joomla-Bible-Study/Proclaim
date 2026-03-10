<?php

/**
 * Proclaim Migration Bridge - Installer Script
 *
 * Disables all Proclaim/BibleStudy 9.x plugins and modules so that
 * Joomla can be safely migrated from 3.x to 4/5 without fatal errors
 * from incompatible extension code.
 *
 * IMPORTANT: This file must remain compatible with PHP 5.6 through 8.2+
 * (Joomla 3.x sites). No typed properties, union types, named arguments,
 * match expressions, or dynamic properties on custom classes.
 *
 * @package    Proclaim
 * @subpackage MigrationBridge
 * @since      10.1.0
 * @copyright  (C) 2026 CWM Team
 * @license    GPL-2.0-or-later
 */

\defined('_JEXEC') or die;

/**
 * Migration bridge installer script.
 *
 * @since 1.0.0
 */
class ProclaimBridgeInstallerScript
{
    /**
     * Extensions to disable.
     *
     * Each entry: array(type, element, folder|null)
     *   - type: 'plugin' or 'module'
     *   - element: the extension element name (plugins use bare name, modules use mod_ prefix)
     *   - folder: plugin group or null for modules
     *
     * @var   array
     * @since 1.0.0
     */
    private $extensions = [
        // --- Legacy BibleStudy 9.x extensions ---
        ['plugin', 'biblestudy',        'finder'],
        ['plugin', 'biblestudysearch',  'search'],
        ['plugin', 'jbspodcast',        'system'],
        ['plugin', 'jbsbackup',         'system'],
        ['module', 'mod_biblestudy',          null],
        ['module', 'mod_biblestudy_podcast',  null],

        // --- Proclaim 9.x extensions (same names as 10.x but incompatible) ---
        ['plugin', 'proclaim',          'finder'],
        ['plugin', 'proclaim',          'system'],
        ['module', 'mod_proclaim',            null],
        ['module', 'mod_proclaim_podcast',    null],
        ['module', 'mod_proclaim_youtube',    null],
        ['module', 'mod_proclaimicon',        null],
    ];

    /**
     * Runs after install/update.
     *
     * @param   string  $type    Install type (install, update, discover_install)
     * @param   object  $parent  Installer adapter
     *
     * @return  void
     * @since   1.0.0
     */
    public function postflight($type, $parent)
    {
        try {
            $db  = JFactory::getDbo();
            $app = JFactory::getApplication();
        } catch (Exception $e) {
            return;
        }

        $disabled      = [];
        $alreadyOff    = [];
        $notFound      = [];
        $errors        = [];

        foreach ($this->extensions as $ext) {
            $extType = $ext[0];
            $element = $ext[1];
            $folder  = $ext[2];

            // Build a human-readable label
            if ($folder !== null) {
                $label = $extType . ' / ' . $folder . ' / ' . $element;
            } else {
                $label = $extType . ' / ' . $element;
            }

            try {
                // Look up the extension
                $query = $db->getQuery(true)
                    ->select($db->qn(['extension_id', 'enabled']))
                    ->from($db->qn('#__extensions'))
                    ->where($db->qn('element') . ' = ' . $db->q($element))
                    ->where($db->qn('type') . ' = ' . $db->q($extType));

                if ($folder !== null) {
                    $query->where($db->qn('folder') . ' = ' . $db->q($folder));
                }

                $db->setQuery($query);
                $row = $db->loadObject();

                if (!$row) {
                    $notFound[] = $label;
                    continue;
                }

                if ((int) $row->enabled === 0) {
                    $alreadyOff[] = $label;
                    continue;
                }

                // Disable it
                $update = $db->getQuery(true)
                    ->update($db->qn('#__extensions'))
                    ->set($db->qn('enabled') . ' = 0')
                    ->where($db->qn('extension_id') . ' = ' . (int) $row->extension_id);

                $db->setQuery($update);
                $db->execute();

                $disabled[] = $label;
            } catch (Exception $e) {
                $errors[] = $label . ' (' . $e->getMessage() . ')';
            }
        }

        // Build summary message
        $lines   = [];
        $lines[] = '<strong>Proclaim Migration Bridge</strong>';

        if (!empty($disabled)) {
            $lines[] = '<br><strong>Disabled (' . \count($disabled) . '):</strong>';
            foreach ($disabled as $item) {
                $lines[] = '&nbsp;&nbsp;&bull; ' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            }
        }

        if (!empty($alreadyOff)) {
            $lines[] = '<br><strong>Already disabled (' . \count($alreadyOff) . '):</strong>';
            foreach ($alreadyOff as $item) {
                $lines[] = '&nbsp;&nbsp;&bull; ' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            }
        }

        if (!empty($notFound)) {
            $lines[] = '<br><strong>Not installed (' . \count($notFound) . '):</strong>';
            foreach ($notFound as $item) {
                $lines[] = '&nbsp;&nbsp;&bull; ' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            }
        }

        if (!empty($errors)) {
            $lines[] = '<br><strong style="color:red">Errors (' . \count($errors) . '):</strong>';
            foreach ($errors as $item) {
                $lines[] = '&nbsp;&nbsp;&bull; ' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            }
        }

        $totalDisabled = \count($disabled) + \count($alreadyOff);
        if ($totalDisabled > 0) {
            $lines[] = '<br>You can now safely migrate Joomla to version 4/5.';
            $lines[] = 'After migration, install <strong>Proclaim 10.x</strong> to register the new extensions.';
        }

        $msgType = !empty($errors) ? 'warning' : 'message';

        try {
            $app->enqueueMessage(implode('<br>', $lines), $msgType);
        } catch (Exception $e) {
            // Silently fail if messaging not available
        }
    }
}
