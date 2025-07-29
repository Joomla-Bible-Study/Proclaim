<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 **/

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<label id="batch-language-lbl" for="batch-player">
    <?php echo Text::_('JBS_MED_PLAYER_DESC') ; ?>
</label>
<select name="batch[player]" class="form-select" id="batch-player">
    <option value=""><?php echo Text::_('JBS_BAT_PLAYER_NOCHANGE'); ?></option>
    <?php echo HTMLHelper::_('select.options', HTMLHelper::_('proclaim.playerlist', true, true), 'value', 'text'); ?>
</select>