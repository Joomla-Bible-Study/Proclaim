<?php

/**
 * Teachers view subset main
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use Joomla\CMS\Html\HtmlHelper;
use Joomla\CMS\Language\Text;

$listing = new Cwmlisting();
$classelement = $listing->createelement($this->params->get('teachers_element'));
?>
<div class="container">
    <div class="hero-unit" style="padding-top:30px; padding-bottom:20px;"> <!-- This div is the header container -->
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
            echo HtmlHelper::_(
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
