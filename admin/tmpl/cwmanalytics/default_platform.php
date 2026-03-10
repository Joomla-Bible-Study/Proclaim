<?php

/**
 * Analytics sub-template: Platform video statistics summary.
 *
 * Shows aggregated stats from external platforms (YouTube, Vimeo, Wistia)
 * with a manual sync button.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * @since      10.1.0
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmanalytics\HtmlView $this */

$hasServers = !empty($this->videoServers);
$hasStats   = !empty($this->platformStats);
$serversJson = htmlspecialchars(json_encode($this->videoServers, JSON_THROW_ON_ERROR), ENT_QUOTES);
$token       = Session::getFormToken();
?>

<div class="card mb-3">
    <div class="card-header fw-semibold d-flex align-items-center">
        <i class="icon-play me-1" aria-hidden="true"></i>
        <?php echo Text::_('JBS_ANA_PLATFORM_STATS'); ?>
        <?php if ($hasServers) : ?>
        <button type="button"
                class="btn btn-sm btn-primary ms-auto"
                id="cwm-sync-platform-stats"
                data-servers="<?php echo $serversJson; ?>"
                data-token="<?php echo $token; ?>"
                data-batch-limit="50">
            <i class="icon-refresh me-1" aria-hidden="true"></i>
            <?php echo Text::_('JBS_ANA_SYNC_PLATFORM_STATS'); ?>
        </button>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (!$hasServers) : ?>
            <p class="text-muted mb-0">
                <i class="icon-info-circle me-1" aria-hidden="true"></i>
                <?php echo Text::_('JBS_ANA_PLATFORM_NO_SERVERS'); ?>
            </p>
        <?php elseif (!$hasStats) : ?>
            <p class="text-muted mb-0">
                <i class="icon-clock me-1" aria-hidden="true"></i>
                <?php echo Text::_('JBS_ANA_PLATFORM_NOT_SYNCED'); ?>
            </p>
        <?php else : ?>
            <div id="cwm-sync-status" class="mb-2" style="display:none"></div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th><?php echo Text::_('JBS_ANA_PLATFORM'); ?></th>
                            <th class="text-end"><?php echo Text::_('JBS_ANA_PLATFORM_VIDEOS_SYNCED'); ?></th>
                            <th class="text-end"><?php echo Text::_('JBS_ANA_PLATFORM_VIEWS'); ?></th>
                            <th class="text-end"><?php echo Text::_('JBS_ANA_PLATFORM_PLAYS'); ?></th>
                            <th class="text-end"><?php echo Text::_('JBS_ANA_PLATFORM_LIKES'); ?></th>
                            <th class="text-end"><?php echo Text::_('JBS_ANA_PLATFORM_LAST_SYNCED'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->platformStats as $row) : ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst((string) ($row['platform'] ?? '')), ENT_QUOTES); ?></span>
                            </td>
                            <td class="text-end text-body"><?php echo number_format((int) ($row['media_count'] ?? 0)); ?></td>
                            <td class="text-end text-body"><?php echo number_format((int) ($row['total_views'] ?? 0)); ?></td>
                            <td class="text-end text-body"><?php echo number_format((int) ($row['total_plays'] ?? 0)); ?></td>
                            <td class="text-end text-body"><?php echo $row['total_likes'] !== null ? number_format((int) $row['total_likes']) : '—'; ?></td>
                            <td class="text-end text-muted small">
                                <?php echo $row['last_synced'] ? htmlspecialchars(substr((string) $row['last_synced'], 0, 16), ENT_QUOTES) : '—'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mt-2 mb-0">
                <i class="icon-lock me-1" aria-hidden="true"></i>
                <?php echo Text::_('JBS_ANA_PLATFORM_GDPR_NOTICE'); ?>
            </p>
        <?php endif; ?>
    </div>
</div>
