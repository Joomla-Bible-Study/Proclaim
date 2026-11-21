<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Health\Check;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Health\HealthCheckInterface;
use CWM\Component\Proclaim\Administrator\Health\HealthGroup;
use CWM\Component\Proclaim\Administrator\Health\HealthResult;
use CWM\Component\Proclaim\Administrator\Health\HealthStatus;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcast;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/**
 * Published podcasts missing a setting their feed needs.
 *
 * The edit form requires every one of them, but it only started to in 10.3.0,
 * and a restore, a migration or a direct database write never passes through
 * it. A podcast the build can't get past is not rebuilt at all, which the
 * scheduled task records only in its log; the others still build, with tags
 * left empty that podcast directories reject.
 *
 * @since  __DEPLOY_VERSION__
 */
final class PodcastSettingsCheck implements HealthCheckInterface
{
    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getId(): string
    {
        return 'content.podcast-settings';
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getGroup(): HealthGroup
    {
        return HealthGroup::ContentIntegrity;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getTitle(): string
    {
        return Text::_('JBS_HEALTH_PODCAST_SETTINGS');
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isPassive(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     *
     * @since  __DEPLOY_VERSION__
     */
    public function run(): HealthResult
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select($db->quoteName(array_merge(['id'], array_keys(Cwmpodcast::FEED_SETTINGS))))
            ->from($db->quoteName('#__bsms_podcast'))
            // The feed builds published podcasts only, so the others are not a finding.
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('id') . ' ASC');

        $notBuilt    = [];
        $incomplete  = [];
        $fingerprint = [];

        foreach ($db->setQuery($query)->loadObjectList() ?: [] as $podcast) {
            $missing = Cwmpodcast::missingSettings($podcast);

            if ($missing === []) {
                continue;
            }

            // The title may be what is missing, so fall back to something that names the podcast.
            $entry = ($podcast->title ?: $podcast->filename ?: Text::_('JBS_CMN_PODCAST'))
                . ' (#' . (int) $podcast->id . '): ' . implode(', ', $missing);

            if (Cwmpodcast::unbuildableSettings($podcast) !== []) {
                $notBuilt[] = $entry;
            } else {
                $incomplete[] = $entry;
            }

            // Which podcast lacks which setting, so quietening today's finding
            // does not silence a different gap tomorrow.
            $fingerprint[] = (int) $podcast->id . ':' . implode(',', array_keys($missing));
        }

        if ($fingerprint === []) {
            return new HealthResult(
                $this->getId(),
                HealthStatus::Ok,
                Text::_('JBS_HEALTH_PODCAST_SETTINGS_OK')
            );
        }

        $detail = [];

        if ($notBuilt !== []) {
            $detail[] = Text::plural('JBS_HEALTH_PODCAST_SETTINGS_NOT_BUILT_N', \count($notBuilt), implode('; ', $notBuilt));
        }

        if ($incomplete !== []) {
            $detail[] = Text::plural('JBS_HEALTH_PODCAST_SETTINGS_INCOMPLETE_N', \count($incomplete), implode('; ', $incomplete));
        }

        $detail[] = Text::_('JBS_HEALTH_PODCAST_SETTINGS_FIX');

        return new HealthResult(
            $this->getId(),
            // A Warning while a feed has stopped updating. The rest still reach
            // their subscribers, so on their own they are a Notice.
            $notBuilt !== [] ? HealthStatus::Warning : HealthStatus::Notice,
            implode(' ', $detail),
            implode(';', $fingerprint),
            'index.php?option=com_proclaim&view=cwmpodcasts',
            Text::_('JBS_HEALTH_PODCAST_SETTINGS_ACTION')
        );
    }
}
