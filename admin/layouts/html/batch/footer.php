<?php

/**
 * Shared batch modal footer layout.
 *
 * Expects $displayData['submitTask'] — the controller task for the batch
 * (e.g. 'cwmmessage.batch').
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

$submitTask = $displayData['submitTask'] ?? '';
?>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
    <?php echo Text::_('JCANCEL'); ?>
</button>
<button type="submit" id="batch-submit-button-id" class="btn btn-success" data-submit-task="<?php echo htmlspecialchars($submitTask, ENT_QUOTES, 'UTF-8'); ?>">
    <?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
