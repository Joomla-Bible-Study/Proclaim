<?php

/**
 * Default View file
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Site\View\Cwmseriesdisplay\HtmlView $this */

?>
<div class="com-proclaim">
<?php
if ($this->params->get('useexpert_seriesdetail') > 0) {
    echo $this->loadTemplate('custom');
} elseif ($this->params->get('seriesdisplaytemplate')) {
    echo $this->loadTemplate($this->params->get('seriesdisplaytemplate'));
} else {
    echo $this->loadTemplate('main');
}
?>
</div>
