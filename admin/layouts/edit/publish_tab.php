<?php

/**
 * Reusable publishing/metadata tab layout for edit views.
 *
 * @package    Proclaim.Administrator
 * @subpackage Layout
 *
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @var mixed $displayData The view object (HtmlView instance).
 *                         Optional key 'tabName' can be passed as array: ['view' => $this, 'tabName' => 'myTab']
 */

// Support both direct view object and array with options
if (\is_array($displayData)) {
    $view    = $displayData['view'];
    $tabName = $displayData['tabName'] ?? 'myTab';
} else {
    $view    = $displayData;
    $tabName = 'myTab';
}

?>
<?php echo HTMLHelper::_('uitab.addTab', $tabName, 'publish', Text::_('JBS_STY_PUBLISH')); ?>
<div class="row">
    <div class="col-lg-12">
        <?php echo LayoutHelper::render('joomla.edit.publishingdata', $view); ?>
    </div>
</div>
<?php echo HTMLHelper::_('uitab.endTab'); ?>
