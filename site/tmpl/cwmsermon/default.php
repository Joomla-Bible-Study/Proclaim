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
use Joomla\CMS\Uri\Uri;

$isPrint = !empty($this->print);
?>
<div class="com-proclaim">
<a href="#proclaim-main-content" class="proclaim-skip-link"><?php echo Text::_('JBS_CMN_SKIP_TO_CONTENT'); ?></a>
<div class="container-fluid proclaim-main-content<?php echo $isPrint ? ' proclaim-print-mode' : ''; ?>" id="proclaim-main-content" role="main">
<?php if (!$isPrint) : ?>
    <?php
    $printUrl = Uri::getInstance();
    $printUrl->setVar('tmpl', 'component');
    $printUrl->setVar('print', '1');
    ?>
    <div class="proclaim-print-btn d-none d-md-block text-end mb-2">
        <a href="<?php echo htmlspecialchars($printUrl->toString()); ?>" target="printWindow" class="btn btn-sm btn-outline-secondary" title="<?php echo Text::_('JBS_CMN_PRINT'); ?>">
            <span class="fa-solid fa-print" aria-hidden="true"></span> <?php echo Text::_('JBS_CMN_PRINT'); ?>
        </a>
    </div>
<?php else : ?>
    <div class="proclaim-print-btn d-none d-md-block text-end mb-2 proclaim-no-print">
        <button type="button" class="btn btn-sm btn-outline-secondary js-print-btn" title="<?php echo Text::_('JBS_CMN_PRINT'); ?>">
            <span class="fa-solid fa-print" aria-hidden="true"></span> <?php echo Text::_('JBS_CMN_PRINT'); ?>
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
</div>
