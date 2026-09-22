/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Narrow the System Health report to one or more statuses.
 *
 * The summary chips are the control: they already name the statuses and carry
 * the counts, so a separate filter bar would repeat them. Pressing none shows
 * everything, which is how the panel opens — the filter is opt-in and nothing
 * is hidden until somebody asks for it.
 *
 * @since __DEPLOY_VERSION__
 */

document.addEventListener('DOMContentLoaded', () => {
    const bar = document.querySelector('.js-health-filters');

    if (!bar) {
        return;
    }

    const chips = Array.from(bar.querySelectorAll('.js-health-filter'));
    const reset = bar.querySelector('.js-health-filter-reset');
    const rows = Array.from(document.querySelectorAll('.js-health-row'));
    const groups = Array.from(document.querySelectorAll('.js-health-group'));
    const live = document.querySelector('.js-health-filter-status');
    const empty = document.querySelector('.js-health-filter-empty');

    if (!chips.length || !rows.length) {
        return;
    }

    /**
     * Statuses currently pressed. Empty means "no filter", not "nothing".
     *
     * @return  {string[]}
     */
    const active = () => chips
        .filter((chip) => chip.getAttribute('aria-pressed') === 'true')
        .map((chip) => chip.dataset.status);

    const apply = () => {
        const wanted = active();
        const filtering = wanted.length > 0;
        let shown = 0;

        rows.forEach((row) => {
            const visible = !filtering || wanted.includes(row.dataset.status);

            row.hidden = !visible;

            if (visible) {
                shown += 1;
            }
        });

        // A heading with nothing under it reads as an empty category rather
        // than one the filter excluded, so the group goes with its rows.
        groups.forEach((group) => {
            const any = Array.from(group.querySelectorAll('.js-health-row'))
                .some((row) => !row.hidden);

            group.hidden = !any;
        });

        // Dim what is not selected, so the pressed chip is not signalled by
        // colour alone. aria-pressed carries the state for assistive tech.
        chips.forEach((chip) => {
            chip.classList.toggle(
                'opacity-50',
                filtering && chip.getAttribute('aria-pressed') !== 'true'
            );
        });

        if (reset) {
            reset.hidden = !filtering;
        }

        if (empty) {
            empty.hidden = shown !== 0;
        }

        // Translated server-side and handed over as a template, so the
        // announcement is a sentence rather than a ratio and translators keep
        // control of the word order.
        if (live) {
            const template = live.dataset.template || '';

            live.textContent = filtering
                ? template.replace('%1$s', String(shown)).replace('%2$s', String(rows.length))
                : '';
        }
    };

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            chip.setAttribute(
                'aria-pressed',
                chip.getAttribute('aria-pressed') === 'true' ? 'false' : 'true'
            );
            apply();
        });
    });

    if (reset) {
        reset.addEventListener('click', () => {
            chips.forEach((chip) => chip.setAttribute('aria-pressed', 'false'));
            apply();
            chips[0].focus();
        });
    }
});
