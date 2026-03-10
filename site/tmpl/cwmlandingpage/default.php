<?php

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

/** @var CWM\Component\Proclaim\Site\View\Cwmlandingpage\HtmlView $this */

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use Joomla\CMS\Language\Text;

// Use a pre-created landing helper from HtmlView
$CWMLanding = $this->landing;
$params     = $this->params;
?>
<div class="com-proclaim">
<a href="#proclaim-main-content" class="proclaim-skip-link"><?php echo Text::_('JBS_CMN_SKIP_TO_CONTENT'); ?></a>
<div id="proclaim_landing" class="container proclaim-main-content" role="main">
    <div id="bsms_header">
        <h1 class="componentheading">
            <?php if (isset($this->main->path) && ($this->params->get('landing_show_page_image') > 0)) {
                echo Cwmimages::renderPicture($this->main, $this->params->get('landing_page_title', ''), '', false);
                ?><?php
                // End of column for logo
            }

if ($this->params->get('landing_show_page_title') > 0) {
    echo $this->params->get('landing_page_title');
}
?>
        </h1>
        <?php
        if ($this->params->get('landing_intro_show') > 0) { ?>
            <div id="proclaim_landing_intro">
                <?php echo $this->params->get('landing_intro'); ?>
            </div>
        <?php }
        ?>
    </div>
    <!-- End div id="bsms_header" -->

    <?php
    // Get section order from new format or legacy fields
    $sections = $CWMLanding->getSectionOrder($params);
$sectionIndex = 0;

foreach ($sections as $section) {
    $sectionIndex++;
    $showIt = $section->id;

    // Skip disabled sections
    if (!$section->enabled) {
        continue;
    }

    $heading       = null;
    $showIt_phrase = null;
    $showhideall   = null;

    // Get pre-fetched items for this section if available
    $items = $this->landingData[$showIt] ?? null;

    switch ($showIt) {
        case 'teachers':
            $heading       = $CWMLanding->getTeacherLandingPage($params, 0, $items);
            $showIt_phrase = Text::_('JBS_CMN_TEACHERS');
            $showhideall   = $this->getShowHide($showIt, $showIt_phrase, $sectionIndex);
            break;

        case 'series':
            $heading       = $CWMLanding->getSeriesLandingPage($params, 0, $items);
            $showIt_phrase = Text::_('JBS_CMN_SERIES');
            $showhideall   = $this->getShowHide($showIt, $showIt_phrase, $sectionIndex);
            break;

        case 'locations':
            $heading       = $CWMLanding->getLocationsLandingPage($params, 0, $items);
            $showIt_phrase = Text::_('JBS_CMN_LOCATIONS');
            $showhideall   = $this->getShowHide($showIt, $showIt_phrase, $sectionIndex);
            break;

        case 'messagetypes':
            $heading       = $CWMLanding->getMessageTypesLandingPage($params, 0, $items);
            $showIt_phrase = Text::_('JBS_CMN_MESSAGETYPES');
            $showhideall   = $this->getShowHide($showIt, $showIt_phrase, $sectionIndex);
            break;

        case 'topics':
            $heading       = $CWMLanding->getTopicsLandingPage($params, 0, $items);
            $showIt_phrase = Text::_('JBS_CMN_TOPICS');
            $showhideall   = $this->getShowHide($showIt, $showIt_phrase, $sectionIndex);
            break;

        case 'books':
            $heading       = $CWMLanding->getBooksLandingPage($params, 0, $items);
            $showIt_phrase = Text::_('JBS_CMN_BOOKS');
            $showhideall   = $this->getShowHide($showIt, $showIt_phrase, $sectionIndex);
            break;

        case 'years':
            $heading       = $CWMLanding->getYearsLandingPage($params, 0, $items);
            $showIt_phrase = Text::_('JBS_CMN_YEARS');
            $showhideall   = $this->getShowHide($showIt, $showIt_phrase, $sectionIndex);
            break;
    }

    // Only render if there's content
    if (!empty($heading)) {
        ?>
            <!-- Wrap each in a DIV... -->
            <div class="landing_item">
                <div class="landing_title">
                    <?php echo $params->get($showIt . 'label'); ?>
                </div>
                <!-- end div id="landing_title" -->
                <div class="landinglist container">
                    <div class="row">
                    <?php
                if (isset($showhideall)) {
                    echo $showhideall;
                }

        echo $heading;
        ?>
                    </div>
                </div>
                <!-- end div class="landinglist" -->
            </div><!-- end div class="landing_item" -->
            <?php
    }
} // End foreach sections
?>
</div><!-- end div id="proclaim_landing" -->
</div>
