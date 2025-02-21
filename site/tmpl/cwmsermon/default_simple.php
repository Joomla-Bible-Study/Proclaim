<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

?>
<div class="page-header">
    <h1 itemprop="headline">
        <?php
        echo $this->item->studytitle; ?>        </h1>
</div>
<dl class="article-info text-muted">

    <dd class="createdby" itemprop="author" itemscope="" itemtype="https://schema.org/Person">
        <i class="icon-user icon-fw" aria-hidden="true"></i>
        <?php
        echo Text::_('JBS_CMN_TEACHER'); ?> <span itemprop="name"><?php
            echo $this->item->teachername; ?></span></dd>

    <dd class="category-name">
        <i class="fas fa-bible" aria-hidden="true"></i>
        <?php
        echo Text::_('JBS_CMN_SCRIPTURE'); ?>: <?php
        echo $this->item->scripture1; ?>    </dd>

    <dd class="published">
        <i class="icon-calendar icon-fw" aria-hidden="true"></i>
        <time datetime="2022-07-14T12:19:37-07:00" itemprop="datePublished">
            <?php
            echo Text::_('JBS_CMN_ITEM_PUBLISHED'); ?>: <?php
            echo $this->item->studydate; ?>    </time>
    </dd>
</dl>
<div itemprop="articleBody" class="com-content-article__body">
    <?php
    echo $this->item->media; ?>    </div>
