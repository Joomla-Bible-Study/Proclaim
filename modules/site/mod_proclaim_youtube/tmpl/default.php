<?php

/**
 * @package     Proclaim.Component
 * @package     Proclaim.Module
 * @subpackage  mod_proclaim_youtube
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\CwmrouteHelper;
use CWM\Module\ProclaimYoutube\Site\Helper\YoutubeHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/** @var array|null $video */
/** @var string|null $embedUrl */
/** @var bool $responsive */
/** @var int $playerWidth */
/** @var int $playerHeight */
/** @var float $aspectRatio */
/** @var object|null $matchedMessage */
/** @var Registry $params */
/** @var int $serverId */
/** @var \stdClass $module */
/** @var SiteApplication $app */

$moduleId        = $module->id ?? 0;
$isLive          = $video['isLive'] ?? false;
$isUpcoming      = $video['isUpcoming'] ?? false;
$showTitle       = (bool) $params->get('show_title', 1);
$showDescription = (bool) $params->get('show_description', 0);
$showLiveBadge   = (bool) $params->get('show_live_badge', 1);
?>

<div class="mod-proclaim-youtube">
    <?php if ($video && $embedUrl) : ?>
        <?php if ($showLiveBadge) : ?>
            <?php
            $statusToken = YoutubeHelper::generateStatusToken($serverId);
            $ajaxUrl     = Uri::base() . 'index.php?option=com_ajax&module=proclaim_youtube&method=getStatus&format=json'
                . '&server_id=' . $serverId
                . '&video_id=' . urlencode($video['videoId'] ?? '')
                . '&token=' . urlencode($statusToken);
            ?>
            <div id="mod-proclaim-youtube-badge-<?php echo $moduleId; ?>" class="mod-proclaim-youtube__badge mb-2"
                 data-server-id="<?php echo $serverId; ?>"
                 data-current-video="<?php echo htmlspecialchars($video['videoId'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                 data-is-live="<?php echo $isLive ? '1' : '0'; ?>"
                 data-is-upcoming="<?php echo $isUpcoming ? '1' : '0'; ?>"
                 data-scheduled-start="<?php echo htmlspecialchars($video['scheduledStartTime'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                 data-token="<?php echo htmlspecialchars($statusToken, ENT_QUOTES, 'UTF-8'); ?>"
                 data-ajax-url="<?php echo htmlspecialchars($ajaxUrl, ENT_QUOTES, 'UTF-8'); ?>"
                 data-label-live="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_LIVE_NOW'), ENT_QUOTES, 'UTF-8'); ?>"
                 data-label-upcoming="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_UPCOMING'), ENT_QUOTES, 'UTF-8'); ?>">
                <?php if ($isLive) : ?>
                    <span class="badge bg-danger">
                        <span class="fas fa-circle me-1" aria-hidden="true"></span>
                        <?php echo Text::_('MOD_PROCLAIM_YOUTUBE_LIVE_NOW'); ?>
                    </span>
                <?php elseif ($isUpcoming) : ?>
                    <span class="badge bg-warning text-dark">
                        <span class="fas fa-clock me-1" aria-hidden="true"></span>
                        <?php echo Text::_('MOD_PROCLAIM_YOUTUBE_UPCOMING'); ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($showTitle && !empty($video['title'])) : ?>
            <h3 class="mod-proclaim-youtube__title mb-2">
                <?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?>
            </h3>
        <?php endif; ?>

        <div class="mod-proclaim-youtube__player<?php echo $responsive ? ' mod-proclaim-youtube__player--responsive' : ''; ?>">
            <?php if ($responsive) : ?>
                <div class="mod-proclaim-youtube__player-wrapper" style="padding-bottom: <?php echo $aspectRatio; ?>%;">
            <?php endif; ?>

            <iframe
                src="<?php echo $embedUrl; ?>"
                <?php if (!$responsive) : ?>
                    width="<?php echo $playerWidth; ?>"
                    height="<?php echo $playerHeight; ?>"
                <?php endif; ?>
                style="border:0;"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
                title="<?php echo htmlspecialchars($video['title'] ?? 'YouTube Video', ENT_QUOTES, 'UTF-8'); ?>"
            ></iframe>

            <?php if ($responsive) : ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($showDescription && !empty($video['truncatedDescription'])) : ?>
            <div class="mod-proclaim-youtube__description mt-2">
                <p><?php echo htmlspecialchars($video['truncatedDescription'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($matchedMessage) && !empty($matchedMessage->id)) : ?>
            <div class="mod-proclaim-youtube__message-link mt-3">
                <?php $messageRoute = CwmrouteHelper::getMessageRoute((int) $matchedMessage->id); ?>
                <a href="<?php echo Route::_($messageRoute); ?>" class="button button-2">
                    <span class="icon-book me-1" aria-hidden="true"></span>
                    <?php echo Text::_('MOD_PROCLAIM_YOUTUBE_VIEW_MESSAGE'); ?>
                </a>
                <?php if (!empty($matchedMessage->teachername)) : ?>
                    <small class="text-muted ms-2">
                        <?php echo Text::sprintf('MOD_PROCLAIM_YOUTUBE_BY_TEACHER', htmlspecialchars($matchedMessage->teachername, ENT_QUOTES, 'UTF-8')); ?>
                    </small>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php elseif ((bool) $params->get('show_no_video', 0)) : ?>
        <?php
        $noVideoMessage = $params->get('no_video_message', '');
        if (empty($noVideoMessage)) {
            $noVideoMessage = Text::_('MOD_PROCLAIM_YOUTUBE_NO_VIDEO');
        }
        ?>
        <div class="mod-proclaim-youtube__no-video alert alert-secondary">
            <?php echo htmlspecialchars($noVideoMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
</div>

<?php
$app->getDocument()->getWebAssetManager()->addInlineStyle(<<<'CSS'
.mod-proclaim-youtube__player--responsive {
    width: 100%;
}
.mod-proclaim-youtube__player--responsive .mod-proclaim-youtube__player-wrapper {
    position: relative;
    width: 100%;
    height: 0;
    overflow: hidden;
}
.mod-proclaim-youtube__player--responsive iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}
.mod-proclaim-youtube__badge .badge {
    font-size: 0.9em;
}
CSS);
?>

<?php if ($video && $embedUrl && $showLiveBadge && ($isLive || $isUpcoming)) : ?>
    <?php
    $wa = $app->getDocument()->getWebAssetManager();
    $basePollInterval = (int) $params->get('poll_interval', 120);
    // Clamp to minimum 60 seconds to protect API quota
    $basePollInterval = max(60, $basePollInterval);
    $basePollMs       = $basePollInterval * 1000;
    // Max backoff: 10 minutes
    $maxPollMs = 600000;
    // Configurable poll window: hours before/after the scheduled event start
    $pollWindowBefore = (int) $params->get('poll_window_before', 2);
    $pollWindowAfter  = (int) $params->get('poll_window_after', 1);

    $inlineScript = <<<JS
(function() {
    'use strict';

    const badgeEl = document.getElementById('mod-proclaim-youtube-badge-{$moduleId}');
    if (!badgeEl) return;

    let wasLive = badgeEl.dataset.isLive === '1';
    let wasUpcoming = badgeEl.dataset.isUpcoming === '1';
    const labelLive = badgeEl.dataset.labelLive;
    const labelUpcoming = badgeEl.dataset.labelUpcoming;

    const basePollInterval = {$basePollMs};
    const maxPollInterval = {$maxPollMs};
    let currentInterval = basePollInterval;
    let unchangedCount = 0;
    const ajaxUrl = badgeEl.dataset.ajaxUrl;

    // Poll window: only poll within N hours before / after the scheduled start
    const pollWindowBeforeMs = {$pollWindowBefore} * 3600000;
    const pollWindowAfterMs = {$pollWindowAfter} * 3600000;
    let scheduledStart = badgeEl.dataset.scheduledStart ? new Date(badgeEl.dataset.scheduledStart).getTime() : 0;

    function isWithinPollWindow() {
        // Always poll if already live (status transitions need detection)
        if (wasLive) return true;
        // If no scheduled start time is known, poll normally (can't gate without data)
        if (!scheduledStart) return true;
        var now = Date.now();
        var windowOpen = scheduledStart - pollWindowBeforeMs;
        var windowClose = scheduledStart + pollWindowAfterMs;
        return now >= windowOpen && now <= windowClose;
    }

    function updateBadge(isLive, isUpcoming) {
        var html = '';
        if (isLive) {
            html = '<span class="badge bg-danger"><span class="fas fa-circle me-1" aria-hidden="true"></span>' + labelLive + '</span>';
        } else if (isUpcoming) {
            html = '<span class="badge bg-warning text-dark"><span class="fas fa-clock me-1" aria-hidden="true"></span>' + labelUpcoming + '</span>';
        }
        badgeEl.innerHTML = html;
    }

    function getBackoffInterval() {
        var multiplier = Math.pow(2, Math.floor(unchangedCount / 3));
        return Math.min(basePollInterval * multiplier, maxPollInterval);
    }

    function getWindowOpenDelay() {
        // If outside the poll window, return ms until the window opens
        if (!scheduledStart) return 0;
        var windowOpen = scheduledStart - pollWindowBeforeMs;
        var delay = windowOpen - Date.now();
        return delay > 0 ? delay : 0;
    }

    function schedulePoll() {
        currentInterval = getBackoffInterval();
        if (!isWithinPollWindow()) {
            // Schedule to wake up when the poll window opens
            var delay = getWindowOpenDelay();
            if (delay > 0) {
                pollTimer = setTimeout(checkStatus, delay);
                return;
            }
            // Window has closed — stop polling entirely
            return;
        }
        pollTimer = setTimeout(checkStatus, currentInterval);
    }

    function checkStatus() {
        if (!isWithinPollWindow()) {
            schedulePoll();
            return;
        }

        fetch(ajaxUrl, { method: 'GET', headers: { 'Accept': 'application/json' } })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                // Update scheduledStartTime if the server returns a newer value
                if (data.scheduledStartTime) {
                    scheduledStart = new Date(data.scheduledStartTime).getTime();
                }

                // Stop polling if daily quota is exhausted
                if (typeof data.quotaRemaining === 'number' && data.quotaRemaining <= 0) {
                    console.log('YouTube API quota exhausted — pausing polling');
                    return;
                }

                var isLive = data.isLive;
                var isUpcoming = data.isUpcoming;
                if (isLive !== wasLive || isUpcoming !== wasUpcoming) {
                    unchangedCount = 0;
                    updateBadge(isLive, isUpcoming);
                    if (isLive && !wasLive) {
                        wasLive = isLive;
                        wasUpcoming = isUpcoming;
                        setTimeout(function() { window.location.reload(); }, 2000);
                        return;
                    }
                    if (!isLive && !isUpcoming) {
                        return;
                    }
                    wasLive = isLive;
                    wasUpcoming = isUpcoming;
                } else {
                    unchangedCount++;
                }
                schedulePoll();
            }
        })
        .catch(function(error) {
            console.log('YouTube status check failed:', error);
            unchangedCount++;
            schedulePoll();
        });
    }

    // Start: if within window poll immediately, otherwise schedule for window open
    var pollTimer;
    if (isWithinPollWindow()) {
        pollTimer = setTimeout(checkStatus, currentInterval);
    } else {
        var delay = getWindowOpenDelay();
        if (delay > 0) {
            pollTimer = setTimeout(checkStatus, delay);
        }
        // else: window has already closed, no polling needed
    }

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearTimeout(pollTimer);
        } else {
            unchangedCount = 0;
            currentInterval = basePollInterval;
            if (isWithinPollWindow()) {
                checkStatus();
            } else {
                schedulePoll();
            }
        }
    });
})();
JS;

    $wa->addInlineScript($inlineScript, [], ['name' => 'mod_proclaim_youtube_status_' . $moduleId]);
endif; ?>
