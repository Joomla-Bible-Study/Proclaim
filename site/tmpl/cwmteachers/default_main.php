<?php

/**
 * Teachers view subset main
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var CWM\Component\Proclaim\Site\View\Cwmteachers\HtmlView $this */

// Use pre-created values from HtmlView
$listing      = $this->listing;
$classelement = $this->classelement;
?>
<div class="container proclaim-main-content" id="proclaim-main-content" role="main">
    <div class="hero-unit pt-4 pb-3"> <!-- This div is the header container -->
        <?php
        if ($classelement) : ?>
            <<?php
            echo $classelement; ?> class="componentheading">
            <?php
        endif; ?>
        <?php
        echo $this->params->get('teacher_title', Text::_('JBS_TCH_OUR_TEACHERS')); ?>
        <?php
        if ($classelement) : ?>
    </<?php
    echo $classelement; ?> >
            <?php
        endif; ?>
</div>
<div class="row">
    <div class="col-12">
        <?php
        if ($this->params->get('teacher_headercode')) {
            echo HTMLHelper::_(
                'content.prepare',
                $this->params->get('teacher_headercode'),
                '',
                'com_proclaim.teachers'
            );
        }
?>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <?php
echo $listing->getFluidListing($this->items, $this->params, $this->template, $type = 'teachers');
?>
    </div>
</div>
<hr>

<div class="listingfooter">
    <?php
    echo $this->page->pagelinks;
echo $this->page->counter;
?>
</div>
<!--end of bsfooter div-->
