<?php

/**
 * Converter Template
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

/** @var CWM\Component\Proclaim\Administrator\View\Cwmmediafile\HtmlView $this */

?>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
    <?php echo Text::_('JCANCEL'); ?>
</button>
<button type="button" class="btn btn-success" id="btn-transfer-filesize" data-bs-dismiss="modal">
    <?php echo Text::_('JBS_MED_CONVERTER'); ?>
</button>
