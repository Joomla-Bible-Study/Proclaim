<?php

/**
 * Show/hide toggle button for landing page sections.
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Expected $displayData keys:
 *   - divId       (string) Section identifier for toggling
 *   - label       (string) Accessible label text
 *   - hiddenCount (int)    Number of hidden items
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

$divId       = $displayData['divId'];
$label       = $displayData['label'] ?? '';
$hiddenCount = (int) ($displayData['hiddenCount'] ?? 0);

if ($hiddenCount <= 0) {
    return;
}

$showText = Text::sprintf('JBS_CMN_SHOW_ALL_SECTION', $label);
$hideText = Text::sprintf('JBS_CMN_SHOW_LESS_SECTION', $label);
?>
<button type="button"
        class="btn btn-outline-primary btn-sm proclaim-landing__show-more"
        data-proclaim-toggle="<?php echo htmlspecialchars($divId, ENT_QUOTES, 'UTF-8'); ?>"
        data-proclaim-show-text="<?php echo htmlspecialchars($showText, ENT_QUOTES, 'UTF-8'); ?>"
        data-proclaim-hide-text="<?php echo htmlspecialchars($hideText, ENT_QUOTES, 'UTF-8'); ?>"
        aria-expanded="false">
    <span class="proclaim-landing__toggle-label"><?php echo $showText; ?></span>
    <span class="proclaim-landing__toggle-count">(<?php echo $hiddenCount; ?>)</span>
</button>
