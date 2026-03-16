<?php

/**
 * Shared logic to split landing items into visible/hidden arrays.
 *
 * Always respects landing_show: 1=above fold, 2=below fold.
 * In grid mode (useLimit=0), numeric limit further caps visible items.
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Expected $displayData keys:
 *   - items    (array) Item arrays with 'landing_show' key
 *   - limit    (int)   Numeric limit for grid mode
 *   - useLimit (int)   0=grid mode, 1=per-record mode
 *
 * Returns via $displayData (set by reference is not possible in layouts,
 * so this file is included directly, not via LayoutHelper).
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/**
 * Split items into visible and hidden arrays.
 *
 * @param   array  $items     Item arrays with 'landing_show' key
 * @param   int    $limit     Numeric limit for grid mode
 * @param   int    $useLimit  0=grid mode with landing_show, 1=per-record mode only
 *
 * @return  array{0: array, 1: array}  [visibleItems, hiddenItems]
 *
 * @since   10.3.0
 */
function proclaimSplitLandingItems(array $items, int $limit, int $useLimit): array
{
    $visibleItems = [];
    $hiddenItems  = [];
    $visibleCount = 0;

    foreach ($items as $item) {
        // landing_show=2 always goes below fold
        if ($item['landing_show'] === 2) {
            $hiddenItems[] = $item;
            continue;
        }

        // In grid mode, apply numeric limit on visible (landing_show=1) items
        if ($useLimit === 0 && $limit < 10000 && $visibleCount >= $limit) {
            $hiddenItems[] = $item;
            continue;
        }

        $visibleItems[] = $item;
        $visibleCount++;
    }

    return [$visibleItems, $hiddenItems];
}
