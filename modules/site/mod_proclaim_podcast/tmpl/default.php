<?php

/**
 * Podcast Model default
 *
 * @package         Proclaim
 * @subpackage      mod_proclaim_podcast
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @link            https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/** @var SiteApplication $app */

if (empty($list)) {
    return;
}

$wa = $app->getDocument()->getWebAssetManager();
$wa->useStyle('com_proclaim.general');
$wa->useStyle('com_proclaim.podcast');

?>

<div class="mod_proclaim_podcast">
    <?php echo $list; ?>
</div>
