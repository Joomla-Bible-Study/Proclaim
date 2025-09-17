<?php

/**
 * @package     Proclaim.Admin
 * @subpackage  mod_proclaimicon
 *
 * @copyright    (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

defined('_JEXEC') or die;

use CWM\Module\Proclaimicon\Administrator\Helper\ProclaimIconHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Joomla\CMS\Application\CMSApplication $app */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('core')
    ->useScript('bootstrap.dropdown');
$wa->registerAndUseScript('mod_quickicon', 'mod_quickicon/quickicon.min.js', ['relative' => true, 'version' => 'auto'], ['type' => 'module']);
$wa->registerAndUseScript('mod_quickicon-es5', 'mod_quickicon/quickicon-es5.min.js', ['relative' => true, 'version' => 'auto'], ['nomodule' => true, 'defer' => true]);

/** @var ProclaimIconHelper $buttons */
$html = HTMLHelper::_('icons.buttons', $buttons);
/** @var stdClass $module */
?>
<?php if (!empty($html)) : ?>
    <nav class="quick-icons px-3 pb-3" aria-label="<?php echo Text::_('MOD_PROCLAIMICON_NAV_LABEL') . ' ' . $module->title; ?>">
        <ul class="nav flex-wrap">
            <?php echo $html; ?>
        </ul>
    </nav>
<?php endif; ?>
