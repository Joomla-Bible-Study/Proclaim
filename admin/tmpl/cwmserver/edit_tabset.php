<?php

/**
 * The server edit form's tab set — every tab, including the addon-driven ones.
 *
 * A sub-template on purpose: the in-place type swap re-renders exactly this
 * region (layout=tabs serves it alone), so the picker never has to submit the
 * form to show a different addon's fields. One source for both renders, or the
 * two drift.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$simple = Cwmhelper::getSimpleView();
?>
<div id="server-tabset-region">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general']); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_GENERAL')); ?>
        <div class="row">
            <div class="col-lg-9">
                <?php echo $this->form->renderField('server_name'); ?>
                <?php echo $this->form->renderField('type'); ?>
            </div>
            <div class="col-lg-3">
                <?php
                echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
                <?php
                if (isset($this->item->id, $this->item->addon)) : ?>
                    <span style="font-weight:bold">
                        <?php
                        echo $this->escape($this->item->addon->name); ?>
                    </span>
                    <?php
                endif; ?>
                <?php
                if (isset($this->item->id, $this->item->addon)) : ?>
                    <p><?php
                        echo $this->escape($this->item->addon->description); ?></p>
                    <?php
                endif; ?>
                <?php
                echo LayoutHelper::render('joomla.edit.global', $this); ?>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>
        <?php
        if ($this->server_form !== null) : ?>
            <?php
            if ($this->server_form->getFieldsets('params')) : ?>
                <?php
                foreach ($this->server_form->getFieldsets('params') as $fieldsets) : ?>
                    <?php
                    // An addon marks its own advanced settings with
                    // simplemode="hide", on the fieldset or on a single field.
                    // The addon knows what is advanced about itself, and a
                    // third-party one gets the same treatment without this
                    // template knowing it exists.
                    if ($simple->mode && ($fieldsets->simplemode ?? '') === 'hide') {
                        continue;
                    }
                    ?>
                    <?php
                    echo HTMLHelper::_(
                        'uitab.addTab',
                        'myTab',
                        strtolower(Text::_($fieldsets->label)),
                        Text::_($fieldsets->label)
                    ); ?>
                    <div class="row">
                        <div class="col-12 col-lg-12">
                            <?php
                            foreach ($this->server_form->getFieldset($fieldsets->name) as $field) : ?>
                                <?php
                                if ($simple->mode && $field->getAttribute('simplemode') === 'hide') {
                                    continue;
                                }
                                ?>
                                <?php echo $field->renderField(); ?>
                                <?php
                            endforeach; ?>
                        </div>
                    </div>
                    <?php
                    echo HTMLHelper::_('uitab.endTab'); ?>
                    <?php
                endforeach; ?>
                <?php
            endif; ?>
            <?php
            if ($this->server_form->getFieldsets('media')) : ?>
                <?php
                echo HTMLHelper::_('uitab.addTab', 'myTab', 'media_settings', Text::_('JBS_SVR_MEDIA_SETTINGS')); ?>
                <div class="row">
                    <div class="accordion" id="accordionlist">
                        <?php
                foreach ($this->server_form->getFieldsets('media') as $name => $fieldset) : ?>
                    <?php
                    if ($simple->mode && ($fieldset->simplemode ?? '') === 'hide') {
                        continue;
                    }
                    ?>
                            <div class="accordion-item">
                                <h2 class="accordion-heading" id="<?php
                        echo Text::_($name) ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse<?php
                                    echo Text::_($name) ?>" aria-expanded="false"
                                            aria-controls="collapse<?php
                                    echo Text::_($name) ?>">
                                        <?php
                                echo Text::_($fieldset->label); ?>
                                    </button>
                                </h2>
                                <div id="collapse<?php
                        echo Text::_($name) ?>" class="accordion-collapse collapse"
                                     aria-labelledby="heading<?php
                                echo $name; ?>"
                                     data-bs-parent="#accordionlist">
                                    <div class="accordion-body">
                                        <?php
                                foreach ($this->server_form->getFieldset($name) as $field) : ?>
                                    <?php
                                    if ($simple->mode && $field->getAttribute('simplemode') === 'hide') {
                                        continue;
                                    }
                                    ?>
                                            <?php echo $field->renderField(); ?>
                                            <?php
                                endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                endforeach; ?>
                    </div>
                </div>
                <?php
                echo HTMLHelper::_('uitab.endTab'); ?>
                <?php
            endif; ?>
            <?php
        endif; ?>
        <?php echo LayoutHelper::render('edit.permissions_tab', ['form' => $this->form, 'canDo' => $this->canDo, 'tabName' => 'myTab']); ?>
        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
</div>
