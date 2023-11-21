<?php

/**
 * Helper for Template Code
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Html\HtmlHelper;

// Do not remove
// this is here to make sure that security of the site is maintained. It should be placed in every template file
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

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


<div class="row-fluid col-lg-12">
    <h2>
        Teachings
    </h2>
</div>


<div class="row-fluid col-lg-12 dropdowns"
     style="background-color:#A9A9A9; margin:0 -5px; padding:8px 8px; border:1px solid #C5C1BE; position:relative; -webkit-border-radius:10px;">

    <?php
    echo $this->page->books;
    echo $this->page->teachers;
    echo $this->page->series;
    $oddeven = '';
    $class1  = '#d3d3d3';
    $class2  = ''; ?>
</div>
<?php
foreach ($this->items as $study) {
    $oddeven = ($oddeven == $class1) ? $class2 : $class1;
    ?>
    <div style="width:100%;">
        <div class="col-lg-3">
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
<div class="row-fluid col-lg-12 pagination pagelinks" style="background-color: #A9A9A9;
    margin: 0 -5px;
    padding: 8px 8px;
    border: 1px solid #C5C1BE;
    position: relative;
    -webkit-border-radius: 9px;">
    <?php
    echo $this->pagination->getPageslinks(); ?>
</div>
