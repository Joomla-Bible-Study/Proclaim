/**
 * WCAG 2.2 Level AA scanning for the E2E suite.
 *
 * Wraps @axe-core/playwright so every accessibility check in the suite asks
 * the same question of every page, and reports a failure in terms someone can
 * act on rather than a wall of JSON.
 *
 * Scope is deliberately narrow in two ways:
 *
 *   - Only the WCAG A and AA rule sets run. axe also ships best-practice and
 *     experimental rules; those are worth reading but failing a release on
 *     them would be failing on opinion rather than on the standard.
 *
 *   - Only Proclaim's own markup is scanned where a page selector allows it.
 *     A Joomla site's template, other extensions and third-party modules share
 *     the page, and we can neither fix nor be judged on their markup.
 *
 * An automated pass is not a conformance claim. axe catches roughly a third to
 * a half of WCAG issues — contrast, names, roles, structure — and cannot judge
 * whether alt text is *meaningful* or whether a flow is usable by keyboard in
 * practice. It is a floor, not a ceiling.
 *
 * That gap is widest at 2.2. WCAG 2.2 adds six AA criteria and axe automates
 * essentially one of them, `target-size` (2.5.8). These still need a human:
 *
 *   2.4.11 Focus Not Obscured        sticky headers hiding the focused control
 *   2.5.7  Dragging Movements        drag-only interactions, e.g. the layout
 *                                    editor and the ordering drag handles
 *   3.2.6  Consistent Help           help placed consistently across pages
 *   3.3.7  Redundant Entry           re-asking for what the user already gave
 *   3.3.8  Accessible Authentication no cognitive-function test to log in
 *
 * Two of those bear directly on Proclaim: the drag-and-drop layout editor and
 * the drag-handle ordering on teachers and series both engage 2.5.7, and
 * neither is covered by anything below.
 *
 * WCAG 2.2 also *removed* 4.1.1 Parsing. axe tags its `duplicate-id` rules
 * `wcag2a-obsolete` rather than `wcag2a`, so the tag set below does not pull
 * them in — verified against axe-core 4.12.1 rather than assumed.
 */

const AxeBuilder = require('@axe-core/playwright').default;

/**
 * The rule tags that together constitute WCAG 2.2 Level AA.
 *
 * 2.2 is backward compatible: everything in 2.0 and 2.1 still applies, so the
 * earlier tags stay and `wcag22aa` is additive. There is no `wcag22a` tag —
 * the two new A-level criteria (3.2.6, 3.3.7) have no automated rule.
 */
const WCAG_AA_TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'];

/**
 * Run an axe scan restricted to WCAG A/AA rules.
 *
 * @param {import('@playwright/test').Page} page
 * @param {object}   [options]
 * @param {string}   [options.include]  CSS selector to confine the scan to.
 * @param {string[]} [options.exclude]  CSS selectors to skip.
 * @param {string[]} [options.disableRules] Rule ids to switch off, with a reason
 *                                          in the calling test.
 * @returns {Promise<import('axe-core').AxeResults>}
 */
async function scan(page, options = {}) {
    let builder = new AxeBuilder({ page }).withTags(WCAG_AA_TAGS);

    if (options.include) {
        builder = builder.include(options.include);
    }

    for (const selector of options.exclude || []) {
        builder = builder.exclude(selector);
    }

    if (options.disableRules && options.disableRules.length) {
        builder = builder.disableRules(options.disableRules);
    }

    return builder.analyze();
}

/**
 * Turn axe violations into something readable in a test failure.
 *
 * The default output is the full result object, which buries the one line that
 * matters — which element, and what to change.
 *
 * @param {import('axe-core').Result[]} violations
 * @returns {string}
 */
function formatViolations(violations) {
    if (!violations.length) {
        return 'No WCAG A/AA violations.';
    }

    const lines = [`${violations.length} WCAG A/AA violation type(s):`, ''];

    for (const violation of violations) {
        const tags = violation.tags.filter((t) => t.startsWith('wcag')).join(', ');
        lines.push(`  [${violation.impact}] ${violation.id} — ${violation.help}`);
        lines.push(`    ${tags}`);
        lines.push(`    ${violation.helpUrl}`);

        // Three is enough to characterise the problem; a listing page can
        // repeat the same fault on every row.
        for (const node of violation.nodes.slice(0, 3)) {
            lines.push(`    → ${node.target.join(' ')}`);
            const detail = (node.failureSummary || '').split('\n').filter(Boolean)[1];

            if (detail) {
                lines.push(`      ${detail.trim()}`);
            }
        }

        if (violation.nodes.length > 3) {
            lines.push(`    … and ${violation.nodes.length - 3} more element(s)`);
        }

        lines.push('');
    }

    return lines.join('\n');
}

/**
 * Scan a page and fail the test with a readable report if it does not meet AA.
 *
 * @param {import('@playwright/test').Page}   page
 * @param {import('@playwright/test').Expect} expect
 * @param {object} [options] Passed through to scan().
 */
async function expectNoViolations(page, expect, options = {}) {
    // Joomla renders its error pages inside the same content region as a real
    // view, so "the container is visible" is true on a 404 and a scan of an
    // error page reports no violations and passes.
    //
    // That is not hypothetical. A wrong admin view name (cwmsermons, which does
    // not exist — the admin view is cwmmessages) made this suite report "sermon
    // list meets WCAG AA" while scanning a 404. A silent false pass on an
    // accessibility gate is worse than no gate, because it is quoted as
    // evidence of compliance.
    const title = await page.title();

    expect(title, `Page is an error page, not the view under test: "${title}"`)
        .not.toMatch(/^Error:?\s*\d*/i);

    const results = await scan(page, options);

    expect(formatViolations(results.violations)).toBe('No WCAG A/AA violations.');
}

module.exports = { scan, formatViolations, expectNoViolations, WCAG_AA_TAGS };
