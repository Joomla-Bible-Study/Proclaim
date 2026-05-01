<?php

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Uri\Uri;

/**
 * Layout variables:
 * @var array $displayData
 *      - showIt (string)
 *      - showIt_phrase (string)
 *      - i (int)
 *      - params (Registry)
 *      - image (object) {path, width, height}
 */

extract($displayData);

$link     = 'javascript:ReverseDisplay2(\'showhide' . $showIt . '\')';
$hideMode = (int) $params->get('landing_hide', 0);
?>
<div id="showhide<?php echo $i; ?>">
    <?php // Case 0: Image only?>
    <?php if ($hideMode === 0) : ?>
        <a class="showhideheadingbutton" href="<?php echo $link; ?>">
            <img src="<?php echo Uri::base() . $image->path; ?>"
                 alt="<?php echo htmlspecialchars($showIt_phrase, ENT_QUOTES, 'UTF-8'); ?>"
                 title="<?php echo htmlspecialchars($showIt_phrase, ENT_QUOTES, 'UTF-8'); ?>"
                 width="<?php echo $image->width; ?>"
                 height="<?php echo $image->height; ?>" />
            <i class="fa-solid fa-arrow-down" title="x"></i>
        </a>

        <?php // Case 1: Image and Label?>
    <?php elseif ($hideMode === 1) : ?>
        <a class="showhideheadingbutton" href="<?php echo $link; ?>">
            <i class="fa-solid fa-arrow-down" title="x"></i>
        </a>
        <a class="showhideheadinglabel" href="<?php echo $link; ?>">
            <span id="landing_label"><?php echo $params->get('landing_hidelabel'); ?></span>
        </a>

        <?php // Case 2: Label only?>
    <?php elseif ($hideMode === 2) : ?>
        <a class="showhideheadinglabel" href="<?php echo $link; ?>">
            <span id="landing_label"><?php echo $params->get('landing_hidelabel'); ?></span>
        </a>
    <?php endif; ?>
</div>