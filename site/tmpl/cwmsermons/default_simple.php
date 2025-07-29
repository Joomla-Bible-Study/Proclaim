<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Html\HtmlHelper;
use Joomla\CMS\Language\Text;


HtmlHelper::_('dropdown.init');
HtmlHelper::_('behavior.multiselect');
HtmlHelper::_('formbehavior.chosen', 'select');

$app       = Factory::getApplication();
$user      = $user = Factory::getApplication()->getSession()->get('user');
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$archived  = $this->state->get('filter.published') == 2 ? true : false;
$trashed   = $this->state->get('filter.published') == -2 ? true : false;
$saveOrder = $listOrder == 'study.ordering';
$columns   = 12;

?>
<style>img {
        border-radius: 4px;
    }</style>
<div class="row-fluid col-12">
    <h4>
        <?php
        echo Text::_('JBS_CMN_TEACHINGS'); ?>
    </h4>
</div>
<div class="row-fluid col-lg-12 dropdowns"
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
<div class="row-fluid col-lg-12 pagination pagelinks" style="background-color: #A9A9A9;
    margin: 0 -5px;
    padding: 8px 8px;
    border: 1px solid #C5C1BE;
    position: relative;
    -webkit-border-radius: 9px;">
    <?php
    echo $this->pagination->getPageslinks(); ?>
</div>
