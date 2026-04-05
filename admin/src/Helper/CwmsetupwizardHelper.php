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
            'tasks'         => ['backup'],
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
            'tasks'         => ['backup', 'podcast', 'analytics'],
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
            'tasks'         => ['backup', 'podcast', 'analytics'],
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

            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_admin'))
                ->where($db->quoteName('id') . ' = 1');
            $db->setQuery($query, 0, 1);
            $json = $db->loadResult();

            if ($json) {
                $params = new Registry($json);

                return (int) $params->get('setup_wizard_complete', 0) === 0;
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
}
