<?php

/**
 * Section permissions layout
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var CWM\Component\Proclaim\Administrator\View\Cwmpermissions\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useStyle('com_proclaim.general');

/**
 * A section's display name, falling back to the raw name.
 *
 * A section declared in access.xml but not yet translated must still be
 * reachable -- an untranslated label is a missing string, not a missing screen.
 */
$sectionLabel = static function (string $section): string {
    $key   = 'JBS_ADM_PERMISSIONS_SECTION_' . strtoupper($section);
    $label = Text::_($key);

    return $label === $key ? ucfirst($section) : $label;
};
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmpermissions&section=' . $this->section); ?>"
      method="post" name="adminForm" id="permissions-form" class="form-validate">

    <?php if ($this->sections === []) : ?>
        <div class="alert alert-warning">
            <?php echo Text::_('JBS_ADM_PERMISSIONS_NO_SECTIONS'); ?>
        </div>
    <?php else : ?>
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-3">
                    <div class="card-header">
                        <h2 class="h5 mb-0"><?php echo Text::_('JBS_ADM_PERMISSIONS_SECTIONS_HEADING'); ?></h2>
                    </div>
                    <nav class="list-group list-group-flush"
                         aria-label="<?php echo Text::_('JBS_ADM_PERMISSIONS_SECTIONS_HEADING'); ?>">
                        <?php foreach ($this->sections as $section) : ?>
                            <?php $isCurrent = $section === $this->section; ?>
                            <a class="list-group-item list-group-item-action<?php echo $isCurrent ? ' active' : ''; ?>"
                               href="<?php echo Route::_('index.php?option=com_proclaim&view=cwmpermissions&section=' . $section); ?>"
                                <?php echo $isCurrent ? ' aria-current="page"' : ''; ?>>
                                <?php echo $sectionLabel($section); ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h2 class="h5 mb-0"><?php echo $sectionLabel($this->section); ?></h2>
                    </div>
                    <div class="card-body">
                        <p class="text-muted"><?php echo Text::_('JBS_ADM_PERMISSIONS_DESC'); ?></p>
                        <?php echo $this->form->getInput('rules'); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php echo $this->form?->renderField('asset_id'); ?>
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
