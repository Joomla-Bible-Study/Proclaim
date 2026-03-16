<?php

/**
 * Landing page template — dispatches to style-specific layouts.
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmlandingpage\HtmlView $this */

use Joomla\CMS\Layout\LayoutHelper;

$CWMLanding = $this->landing;
$params     = $this->params;
$layoutPath = JPATH_COMPONENT_SITE . '/layouts';
$style      = $params->get('landing_style', 'hero');
$sections   = $CWMLanding->getSectionOrder($params);

// Fetch all section data upfront
$allSectionData = [];
$sectionIndex   = 0;

foreach ($sections as $section) {
    if (!$section->enabled) {
        continue;
    }

    $items       = $this->landingData[$section->id] ?? null;
    $sectionData = $CWMLanding->getSectionData($section->id, $params, $items);

    if (!empty($sectionData['items'])) {
        $sectionIndex++;
        $allSectionData[] = [
            'data'  => $sectionData,
            'id'    => $section->id,
            'index' => $sectionIndex,
            'label' => $params->get($section->id . 'label', ''),
        ];
    }
}
?>
<div class="com-proclaim proclaim-landing proclaim-landing--<?php echo htmlspecialchars($style, ENT_QUOTES, 'UTF-8'); ?>">

    <?php echo LayoutHelper::render('landing.header', [
        'params' => $params,
        'main'   => $this->main ?? null,
    ], $layoutPath); ?>

    <div id="proclaim-main-content" class="proclaim-main-content" role="main">
        <?php
        // Dashboard: render featured row first
        if ($style === 'dashboard') {
            $sectionsByType = [];
            foreach ($allSectionData as $entry) {
                $sectionsByType[$entry['id']] = $entry['data'];
            }

            echo LayoutHelper::render('landing.dashboard.featured', [
                'allSections' => $sectionsByType,
            ], $layoutPath);

            // Dashboard uses accordion wrapper
            echo '<div class="accordion proclaim-landing-accordion" id="proclaimLandingAccordion">';
        }

        foreach ($allSectionData as $entry) {
            $sectionData  = $entry['data'];
            $sectionId    = $entry['id'];
            $sectionLabel = $entry['label'];
            $idx          = $entry['index'];

            $displayData = [
                'section'      => $sectionData,
                'sectionLabel' => $sectionLabel,
                'sectionIndex' => $idx,
                'params'       => $params,
            ];

            if ($style === 'dashboard') {
                $displayData['accordionId'] = 'proclaimLandingAccordion';
                echo LayoutHelper::render('landing.dashboard.section', $displayData, $layoutPath);
            } else {
                // Cards and Hero use image vs text layouts
                $layoutType = $sectionData['hasImages'] ? 'section-images' : 'section-text';

                // Hero style: alternate band styles, accent for topics
                if ($style === 'hero') {
                    if ($sectionId === 'topics') {
                        $displayData['bandStyle'] = 'accent';
                    } else {
                        $displayData['bandStyle'] = ($idx % 2 === 0) ? 'dark' : 'light';
                    }
                }

                echo LayoutHelper::render('landing.' . $style . '.' . $layoutType, $displayData, $layoutPath);
            }
        }

        if ($style === 'dashboard') {
            echo '</div><!-- end accordion -->';
        }
        ?>
    </div>
</div>
