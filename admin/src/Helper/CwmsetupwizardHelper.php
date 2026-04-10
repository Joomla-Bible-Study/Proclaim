<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Setup Wizard helper — preset definitions and first-run detection.
 *
 * @since  10.3.0
 */
class CwmsetupwizardHelper
{
    /**
     * Ministry style preset definitions.
     *
     * Each preset configures simple_mode, default features, and recommended
     * server types for the wizard's Step 1 selection.
     *
     * @var    array
     * @since  10.3.0
     */
    public const PRESETS = [
        'simple' => [
            'label'         => 'JBS_WIZARD_STYLE_SIMPLE',
            'description'   => 'JBS_WIZARD_STYLE_SIMPLE_DESC',
            'icon'          => 'fa-book-bible',
            'simple_mode'   => 1,
            'use_series'    => true,
            'use_topics'    => false,
            'use_locations' => false,
            'servers'       => ['Local'],
            'tasks'         => ['analytics'],
        ],
        'full_media' => [
            'label'         => 'JBS_WIZARD_STYLE_FULL',
            'description'   => 'JBS_WIZARD_STYLE_FULL_DESC',
            'icon'          => 'fa-photo-film',
            'simple_mode'   => 0,
            'use_series'    => true,
            'use_topics'    => true,
            'use_locations' => false,
            'servers'       => ['Local', 'Youtube', 'Vimeo', 'Direct'],
            'tasks'         => ['analytics'],
        ],
        'multi_campus' => [
            'label'         => 'JBS_WIZARD_STYLE_CAMPUS',
            'description'   => 'JBS_WIZARD_STYLE_CAMPUS_DESC',
            'icon'          => 'fa-church',
            'simple_mode'   => 0,
            'use_series'    => true,
            'use_topics'    => true,
            'use_locations' => true,
            'servers'       => ['Local', 'Youtube', 'Vimeo', 'Direct'],
            'tasks'         => ['analytics'],
        ],
    ];

