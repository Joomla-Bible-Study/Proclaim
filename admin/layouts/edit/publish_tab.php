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
 * Reusable Publishing tab for Proclaim edit views.
 *
 * Mirrors Joomla core's Content publishing tab — a two-column layout
 * with Publishing (publish_up/down, created/modified, id, etc.) on the
 * left and Metadata (metakey, metadesc, metadata fieldset) on the right,
 * with the Metadata column only appearing when the form actually has
 * those fields.
 *
 * @var mixed $displayData Either the view object directly, or an array:
 *                         ['view' => $this, 'tabName' => 'myTab']
 */

if (\is_array($displayData)) {
    $view    = $displayData['view'];
    $tabName = $displayData['tabName'] ?? 'myTab';
} else {
    $view    = $displayData;
    $tabName = 'myTab';
}

$form       = $view->getForm();
$hasMetakey = (bool) $form->getField('metakey', 'params') || (bool) $form->getField('metakey');
$hasMetadesc = (bool) $form->getField('metadesc', 'params') || (bool) $form->getField('metadesc');
$hasMetadata = $hasMetakey || $hasMetadesc || (bool) $form->getFieldset('metadata');

$leftCol  = $hasMetadata ? 'col-lg-6' : 'col-lg-12';
?>
<?php echo HTMLHelper::_('uitab.addTab', $tabName, 'publish', Text::_('JBS_STY_PUBLISH')); ?>
<div class="row">
    <div class="<?php echo $leftCol; ?>">
        <fieldset class="adminform">
            <legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
            <?php echo LayoutHelper::render('joomla.edit.publishingdata', $view); ?>
        </fieldset>
    </div>
    <?php if ($hasMetadata) : ?>
    <div class="col-lg-6">
        <fieldset class="adminform">
            <legend><?php echo Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
            <?php if ($hasMetakey) : ?>
                <?php echo $form->renderField('metakey', $form->getField('metakey', 'params') ? 'params' : null); ?>
            <?php endif; ?>
            <?php if ($hasMetadesc) : ?>
                <?php echo $form->renderField('metadesc', $form->getField('metadesc', 'params') ? 'params' : null); ?>
            <?php endif; ?>
            <?php echo LayoutHelper::render('joomla.edit.metadata', $view); ?>
        </fieldset>
    </div>
    <?php endif; ?>
</div>
<?php echo HTMLHelper::_('uitab.endTab'); ?>
