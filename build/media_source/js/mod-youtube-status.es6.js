/**
 * YouTube module status polling, countdown timer, and live badge management.
 *
 * Reads configuration from data-* attributes on the badge element and
 * script options registered via Joomla.getOptions('mod_proclaim_youtube').
 *
 * @package    Proclaim.Module
 * @subpackage mod_proclaim_youtube
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
    'use strict';

    /**
     * Initialize polling and countdown for a single module instance.
     *
     * @param {object} config - Module instance configuration from Joomla.getOptions
     */
    function initInstance(config) {
        const moduleId = config.moduleId;
        const badgeEl = document.getElementById('mod-proclaim-youtube-badge-' + moduleId);

        if (!badgeEl) {
            return;
        }

        let wasLive = badgeEl.dataset.isLive === '1';
        let wasUpcoming = badgeEl.dataset.isUpcoming === '1';
        const labelLive = badgeEl.dataset.labelLive;
        const labelUpcoming = badgeEl.dataset.labelUpcoming;

        const basePollInterval = config.pollInterval;
        const maxPollInterval = config.maxPollInterval;
        let currentInterval = basePollInterval;
        let unchangedCount = 0;
        const ajaxUrl = badgeEl.dataset.ajaxUrl;

        // Poll window: only poll within N hours before / after the scheduled start
        const pollWindowBeforeMs = config.pollWindowBefore * 3600000;
        const pollWindowAfterMs = config.pollWindowAfter * 3600000;
        let scheduledStart = badgeEl.dataset.scheduledStart
            ? new Date(badgeEl.dataset.scheduledStart).getTime()
            : 0;

        // --- Countdown timer ---
        const countdownEl = document.getElementById('mod-proclaim-youtube-countdown-' + moduleId);
        let countdownTimer = null;

        function formatCountdown(ms) {
            if (ms <= 0) {
                return countdownEl.dataset.labelStartingSoon;
            }

            const totalMin = Math.floor(ms / 60000);
            const hours = Math.floor(totalMin / 60);
            const minutes = totalMin % 60;
            const parts = [];

            if (hours > 0) {
                parts.push(hours + 'h');
            }

            if (minutes > 0 || hours === 0) {
                parts.push(minutes + ' min');
            }

            return countdownEl.dataset.labelLiveIn.replace('%s', parts.join(' '));
        }

        function updateCountdown() {
            if (!countdownEl) {
                return;
            }

            if (!scheduledStart) {
                countdownEl.style.display = 'none';

                return;
            }

            countdownEl.style.display = '';
            const remaining = scheduledStart - Date.now();
            const timerEl = countdownEl.querySelector('.mod-proclaim-youtube__countdown-timer');
            const dateEl = countdownEl.querySelector('.mod-proclaim-youtube__countdown-date');

            timerEl.textContent = formatCountdown(remaining);
            dateEl.textContent = countdownEl.dataset.labelScheduledFor.replace(
                '%s',
                new Date(scheduledStart).toLocaleString(undefined, {
                    weekday: 'long',
                    month: 'long',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                })
            );

            if (remaining > 0) {
                // Update every second when < 1 hour, every 30s otherwise
                countdownTimer = setTimeout(updateCountdown, remaining > 60000 ? 30000 : 1000);
            }
        }

        updateCountdown();

        // --- Notify me button ---
        const notifyEl = document.getElementById('mod-proclaim-youtube-notify-' + moduleId);
        let notifyGranted = false;

        if (notifyEl && 'Notification' in window) {
            const notifyBtn = notifyEl.querySelector('.mod-proclaim-youtube__notify-btn');
            const notifyLabel = notifyEl.querySelector('.mod-proclaim-youtube__notify-label');

            function applyNotifyState(permission) {
                if (permission === 'granted') {
                    notifyGranted = true;
                    notifyLabel.textContent = notifyEl.dataset.labelEnabled;
                    notifyBtn.classList.replace('text-muted', 'text-success');
                } else if (permission === 'denied') {
                    notifyLabel.textContent = notifyEl.dataset.labelDenied;
                    notifyBtn.style.opacity = '0.5';
                    notifyBtn.style.pointerEvents = 'none';
                }
            }

            // Reflect existing permission state
            applyNotifyState(Notification.permission);

            notifyBtn.addEventListener('click', function () {
                if (Notification.permission === 'granted') {
                    notifyGranted = true;

                    return;
                }

                Notification.requestPermission().then(function (perm) {
                    applyNotifyState(perm);
                });
            });
        } else if (notifyEl) {
            // Notification API not supported — hide button
            notifyEl.style.display = 'none';
        }

        // --- Mini-player (PiP) ---
        const miniEl = document.getElementById('mod-proclaim-youtube-miniplayer-' + moduleId);

        if (miniEl) {
            const playerEl = badgeEl.closest('.mod-proclaim-youtube')
                .querySelector('.mod-proclaim-youtube__player');
            const iframe = playerEl ? playerEl.querySelector('iframe') : null;

            if (playerEl && iframe) {
                const miniFrame = miniEl.querySelector('.mod-proclaim-youtube__miniplayer-frame');
                const closeBtn = miniEl.querySelector('.mod-proclaim-youtube__miniplayer-close');
                const expandBtn = miniEl.querySelector('.mod-proclaim-youtube__miniplayer-expand');
                let dismissed = false;
                let miniActive = false;

                function showMini() {
                    if (dismissed || miniActive) {
                        return;
                    }

                    const miniIframe = document.createElement('iframe');

                    miniIframe.src = iframe.src;
                    miniIframe.allow = iframe.allow;
                    miniIframe.allowFullscreen = true;
                    miniIframe.title = iframe.title;
                    miniFrame.innerHTML = '';
                    miniFrame.appendChild(miniIframe);
                    miniEl.style.display = 'block';
                    miniActive = true;
                }

                function hideMini() {
                    miniEl.style.display = 'none';
                    miniFrame.innerHTML = '';
                    miniActive = false;
                }

                closeBtn.addEventListener('click', function () {
                    dismissed = true;
                    hideMini();
                });

                expandBtn.addEventListener('click', function () {
                    hideMini();
                    playerEl.scrollIntoView({behavior: 'smooth', block: 'center'});
                });

                const miniObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) {
                            showMini();
                        } else {
                            hideMini();
                        }
                    });
                }, {
                    root: null,
                    rootMargin: '0px',
                    threshold: 0.3,
                });

                miniObserver.observe(playerEl);
            }
        }

        // --- Polling logic ---

        function isWithinPollWindow() {
            // Always poll if already live (status transitions need detection)
            if (wasLive) {
                return true;
            }

            // If no scheduled start time is known, poll normally (can't gate without data)
            if (!scheduledStart) {
                return true;
            }

            const now = Date.now();
            const windowOpen = scheduledStart - pollWindowBeforeMs;
            const windowClose = scheduledStart + pollWindowAfterMs;

            return now >= windowOpen && now <= windowClose;
        }

        function updateBadge(isLive, isUpcoming) {
            let html = '';

            if (isLive) {
                html = '<span class="badge bg-danger"><span class="fas fa-circle me-1" aria-hidden="true"></span>' + labelLive + '</span>';
            } else if (isUpcoming) {
                html = '<span class="badge bg-warning text-dark"><span class="fas fa-clock me-1" aria-hidden="true"></span>' + labelUpcoming + '</span>';
            }

            badgeEl.innerHTML = html;
        }

        function getBackoffInterval() {
            const multiplier = Math.pow(2, Math.floor(unchangedCount / 3));

            return Math.min(basePollInterval * multiplier, maxPollInterval);
        }

        function getWindowOpenDelay() {
            if (!scheduledStart) {
                return 0;
            }

            const windowOpen = scheduledStart - pollWindowBeforeMs;
            const delay = windowOpen - Date.now();

            return delay > 0 ? delay : 0;
        }

        let pollTimer;

        function schedulePoll() {
            currentInterval = getBackoffInterval();

            if (!isWithinPollWindow()) {
                const delay = getWindowOpenDelay();

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

            fetch(ajaxUrl, {method: 'GET', headers: {'Accept': 'application/json'}})
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (!data.success) {
                        return;
                    }

                    // Update scheduledStartTime if the server returns a newer value
                    if (data.scheduledStartTime) {
                        scheduledStart = new Date(data.scheduledStartTime).getTime();
                        clearTimeout(countdownTimer);
                        updateCountdown();
                    }

                    // Stop polling if daily quota is exhausted
                    if (typeof data.quotaRemaining === 'number' && data.quotaRemaining <= 0) {
                        return;
                    }

                    const isLive = data.isLive;
                    const isUpcoming = data.isUpcoming;

                    if (isLive !== wasLive || isUpcoming !== wasUpcoming) {
                        unchangedCount = 0;
                        updateBadge(isLive, isUpcoming);

                        if (isLive && !wasLive) {
                            wasLive = isLive;
                            wasUpcoming = isUpcoming;

                            if (countdownEl) {
                                countdownEl.style.display = 'none';
                            }

                            // Fire browser notification if permission was granted
                            if (notifyGranted && notifyEl) {
                                try {
                                    const videoId = notifyEl.dataset.videoId || badgeEl.dataset.currentVideo;
                                    const notification = new Notification(notifyEl.dataset.labelLiveTitle, {
                                        body: notifyEl.dataset.labelLiveBody,
                                        icon: 'https://img.youtube.com/vi/' + videoId + '/default.jpg',
                                        tag: 'proclaim-live-' + badgeEl.dataset.serverId,
                                    });

                                    // Auto-close after 10 seconds
                                    setTimeout(function () { notification.close(); }, 10000);
                                } catch {
                                    // Notification failed — page reload will handle it
                                }
                            }

                            setTimeout(function () {
                                window.location.reload();
                            }, 2000);

                            return;
                        }

                        if (!isLive && !isUpcoming) {
                            return;
                        }

                        wasLive = isLive;
                        wasUpcoming = isUpcoming;
                    } else {
                        unchangedCount += 1;
                    }

                    schedulePoll();
                })
                .catch(function () {
                    unchangedCount += 1;
                    schedulePoll();
                });
        }

        // Start: if within window poll immediately, otherwise schedule for window open
        if (isWithinPollWindow()) {
            pollTimer = setTimeout(checkStatus, currentInterval);
        } else {
            const delay = getWindowOpenDelay();

            if (delay > 0) {
                pollTimer = setTimeout(checkStatus, delay);
            }
            // else: window has already closed, no polling needed
        }

        document.addEventListener('visibilitychange', function () {
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
    }

    // Initialize all module instances on the page
    document.addEventListener('DOMContentLoaded', function () {
        const allOptions = Joomla.getOptions('mod_proclaim_youtube') || [];

        allOptions.forEach(function (config) {
            initInstance(config);
        });
    });
})();