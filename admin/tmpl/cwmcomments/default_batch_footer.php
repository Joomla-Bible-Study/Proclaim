<?php

/**
 * Batch Template
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmcomments\HtmlView $this */

$published = $this->state->get('filter.published');

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_proclaim.cwmadmin-batch-footer');

?>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
    <?php
    echo Text::_('JCANCEL'); ?>
</button>
<button type="submit" id='batch-submit-button-id' class="btn btn-success" data-submit-task='cwmcomment.batch'>
    <?php
    echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
