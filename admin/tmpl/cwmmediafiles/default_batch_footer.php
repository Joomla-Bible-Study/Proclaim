<?php

/**
 * Batch Template
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

$published = $this->state->get('filter.published');
?>
<a class="btn" type="button"
   onclick="document.id('batch-mediafiles-id');document.id('batch-access').value=''" data-dismiss="modal">
    <?php
    echo Text::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('mediafile.batch');">
    <?php
    echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