    /**
     * Check whether the setup wizard should be shown on the control panel.
     *
     * Returns true only for admin users on a fresh install (wizard not yet completed).
     *
     * @return  bool
     *
     * @since   10.3.0
     */
    public static function shouldShowWizard(): bool
    {
        try {
            $user = Factory::getApplication()->getIdentity();

            if (!$user || !$user->authorise('core.admin', 'com_proclaim')) {
                return false;
            }

            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // Check if wizard was already completed or dismissed
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_admin'))
                ->where($db->quoteName('id') . ' = 1');
            $db->setQuery($query, 0, 1);
            $json = $db->loadResult();

            if ($json) {
                $params = new Registry($json);

                // Explicitly completed — never show again
                if ((int) $params->get('setup_wizard_complete', 0) === 1) {
                    return false;
                }
            }

            // Distinguish fresh install from upgrade: if studies exist,
            // this is an upgrade and the wizard should not auto-show.
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_studies'));
            $db->setQuery($query);

            if ((int) $db->loadResult() > 0) {
                return false;
            }

            // No admin record yet — show wizard
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get a preset definition by key.
     *
     * @param   string  $key  Preset key (simple, full_media, multi_campus)
     *
     * @return  array|null  Preset data or null if not found
     *
     * @since   10.3.0
     */
    public static function getPreset(string $key): ?array
    {
        return self::PRESETS[$key] ?? null;
    }

    /**
     * Check whether the post-wizard "Getting Started" checklist should show.
     *
     * Shows after the wizard is complete but the checklist hasn't been dismissed.
     *
     * @return  bool
     *
     * @since   10.3.0
     */
    public static function shouldShowChecklist(): bool
    {
        try {
            $user = Factory::getApplication()->getIdentity();

            if (!$user || !$user->authorise('core.admin', 'com_proclaim')) {
                return false;
            }

            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_admin'))
                ->where($db->quoteName('id') . ' = 1');
            $db->setQuery($query, 0, 1);
            $json = $db->loadResult();

            if (!$json) {
                return false;
            }

            $params = new Registry($json);

            // Only show after wizard is complete and before checklist is dismissed
            return (int) $params->get('setup_wizard_complete', 0) === 1
                && (int) $params->get('setup_checklist_dismissed', 0) === 0;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Build the post-wizard checklist items based on ministry style.
     *
     * Each item checks the database to determine if it's been completed.
     *
     * @return  array  Array of checklist items [{key, label, done, link}, ...]
     *
     * @since   10.3.0
     */
    public static function getChecklistItems(): array
    {
        try {
            $db     = Factory::getContainer()->get(DatabaseInterface::class);
            $query  = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_admin'))
                ->where($db->quoteName('id') . ' = 1');
            $db->setQuery($query, 0, 1);
            $params = new Registry($db->loadResult() ?: '{}');

            $style = 'simple';

            if ((int) $params->get('simple_mode', 0) === 0) {
                $style = $params->get('enable_location_filtering', 0) ? 'multi_campus' : 'full_media';
            }

            $items = [];

            // Check if default teacher has been edited (has more than just a name)
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('teachername'), $db->quoteName('short'), $db->quoteName('image')])
                ->from($db->quoteName('#__bsms_teachers'))
                ->where($db->quoteName('published') . ' = 1')
                ->order($db->quoteName('id') . ' ASC');
            $db->setQuery($query, 0, 1);
            $teacher   = $db->loadObject();
            $teacherId = $teacher ? (int) $teacher->id : 0;

            $teacherComplete = $teacher && (!empty($teacher->short) || !empty($teacher->image));
            $items[]         = [
                'key'   => 'teacher_profile',
                'label' => 'JBS_CHECKLIST_TEACHER_PROFILE',
                'done'  => $teacherComplete,
                'link'  => $teacherId ? 'index.php?option=com_proclaim&task=cwmteacher.edit&id=' . $teacherId : 'index.php?option=com_proclaim&task=cwmteacher.add',
            ];

            // Check if a real message exists (not sample)
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_studies'))
                ->where($db->quoteName('published') . ' >= 0')
                ->where($db->quoteName('alias') . ' != ' . $db->quote('welcome-to-proclaim'));
            $db->setQuery($query);
            $hasRealMessage = (int) $db->loadResult() > 0;

            $items[] = [
                'key'   => 'first_message',
                'label' => 'JBS_CHECKLIST_FIRST_MESSAGE',
                'done'  => $hasRealMessage,
                'link'  => 'index.php?option=com_proclaim&view=cwmmessage&layout=wizard',
            ];

            // Check if a series with image exists (Full/Multi-Campus)
            if ($style !== 'simple') {
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__bsms_series'))
                    ->where($db->quoteName('published') . ' = 1')
                    ->where($db->quoteName('series_thumbnail') . ' IS NOT NULL')
                    ->where($db->quoteName('series_thumbnail') . ' != ' . $db->quote(''));
                $db->setQuery($query);
                $hasSeriesImage = (int) $db->loadResult() > 0;

                $items[] = [
                    'key'   => 'series_image',
                    'label' => 'JBS_CHECKLIST_SERIES_IMAGE',
                    'done'  => $hasSeriesImage,
                    'link'  => 'index.php?option=com_proclaim&view=cwmseries',
                ];
            }

            // Check if podcast is configured (if enabled)
            if ($style !== 'simple') {
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__bsms_podcast'))
                    ->where($db->quoteName('published') . ' = 1');
                $db->setQuery($query);
                $hasPodcast = (int) $db->loadResult() > 0;

                if (!$hasPodcast && $params->get('enable_podcast', 0)) {
                    $items[] = [
                        'key'   => 'podcast_setup',
                        'label' => 'JBS_CHECKLIST_PODCAST_SETUP',
                        'done'  => false,
                        'link'  => 'index.php?option=com_proclaim&task=cwmpodcast.add',
                    ];
                }
            }

            // Multi-Campus: check location wizard completed
            if ($style === 'multi_campus') {
                $mapping     = $params->get('location_group_mapping', '');
                $hasMappings = !empty($mapping) && $mapping !== '{}' && $mapping !== '[]';

                $items[] = [
                    'key'   => 'location_wizard',
                    'label' => 'JBS_CHECKLIST_LOCATION_WIZARD',
                    'done'  => $hasMappings || (int) $params->get('location_system_dismissed', 0) === 1,
                    'link'  => 'index.php?option=com_proclaim&view=cwmlocationwizard',
                ];
            }

            // View your site
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_proclaim&view=cwmsermons%'))
                ->where($db->quoteName('client_id') . ' = 0')
                ->where($db->quoteName('published') . ' = 1');
            $db->setQuery($query, 0, 1);
            $menuItemId = (int) $db->loadResult();

            if ($menuItemId > 0) {
                $items[] = [
                    'key'      => 'view_site',
                    'label'    => 'JBS_CHECKLIST_VIEW_SITE',
                    'done'     => false,
                    'link'     => 'index.php?Itemid=' . $menuItemId,
                    'external' => true,
                ];
            }

            return $items;
        } catch (\Throwable) {
            return [];
        }
    }
}
