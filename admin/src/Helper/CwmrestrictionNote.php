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

/**
 * Which links in a media file's access chain put it out of a visitor's reach.
 *
 * Exists because the restricted-media list could not answer its own question:
 * it showed two 2010 files with nothing to say why, and the answer — an
 * archived message set to Special — took a database query. The media row's
 * own level read Public; the restriction was inherited, and inherited
 * restriction is invisible unless something names it.
 *
 * Pure over the three levels and the visitor's set, so the naming rule is
 * testable on its own. The rule is the same chain rule the download route
 * applies: every level must be satisfied independently — Joomla view levels
 * are unordered sets, so there is no "most restrictive" to reduce to.
 *
 * @since  __DEPLOY_VERSION__
 */
final class CwmrestrictionNote
{
    /**
     * The chain members whose level the visitor's set does not satisfy.
     *
     * A null level means that link is absent — a media file with no message,
     * a message in no series — and constrains nothing, matching
     * Cwmdownload::isAccessible().
     *
     * @param   ?int        $mediaAccess   The media file's own level.
     * @param   ?int        $studyAccess   Its message's level.
     * @param   ?int        $seriesAccess  That message's series' level.
     * @param   array<int>  $levels        The levels the visitor holds.
     *
     * @return  list<array{member: string, level: int}>  In chain order:
     *                                                   media, message, series.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function restrictedBy(
        ?int $mediaAccess,
        ?int $studyAccess,
        ?int $seriesAccess,
        array $levels
    ): array {
        $levels = array_map('intval', $levels);
        $out    = [];

        foreach (['media' => $mediaAccess, 'message' => $studyAccess, 'series' => $seriesAccess] as $member => $level) {
            if ($level !== null && !\in_array($level, $levels, true)) {
                $out[] = ['member' => $member, 'level' => $level];
            }
        }

        return $out;
    }
}
