<?php

/**
 * Reusable permissions tab layout for edit views.
 *
 * @package    Proclaim.Administrator
 * @subpackage Layout
 *
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Expected $displayData keys:
 *   'form'    - Joomla\CMS\Form\Form instance
 *   'canDo'   - CMSObject with permission flags
 *   'tabName' - (optional) tab set name, defaults to 'myTab'
 */

$form    = $displayData['form'];
$canDo   = $displayData['canDo'];
$tabName = $displayData['tabName'] ?? 'myTab';

if (!$canDo->get('core.admin')) {
    return;
}
?>
<?php echo HTMLHelper::_('uitab.addTab', $tabName, 'permissions', Text::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
<div class="row">
    <div class="col-lg-12">
        <?php echo $form->getInput('rules'); ?>
    </div>
</div>
<?php echo HTMLHelper::_('uitab.endTab'); ?>
