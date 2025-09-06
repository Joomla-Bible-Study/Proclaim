<?php

/**
 * Mod_Proclaim core file
 *
 * @package         Proclaim
 * @subpackage      mod_proclaim
 * @copyright   (C) 2025 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @link            https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/** @var Registry $params */
$show_link = $params->get('show_link', 1);

$Listing = new Cwmlisting(); ?>

<div class="row-fluid col-12">
    <h5>
        <?php
        echo Text::_('JBS_CMN_TEACHINGS'); ?>
    </h5>
</div>

<?php
/** @var stdClass $list */
foreach ($list as $study) {
    ?>
    <div class="page-header">
        <p itemprop="headline">
            <?php
            echo $study->studytitle; ?>        </p>
    </div>
    <dl class="article-info text-muted">

        <dd class="createdby" itemprop="author" itemscope="" itemtype="https://schema.org/Person">
            <span class="icon-user icon-fw" aria-hidden="true"></span>
            <span itemprop="name"><?php
                echo $study->teachername; ?></span></dd>

        <dd class="category-name">
            <span class="fas fa-bible" aria-hidden="true"></span>
            <?php
            echo $study->scripture1; ?>    </dd>

        <dd class="published">
            <span class="icon-calendar icon-fw" aria-hidden="true"></span>
            <time datetime="2022-07-14T12:19:37-07:00" itemprop="datePublished">
                <?php
                echo $study->studydate; ?>    </time>
        </dd>
    </dl>
    <div itemprop="articleBody" class="com-content-article__body">
        <?php
        echo $study->media; ?>    </div>
    <div>
        <hr/>
    </div>
    <?php
} ?>

<div class="row-fluid">
    <div class="col-12">
        <?php
        if ($params->get('show_link') > 0) {
            echo '<span class="fas fa-bible" aria-hidden="true"></span>' . $link;
        }
        ?>
    </div>
</div>
<!--end of footer div-->
<!--end container -->
<div style="clear: both;"></div>

