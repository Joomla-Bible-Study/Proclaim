<?php

/**
 * Simple card grid template for sermons
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmsermons\HtmlView $this */

use Joomla\CMS\Language\Text;

// Add template accent color for pagination and load simple-grid CSS
$accentColor = $this->params->get('backcolor', '#287585');
$wa = $this->getDocument()->getWebAssetManager();
$wa->addInlineStyle(":root { --proclaim-accent-color: {$accentColor}; }");
$wa->useStyle('com_proclaim.simple-grid');

?>
<div class="proclaim-simple-grid">
    <h4><?php echo Text::_('JBS_CMN_TEACHINGS'); ?></h4>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
        <?php foreach ($this->items as $item) : ?>
            <div class="col">
                <div class="card h-100 proclaim-simple-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="<?php echo $item->detailslink; ?>">
                                <?php echo $this->escape($item->studytitle); ?>
                            </a>
                        </h5>
                        <div class="proclaim-simple-content">
                            <div class="proclaim-simple-meta">
                                <?php if (!empty($item->teachername)) : ?>
                                    <span><i class="fas fa-user fa-fw" aria-hidden="true"></i> <?php echo $item->teachername; ?></span>
                                <?php endif; ?>
                                <?php if (!empty($item->scripture1)) : ?>
                                    <span><i class="fas fa-bible" aria-hidden="true"></i> <?php echo $item->scripture1; ?></span>
                                <?php endif; ?>
                                <?php if (!empty($item->studydate)) : ?>
                                    <span><i class="fas fa-calendar-alt fa-fw" aria-hidden="true"></i> <?php echo $item->studydate; ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($item->study_thumbnail)) : ?>
                                <div class="proclaim-simple-thumb">
                                    <?php echo $item->study_thumbnail; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($item->media)) : ?>
                        <div class="card-footer proclaim-simple-media">
                            <?php echo $item->media; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="pagination-container pagelinks mt-4">
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
</div>
