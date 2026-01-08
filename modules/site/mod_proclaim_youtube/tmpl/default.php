<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Module
 * @subpackage mod_proclaim_youtube
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\CwmrouteHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var array|null $video */
/** @var string|null $embedUrl */
/** @var bool $responsive */
/** @var int $playerWidth */
/** @var int $playerHeight */
/** @var float $aspectRatio */
/** @var object|null $matchedMessage */
/** @var \Joomla\Registry\Registry $params */
?>

<div class="mod-proclaim-youtube">
    <?php if ($video && $embedUrl): ?>
        <?php
        $isLive          = $video['isLive'] ?? false;
        $isUpcoming      = $video['isUpcoming'] ?? false;
        $showTitle       = (bool) $params->get('show_title', 1);
        $showDescription = (bool) $params->get('show_description', 0);
        $showLiveBadge   = (bool) $params->get('show_live_badge', 1);
        ?>

        <?php if ($showLiveBadge && ($isLive || $isUpcoming)): ?>
            <div class="mod-proclaim-youtube__badge mb-2">
                <?php if ($isLive): ?>
                    <span class="badge bg-danger">
                        <span class="fas fa-circle me-1" aria-hidden="true"></span>
                        <?php echo Text::_('MOD_PROCLAIM_YOUTUBE_LIVE_NOW'); ?>
                    </span>
                <?php elseif ($isUpcoming): ?>
                    <span class="badge bg-warning text-dark">
                        <span class="fas fa-clock me-1" aria-hidden="true"></span>
                        <?php echo Text::_('MOD_PROCLAIM_YOUTUBE_UPCOMING'); ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($showTitle && !empty($video['title'])): ?>
            <h3 class="mod-proclaim-youtube__title mb-2">
                <?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?>
            </h3>
        <?php endif; ?>

        <div class="mod-proclaim-youtube__player<?php echo $responsive ? ' mod-proclaim-youtube__player--responsive' : ''; ?>">
            <?php if ($responsive): ?>
                <div class="mod-proclaim-youtube__player-wrapper" style="padding-bottom: <?php echo $aspectRatio; ?>%;">
            <?php endif; ?>

            <iframe
                src="<?php echo $embedUrl; ?>"
                <?php if (!$responsive): ?>
                    width="<?php echo $playerWidth; ?>"
                    height="<?php echo $playerHeight; ?>"
                <?php endif; ?>
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
                title="<?php echo htmlspecialchars($video['title'] ?? 'YouTube Video', ENT_QUOTES, 'UTF-8'); ?>"
            ></iframe>

            <?php if ($responsive): ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($showDescription && !empty($video['truncatedDescription'])): ?>
            <div class="mod-proclaim-youtube__description mt-2">
                <p><?php echo htmlspecialchars($video['truncatedDescription'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($matchedMessage) && !empty($matchedMessage->id)): ?>
            <div class="mod-proclaim-youtube__message-link mt-3">
                <?php
                $messageRoute = CwmrouteHelper::getMessageRoute((int) $matchedMessage->id);
                ?>
                <a href="<?php echo Route::_($messageRoute); ?>" class="btn btn-outline-primary btn-sm">
                    <span class="icon-book me-1" aria-hidden="true"></span>
                    <?php echo Text::_('MOD_PROCLAIM_YOUTUBE_VIEW_MESSAGE'); ?>
                </a>
                <?php if (!empty($matchedMessage->teachername)): ?>
                    <small class="text-muted ms-2">
                        <?php echo Text::sprintf('MOD_PROCLAIM_YOUTUBE_BY_TEACHER', htmlspecialchars($matchedMessage->teachername, ENT_QUOTES, 'UTF-8')); ?>
                    </small>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <?php
        $noVideoMessage = $params->get('no_video_message', '');
        if (empty($noVideoMessage)) {
            $noVideoMessage = Text::_('MOD_PROCLAIM_YOUTUBE_NO_VIDEO');
        }
        ?>
        <div class="mod-proclaim-youtube__no-video alert alert-info">
            <?php echo htmlspecialchars($noVideoMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
</div>

<style>
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
}
.mod-proclaim-youtube__badge .badge {
    font-size: 0.9em;
}
</style>
