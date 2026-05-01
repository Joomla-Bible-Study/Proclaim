<?php

/**
 * Merge Modal Footer
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

/** @var CWM\Component\Proclaim\Administrator\View\Cwmlocations\HtmlView $this */

$this->getDocument()->getWebAssetManager()->useScript('com_proclaim.cwmadmin-locations-merge');
?>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
    <?php echo Text::_('JCANCEL'); ?>
</button>
<button type="submit" id="merge-submit-button-id" class="btn btn-danger" data-submit-task="cwmlocations.merge">
    <?php echo Text::_('JBS_LOC_MERGE'); ?>
</button>
