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

/** @var CWM\Component\Proclaim\Site\View\Cwmsermon\HtmlView $this */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$isPrint = !empty($this->print);
?>
<a href="#proclaim-main-content" class="proclaim-skip-link"><?php echo Text::_('JBS_CMN_SKIP_TO_CONTENT'); ?></a>
<div class="container-fluid proclaim-main-content" id="proclaim-main-content" role="main">
<?php if (!$isPrint) : ?>
    <div class="proclaim-print-btn text-end mb-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print();return false;" title="<?php echo Text::_('JBS_CMN_PRINT'); ?>">
            <span class="fas fa-print" aria-hidden="true"></span> <?php echo Text::_('JBS_CMN_PRINT'); ?>
        </button>
    </div>
<?php endif; ?>
<?php

if ($this->item->params->get('sermontemplate') && !$this->simple->mode) {
    echo $this->loadTemplate($this->item->params->get('sermontemplate'));
} elseif ($this->simple->mode === 1) {
    echo $this->loadTemplate('simple');
} else {
    echo '<div>' . $this->loadTemplate('main') . '</div>';
}

if (!$isPrint) {
    $show_comments = $this->item->params->get('show_comments');

    if ($show_comments >= 1) {
        $user   = Factory::getApplication()->getIdentity();
        $groups = $user->getAuthorisedViewLevels();

        if (\in_array($show_comments, $groups, false)) {
            echo '<div style="padding-top: 10px; margin: auto;">' . $this->loadTemplate('commentsform') . '</div>';
        }
    }

    echo $this->loadTemplate('footer');
    echo $this->loadTemplate('footerlink');
}

?>
</div><!--end of container fluid-->
