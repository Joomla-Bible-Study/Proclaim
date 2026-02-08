<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmsermons\HtmlView $this */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('dropdown.init');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$app       = Factory::getApplication();
$user      = $app->getIdentity();
$userId    = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2;
$trashed   = $this->state->get('filter.published') == -2;
$saveOrder = $listOrder === 'study.ordering';
$columns   = 12;

// Add template accent color for pagination
$accentColor = $this->params->get('backcolor', '#287585');
$wa = $this->getDocument()->getWebAssetManager();
$wa->addInlineStyle(":root { --proclaim-accent-color: {$accentColor}; }");

?>
<style>img {
        border-radius: 4px;
    }</style>
<div class="row col-12">
    <h4>
        <?php
        echo Text::_('JBS_CMN_TEACHINGS'); ?>
    </h4>
</div>
<div class="row col-lg-12 dropdowns"
     style="background-color:#A9A9A9; margin:0 -5px; padding:4px 4px; border:1px solid #C5C1BE; position:relative; -webkit-border-radius:10px;">
</div>
<?php
foreach ($this->items as $this->item) {
    ?>
    <div class="page-header">
        <h5 itemprop="headline">
            <?php
            echo $this->item->studytitle; ?>        </h5>
    </div>
    <dl class="article-info text-muted" style="display: grid;">

        <dd class="createdby" itemprop="author" itemscope="" itemtype="https://schema.org/Person">
            <i class="icon-user icon-fw" aria-hidden="true"></i>
            <span itemprop="name"><?php
                echo $this->item->teachername; ?></span></dd>

        <dd class="category-name">
            <i class="fas fa-bible" aria-hidden="true"></i>
            <?php
            echo $this->item->scripture1; ?>    </dd>

        <dd class="published">
            <i class="icon-calendar icon-fw" aria-hidden="true"></i>
            <time datetime="2022-07-14T12:19:37-07:00" itemprop="datePublished">
                <?php
                echo $this->item->studydate; ?>    </time>
        </dd>
    </dl>
    <div itemprop="articleBody" class="com-content-article__body">
        <?php
        echo $this->item->media; ?>    </div>
    <div>
        <hr/>
    </div>
    <?php
} ?>
<div class="pagination-container pagelinks">
    <?php echo $this->pagination->getPageslinks(); ?>
</div>
