<?php

/**
 * Helper for Template Code
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmsermons\HtmlView $this */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Add template accent color for pagination
$accentColor = $this->params->get('backcolor', '#287585');
$wa = $this->getDocument()->getWebAssetManager();
$wa->addInlineStyle(":root { --proclaim-accent-color: {$accentColor}; }");
$wa->addInlineStyle('img { border-radius: 4px; }');

?>
<div class="row">
    <h2>
        Teachings
    </h2>
</div>


<div class="row dropdowns"
     style="background-color:#A9A9A9; margin:0 -5px; padding:8px 8px; border:1px solid #C5C1BE; position:relative; -webkit-border-radius:10px;">

    <?php
    echo $this->page->books;
echo $this->page->teachers;
echo $this->page->series;
?>
</div>
<?php
foreach ($this->items as $study) {
    ?>
    <div style="width:100%;">
        <div class="col-12 col-md-6 col-lg-3">
            <div style="padding:12px 8px;line-height:22px;height:200px;">
                <?php
                if ($study->study_thumbnail) {
                    echo '<span style="max-width:250px; height:auto;">' . $study->study_thumbnail . '</span>';
                    echo '<br />';
                } ?>
                <strong><?php
                    echo $study->studytitle; ?></strong><br/>
                <span style="color:#9b9b9b;"><?php
                    echo $study->scripture1; ?> | <?php
                    echo $study->studydate; ?></span><br/>
                <div style="font-size:85%;margin-bottom:-17px;max-height:122px;overflow:hidden;"><?php
                    echo $study->teachername; ?></div>
                <br/>
                <div style="background: rgba(0, 0, 0, 0) linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, white 100%) repeat scroll 0 0;bottom: 0;height: 32px;margin-top: -32px; position: relative;width: 100%;"></div>
                <?php
                echo $study->media; ?>
            </div>
        </div>


    </div>
    <?php
} ?>
<div class="pagination-container pagelinks">
    <?php echo $this->pagination->getPageslinks(); ?>
</div>
