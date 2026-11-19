/**
 * Proclaim Admin Control Panel scripts.
 *
 * Handles:
 *  - Podcast task notice dismissal with localStorage (7-day snooze)
 *  - Starting the guided tour a post-install link names (?startTour=...)
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

        // If dismissed within the snooze window, leave the notice hidden (d-none stays).
        if (stored && (Date.now() - parseInt(stored, 10)) < DAYS * 86400000) {
            return;
        }

        // Not snoozed — reveal the notice (it starts hidden to avoid flash).
        notice.classList.remove('d-none');

        const alert = notice.querySelector('.alert');

        if (alert) {
            alert.addEventListener('closed.bs.alert', () => {
                localStorage.setItem(KEY, Date.now().toString());
            });
        }
    };

    /**
   * Start the tour a post-install link asked for (?startTour=...).
   * The control panel passes its id via Joomla.getOptions('com_proclaim.cpanel').
   */
    const initGuidedTour = () => {
        const options = Joomla.getOptions('com_proclaim.cpanel', {});
        const tourId = Number.parseInt(options.startTour, 10) || 0;

        if (tourId <= 0) {
            return;
        }

        // Core's Guided Tours script has no API to start a tour. It starts one
        // from a click on any .button-start-guidedtour carrying data-id.
        const trigger = document.createElement('button');

        trigger.type = 'button';
        trigger.className = 'button-start-guidedtour';
        trigger.dataset.id = String(tourId);
        trigger.hidden = true;

        document.body.appendChild(trigger);
        trigger.click();
        trigger.remove();
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
