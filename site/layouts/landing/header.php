<?php

/**
 * Landing page header layout (shared across all styles).
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Expected $displayData keys:
 *   - params (Registry)
 *   - main   (object|null) Page image object
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use Joomla\CMS\Language\Text;

$params = $displayData['params'];
$main   = $displayData['main'] ?? null;
?>
<a href="#proclaim-main-content" class="proclaim-skip-link"><?php echo Text::_('JBS_CMN_SKIP_TO_CONTENT'); ?></a>
<div id="bsms_header" class="proclaim-landing__header">
    <?php if (isset($main->path) && (int) $params->get('landing_show_page_image') > 0) : ?>
        <div class="proclaim-landing__header-image">
            <?php echo Cwmimages::renderPicture($main, $params->get('landing_page_title', ''), '', false); ?>
        </div>
    <?php endif; ?>

    <?php if ((int) $params->get('landing_show_page_title') > 0) : ?>
        <h1 class="proclaim-landing__title"><?php echo htmlspecialchars($params->get('landing_page_title', ''), ENT_QUOTES, 'UTF-8'); ?></h1>
    <?php endif; ?>

    <?php if ((int) $params->get('landing_intro_show') > 0 && $params->get('landing_intro')) : ?>
        <div class="proclaim-landing__intro">
            <?php echo $params->get('landing_intro'); ?>
        </div>
    <?php endif; ?>
</div>
