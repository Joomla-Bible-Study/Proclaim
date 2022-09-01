<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
?>
<div class="page-header">
    <h1 itemprop="headline">
        <?php echo $this->item->studytitle; ?>		</h1>
</div>
<dl class="article-info text-muted">

    <dd class="createdby" itemprop="author" itemscope="" itemtype="https://schema.org/Person">
        <span class="icon-user icon-fw" aria-hidden="true"></span>
        <?php echo Text::_('JBS_CMN_TEACHER'); ?> <span itemprop="name"><?php echo $this->item->teachername;?></span>	</dd>

    <dd class="category-name">
        <span class="fas fa-bible" aria-hidden="true"></span>
        <?php echo Text::_('JBS_CMN_SCRIPTURE'); ?>: <?php echo $this->item->scripture1;?>	</dd>

    <dd class="published">
        <span class="icon-calendar icon-fw" aria-hidden="true"></span>
        <time datetime="2022-07-14T12:19:37-07:00" itemprop="datePublished">
            <?php echo Text::_('JBS_CMN_ITEM_PUBLISHED'); ?>: <?php echo $this->item->studydate;?>	</time>
    </dd>
</dl>
<div itemprop="articleBody" class="com-content-article__body">
    <?php echo $this->item->media; ?> 	</div>
