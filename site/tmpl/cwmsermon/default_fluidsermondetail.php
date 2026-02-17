<?php

/**
 * Helper for Template Code
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmsermon\HtmlView $this */

?>
<div class="container-fluid">


    <div class="row">
        <div class="col-12">
            <h3 class="text-end">
                <?php echo $this->escape($this->params->get('list_page_title', Text::_('JBS_CMN_MESSAGES'))); ?>
            </h3>
        </div>
        <div class="col-12">
            <h4 class="text-end">
                with <?php
                echo $this->item->teachername; ?>
            </h4>
        </div>
    </div>
    <?php
$isPrint = !empty($this->print);
if (!$isPrint) {
    echo $this->page->social;
}
?>
    <br/>
    <h2 style="text-align:center;">
        <?php
    echo $this->item->studytitle; ?>
    </h2>
    <h4 style="text-align:center;">
        <strong><?php
        echo !empty($this->item->allScriptures) ? $this->item->allScriptures : $this->item->scripture1; ?></strong></h4>
    <?php
echo $this->item->media; ?>
    <p>
        <?php
    echo $this->item->studytext; ?>
    </p>
</div>
