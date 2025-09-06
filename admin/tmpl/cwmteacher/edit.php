<?php
/**
 * Form
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$app = Factory::getApplication();
$input = $app->input;

// Set up defaults
if ($input->getInt('id')) {
    $teacher_thumbnail = $this->item->teacher_thumbnail;
} else {
    $teacher_thumbnail = $this->admin->params->get('default_teacher_image');
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->addInlineScript(
        "Joomla.submitbutton = function (task) {
			if (task === 'cwmteacher.cancel' || document.formvalidator.isValid(document.getElementById('teacher-form')))
			{
				Joomla.submitform(task, document.getElementById('teacher-form'))
			}
			else
			{
				alert('" . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . "')
			}
		}

		function jInsertFieldValue (value, id)
		{
			var old_id = document.id(id).value
			if (old_id !== id)
			{
				var elem = document.id(id)
				elem.value = value
				elem.fireEvent('change')
			}
		}
"
    );

$this->useCoreUI = true;

// In case of modal
$isModal = $input->get('layout') === 'modal';
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>
<form action="<?php
echo Route::_('index.php?option=com_proclaim&layout=' . $layout . $tmpl . '&id=' . (int)$this->item->id); ?>"
      method="post" name="adminForm" id="teacher-form" class="form-validate" enctype="multipart/form-data">
    <div class="row-fluid">
        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]
        ); ?>
        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JBS_CMN_DETAILS')); ?>
        <!-- Begin Content -->
        <div class="row">
            <div class="col-lg-9">
                <div class="well well-small">
                    <div class="control-group">
                        <div class="control-label">
                            <?php
                            echo $this->form->getLabel('teachername'); ?>
                        </div>
                        <div class="controls">
                            <?php
                            echo $this->form->getInput('teachername'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
                            <?php
                            echo $this->form->getLabel('alias'); ?>
                        </div>
                        <div class="controls">
                            <?php
                            echo $this->form->getInput('alias'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
                            <?php
                            echo $this->form->getLabel('contact'); ?>
                        </div>
                        <div class="controls">
                            <?php
                            echo $this->form->getInput('contact'); ?>
                            <?php
                            if ($this->form->getValue('contact')) {
                                ?>
                                <div class="button2-left">
                                    <div class="blank">
                                        <a href="<?php
                                        echo Route::_(
                                            'index.php?option=com_contact&task=contact.edit&id=' . (int)$this->form->getValue(
                                                'contact'
                                            )
                                        ); ?>"
                                           target="blank"
                                           class="btn"><?php
                                            echo Text::_('JBS_TCH_EDIT_THIS_CONTACT'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php
                            } ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
                            <?php
                            echo $this->form->getLabel('title'); ?>
                        </div>
                        <div class="controls">
                            <?php
                            echo $this->form->getInput('title'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
                            <?php
                            echo $this->form->getLabel('address'); ?>
                        </div>
                        <div class="controls">
                            <?php
                            echo $this->form->getInput('address'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
                            <?php
                            echo $this->form->getLabel('phone'); ?>
                        </div>
                        <div class="controls">
                            <?php
                            echo $this->form->getInput('phone'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
                            <?php
                            echo $this->form->getLabel('email'); ?>
                        </div>
                        <div class="controls">
                            <?php
                            echo $this->form->getInput('email'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        if ($this->item->teacher_thumbnail) : ?>
                            <img src="<?php
                            echo JUri::root() . $this->item->teacher_thumbnail; ?>"
                                 alt="<?php
                                 echo $this->form->getValue('teachername'); ?>"
                                 class="thumbnail center"/>
                        <?php
                        endif; ?>
                        <?php
                        echo $this->form->getLabel('image'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('image', null, $teacher_thumbnail); ?>
                    </div>
                </div>
                <hr/>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('published'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('published'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('access'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('access'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('list_show'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('list_show'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('landing_show'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('landing_show'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php
                        echo $this->form->getLabel('language'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo $this->form->getInput('language'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'information', Text::_('JBS_TCH_INFO')); ?>
        <div class="row">
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('short'); ?>
                </div>
                <div class="clr"></div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('short'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('information'); ?>
                </div>
                <div class="clr"></div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('information'); ?>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'links', Text::_('JBS_TCH_LINKS')); ?>
        <div class="row">
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('website'); ?>
                </div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('website'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('facebooklink'); ?>
                </div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('facebooklink'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('twitterlink'); ?>
                </div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('twitterlink'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('bloglink'); ?>
                </div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('bloglink'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('link1'); ?>
                </div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('link1'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('linklabel1'); ?>
                </div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('linklabel1'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('link2'); ?>
                </div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('link2'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('linklabel2'); ?>
                </div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('linklabel2'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('link3'); ?>
                </div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('link3'); ?>
                </div>
            </div>
            <div class="control-group">
                <div class="control-label">
                    <?php
                    echo $this->form->getLabel('linklabel3'); ?>
                </div>
                <div class="controls">
                    <?php
                    echo $this->form->getInput('linklabel3'); ?>
                </div>
            </div>
        </div>
        <?php
        echo HTMLHelper::_('uitab.endTab'); ?>

        <?php
        if ($this->canDo->get('core.admin')): ?>
            <?php
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('JBS_ADM_ADMIN_PERMISSIONS')); ?>
            <div class="row-fluid">
                <?php
                echo $this->form->getInput('rules'); ?>
            </div>
            <?php
            echo HTMLHelper::_('uitab.endTab'); ?>
        <?php
        endif; ?>
    </div>
    <?php
    echo $this->form->getInput('id'); ?>
    <?php
    echo $this->form->getInput('teacher_image'); ?>
    <input type="hidden" name="task" value=""/>
    <?php
    echo HTMLHelper::_('form.token'); ?>
</form>
