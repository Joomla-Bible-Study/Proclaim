<?php

/**
 * Teacher modal return — sends PostMessage back to parent with selected teacher data
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

/** @var CWM\Component\Proclaim\Administrator\View\Cwmteacher\HtmlView $this */

$icon    = 'icon-check';
$title   = $this->item ? $this->item->teachername : '';
$content = $this->item ? ($this->item->alias ?? '') : '';
$data    = ['contentType' => 'com_proclaim.teacher'];

if ($this->item) {
    $data['id']    = $this->item->id;
    $data['title'] = $this->item->teachername;
}

// Add Content select script
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('modal-content-select');

// The data for Content select script
$this->getDocument()->addScriptOptions('content-select-on-load', $data, false);

?>

<div class="px-4 py-5 my-5 text-center">
    <span class="fa-8x mb-4 <?php echo $icon; ?>" aria-hidden="true"></span>
    <h1 class="display-5 fw-bold"><?php echo $this->escape($title); ?></h1>
    <div class="col-lg-6 mx-auto">
        <p class="lead mb-4">
            <?php echo $this->escape($content); ?>
        </p>
    </div>
</div>
