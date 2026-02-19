/**
 * Proclaim Admin Control Panel scripts.
 *
 * Handles:
 *  - Podcast task notice dismissal with localStorage (7-day snooze)
 *  - Auto-start of guided tour on first visit after upgrade
 *
 * @package  Proclaim
 * @since    10.1.0
 */
((document, Joomla) => {
    'use strict';

    /**
   * Dismiss the podcast task warning for 7 days using localStorage.
   * The notice element is only rendered server-side when the condition applies,
   * so this function is a no-op when the element is absent.
   */
    const initPodcastTaskNotice = () => {
        const KEY = 'proclaim_podcast_task_notice_dismissed';
        const DAYS = 7;
        const notice = document.getElementById('proclaim-podcast-task-notice');

        if (!notice) {
            return;
        }

        const stored = localStorage.getItem(KEY);

        if (stored && (Date.now() - parseInt(stored, 10)) < DAYS * 86400000) {
            notice.style.display = 'none';
            return;
        }

        const alert = notice.querySelector('.alert');

        if (alert) {
            alert.addEventListener('closed.bs.alert', () => {
                localStorage.setItem(KEY, Date.now().toString());
            });
        }
    };

    /**
   * Auto-start the guided tour when ?startTour=1 is in the URL.
   * Tour ID is passed via Joomla.getOptions('com_proclaim.cpanel').
   */
    const initGuidedTour = () => {
        const options = Joomla.getOptions('com_proclaim.cpanel', {});
        const tourId = options.startTour || 0;

        if (!tourId) {
            return;
        }

        if (typeof Joomla !== 'undefined' && Joomla.guidedTours) {
            Joomla.guidedTours.startTour(tourId);
        } else {
            const btn = document.querySelector(".button-start-guidedtour[data-gt-uid='com_proclaim_whats_new_10_1']");

            if (btn) {
                btn.click();
            }
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initPodcastTaskNotice();
            setTimeout(initGuidedTour, 500);
        });
    } else {
        initPodcastTaskNotice();
        setTimeout(initGuidedTour, 500);
    }
})(document, window.Joomla);
