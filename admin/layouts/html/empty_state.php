<?php

/**
 * Reusable empty-state alert layout.
 *
 * @var   array  $displayData  Optional keys:
 *                             - 'message' (string) Language key; defaults to JGLOBAL_NO_MATCHING_RESULTS
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

$message = $displayData['message'] ?? 'JGLOBAL_NO_MATCHING_RESULTS';
?>
<div class="alert alert-info">
    <span class="icon-info-circle" aria-hidden="true"></span><span
            class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
    <?php echo Text::_($message); ?>
</div>
