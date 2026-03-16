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

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
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
/** @var array $fallbackSermons */
/** @var \stdClass|null $fallbackTemplate */
/** @var Registry|null $fallbackParams */

$app->getDocument()->getWebAssetManager()->useStyle('com_proclaim.mod-youtube');
$moduleId        = $module->id ?? 0;
$isLive          = $video['isLive'] ?? false;
$isUpcoming      = $video['isUpcoming'] ?? false;
$showTitle       = (bool) $params->get('show_title', 1);
$showDescription = (bool) $params->get('show_description', 0);
$showLiveBadge   = (bool) $params->get('show_live_badge', 1);
?>

<div class="com-proclaim mod-proclaim-youtube">
    <?php if ($video && $embedUrl) : ?>
        <?php if ($showLiveBadge) : ?>
            <?php
            $statusToken = YoutubeHelper::generateStatusToken($serverId);
            $ajaxUrl     = Uri::base() . 'index.php?option=com_ajax&module=proclaim_youtube&method=getStatus&format=json'
                . '&server_id=' . $serverId
                . '&video_id=' . urlencode($video['videoId'] ?? '')
                . '&token=' . urlencode($statusToken);
            ?>
            <div class="mod-proclaim-youtube__status-bar mb-2">
                <div id="mod-proclaim-youtube-badge-<?php echo $moduleId; ?>" class="mod-proclaim-youtube__badge"
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

                <?php if ((bool) $params->get('show_countdown', 1) && $isUpcoming && !empty($video['scheduledStartTime'])) : ?>
                    <div id="mod-proclaim-youtube-countdown-<?php echo $moduleId; ?>"
                         class="mod-proclaim-youtube__countdown"
                         data-label-live-in="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_LIVE_IN'), ENT_QUOTES, 'UTF-8'); ?>"
                         data-label-starting-soon="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_STARTING_SOON'), ENT_QUOTES, 'UTF-8'); ?>"
                         data-label-scheduled-for="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_SCHEDULED_FOR'), ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="mod-proclaim-youtube__countdown-timer text-muted"></span>
                        <span class="mod-proclaim-youtube__countdown-sep text-muted" aria-hidden="true">&middot;</span>
                        <span class="mod-proclaim-youtube__countdown-date small text-muted"></span>
                    </div>
                <?php endif; ?>

                <?php if ((bool) $params->get('show_notify_button', 1) && $isUpcoming) : ?>
                    <div id="mod-proclaim-youtube-notify-<?php echo $moduleId; ?>"
                         class="mod-proclaim-youtube__notify"
                         data-video-title="<?php echo htmlspecialchars($video['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                         data-video-id="<?php echo htmlspecialchars($video['videoId'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                         data-label-notify="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_NOTIFY_ME'), ENT_QUOTES, 'UTF-8'); ?>"
                         data-label-enabled="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_NOTIFY_ENABLED'), ENT_QUOTES, 'UTF-8'); ?>"
                         data-label-denied="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_NOTIFY_DENIED'), ENT_QUOTES, 'UTF-8'); ?>"
                         data-label-live-title="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_NOTIFY_LIVE_TITLE'), ENT_QUOTES, 'UTF-8'); ?>"
                         data-label-live-body="<?php echo htmlspecialchars(Text::sprintf('MOD_PROCLAIM_YOUTUBE_NOTIFY_LIVE_BODY', $video['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        <span role="button" class="mod-proclaim-youtube__notify-btn text-muted small">
                            <span class="fas fa-bell me-1" aria-hidden="true"></span>
                            <span class="mod-proclaim-youtube__notify-label"><?php echo Text::_('MOD_PROCLAIM_YOUTUBE_NOTIFY_ME'); ?></span>
                        </span>
                    </div>
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
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
                loading="lazy"
                title="<?php echo htmlspecialchars($video['title'] ?? 'YouTube Video', ENT_QUOTES, 'UTF-8'); ?>"
            ></iframe>

            <?php if ($responsive) : ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ((bool) $params->get('enable_mini_player', 1) && $isLive) : ?>
            <div id="mod-proclaim-youtube-miniplayer-<?php echo $moduleId; ?>"
                 class="mod-proclaim-youtube__miniplayer"
                 style="display:none;">
                <div class="mod-proclaim-youtube__miniplayer-controls">
                    <button type="button" class="mod-proclaim-youtube__miniplayer-expand btn btn-sm btn-light"
                            aria-label="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_MINI_PLAYER_EXPAND'), ENT_QUOTES, 'UTF-8'); ?>"
                            title="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_MINI_PLAYER_EXPAND'), ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="fas fa-expand" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="mod-proclaim-youtube__miniplayer-close btn btn-sm btn-light"
                            aria-label="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_MINI_PLAYER_CLOSE'), ENT_QUOTES, 'UTF-8'); ?>"
                            title="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_MINI_PLAYER_CLOSE'), ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="fas fa-times" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="mod-proclaim-youtube__miniplayer-frame"></div>
            </div>
        <?php endif; ?>

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

    <?php elseif (!empty($fallbackSermons) && $fallbackTemplate && $fallbackParams) : ?>
        <div class="mod-proclaim-youtube__fallback">
            <?php
            $listing = new Cwmlisting();
        $fallbackParams->set('listing_item_style', 'grid');
        $fallbackParams->set('grid_card_size', 'medium');
        echo $listing->getFluidListing(
            $fallbackSermons,
            $fallbackParams,
            $fallbackTemplate,
            'sermons'
        );
        ?>
        </div>
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

<?php if ($video && $embedUrl && $showLiveBadge && ($isLive || $isUpcoming)) : ?>
    <?php
    $wa               = $app->getDocument()->getWebAssetManager();
    $basePollInterval = (int) $params->get('poll_interval', 120);
    // Clamp to minimum 60 seconds to protect API quota
    $basePollInterval = max(60, $basePollInterval);

    // Register script options for this module instance
    $existing   = $app->getDocument()->getScriptOptions('mod_proclaim_youtube') ?: [];
    $existing[] = [
        'moduleId'         => $moduleId,
        'pollInterval'     => $basePollInterval * 1000,
        'maxPollInterval'  => 600000,
        'pollWindowBefore' => (int) $params->get('poll_window_before', 2),
        'pollWindowAfter'  => (int) $params->get('poll_window_after', 1),
    ];
    $app->getDocument()->addScriptOptions('mod_proclaim_youtube', $existing, false);
    $wa->useScript('com_proclaim.mod-youtube-status');
    ?>
<?php endif; ?>
