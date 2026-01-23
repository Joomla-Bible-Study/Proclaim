<?php

declare(strict_types=1);

/**
 * Default
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

?>
<a href="#proclaim-main-content" class="proclaim-skip-link"><?php echo Text::_('JBS_CMN_SKIP_TO_CONTENT'); ?></a>
<div class="container-fluid proclaim-main-content" id="proclaim-main-content" role="main"><?php

if ($this->item->params->get('sermontemplate') && !$this->simple->mode) {
    echo $this->loadTemplate($this->item->params->get('sermontemplate'));
} elseif ($this->simple->mode === 1) {
    echo $this->loadTemplate('simple');
} else {
    echo '<div>' . $this->loadTemplate('main') . '</div>';
}

$show_comments = $this->item->params->get('show_comments');

if ($show_comments >= 1) {
    $user   = Factory::getUser();
    $groups = $user->getAuthorisedViewLevels();

    if (in_array($show_comments, $groups, false)) {
        echo '<div style="padding-top: 10px; margin: auto;">' . $this->loadTemplate('commentsform') . '</div>';
    }
}

echo $this->loadTemplate('footer');
echo $this->loadTemplate('footerlink');

?>
</div><!--end of container fluid-->
