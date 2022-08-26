<?php
/**
 * Form
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

$this->configFieldsets  = array('editorConfig');
$this->hiddenFieldsets  = array('basic-limited');
$this->ignore_fieldsets = array('jmetadata', 'item_associations');
$this->canDo            = CWMProclaimHelper::getActions('', 'message');

// Create shortcut to parameters.
$params = $this->form->getFieldsets('params');

$app   = Factory::getApplication();
$input = $app->input;

$return  = base64_encode('index.php?option=com_proclaim&task=cwmmessage.edit&id=' . (int) $this->item->id);
$options = base64_encode('study_id=' . $this->item->id . '&createdate=' . $this->item->studydate);

// Set up defaults
if ($input->getInt('id'))
{
	$booknumber  = $this->item->booknumber;
	$thumbnailm  = $this->item->thumbnailm;
	$teacher_id  = $this->item->teacher_id;
	$location_id = $this->item->location_id;
	$series_id   = $this->item->series_id;
	$messagetype = $this->item->messagetype;
	$thumbnailm  = $this->item->thumbnailm;
	$user_id     = $this->item->user_id;
}
else
{
	$booknumber  = $this->admin_params->get('booknumber');
	$thumbnailm  = $this->admin_params->get('default_study_image');
	$teacher_id  = $this->admin_params->get('teacher_id');
	$location_id = $this->admin_params->get('location_id');
	$series_id   = $this->admin_params->get('series_id');
	$messagetype = $this->admin_params->get('messagetype');
	$thumbnailm  = $this->admin_params->get('default_study_image');
	$user_id     = $this->admin->user_id;
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->addInlineScript('
	Joomla.submitbutton = function (task) {
		if (task == "cwmmessage.cancel" || document.formvalidator.isValid(document.getElementById("message-form")))
		{
			Joomla.submitform(task, document.getElementById("message-form"));
		}
		else
		{
			alert("' . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . '")
		}
	}
');

// In case of modal
$isModal = $input->get('layout') === 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>
<form action="<?php echo Route::_('index.php?option=com_proclaim&view=cwmmessageform&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="message-form" class="form-validate" enctype="multipart/form-data">
	<?= HTMLHelper::_('form.token'); ?>
    <input type="hidden" name="option" value="com_proclaim">
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <div class="form-inline form-inline-header">
        <div class="control-group">
            <div class="control-label">
				<?php echo $this->form->getLabel('studytitle'); ?>
            </div>
            <div class="controls">
				<?php echo $this->form->getInput('studytitle'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
				<?php echo $this->form->getLabel('alias'); ?>
            </div>
            <div class="controls">
				<?php echo $this->form->getInput('alias'); ?>
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
				<?php echo Text::_('JBS_STY_HITS'); ?>
            </div>
            <div class="controls small">
				<?php echo '<input type="text" name="jform[hits]" id="jform_hits" value="' . $this->item->hits .
					'" class="readonly" size="10" readonly="" aria-invalid="false">';
				?>
            </div>
        </div>
        <div class="row-fluid" id="media">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th class="center"><?php echo Text::_('JBS_CMN_EDIT_MEDIA_FILE'); ?></th>
                    <th class="center"><?php echo Text::_('JSTATUS'); ?></th>
                    <th class="center"><?php echo Text::_('JBS_CMN_MEDIA_CREATE_DATE'); ?></th>
                    <th class="center hidden-phone">Language</th>
                    <th class="center hidden-phone">Access</th>
                    <th class="center hidden-phone">ID</th>
                </tr>
                </thead>
                <tbody>

				<?php
				if (count($this->mediafiles) > 0) :
					foreach ($this->mediafiles as $i => $item) :
						?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td>
								<?php $link = 'index.php?option=com_proclaim&amp;task=mediafileform.edit&amp;id='
									. (int) $item->id . '&amp;return=' . $return . '&amp;options=' . $options; ?>
                                <a class="btn btn-primary" href="<?php echo $link; ?>"
                                   title="<?php echo $this->escape($item->params->get('filename'))
									   ? $this->escape($item->params->get('filename'))
									   : $this->escape($item->params->get('media_image_name')); ?>">
									<?php echo($this->escape($item->params->get('filename'))
										? $this->escape($item->params->get('filename'))
										: $this->escape($item->params->get('media_image_name'))); ?>
                                </a>
                            </td>
                            <td class="center">
								<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'message.', true, 'cb', '', ''); ?>
                            </td>
                            <td class="center">
								<?php echo HTMLHelper::_('date', $item->createdate, Text::_('DATE_FORMAT_LC4')); ?>
                            </td>
                            <td class="center hidden-phone">
								<?php echo $item->language; ?>
                            </td>
                            <td class="center hidden-phone">
								<?php echo $item->access_level; ?>
                            </td>
                            <td class="center hidden-phone">
								<?php echo $item->id; ?>
                            </td>

                        </tr>
					<?php
					endforeach;
				else:
					?>
                    <tr>
                        <td colspan="5" class="center"><?php echo Text::_('JBS_STY_NO_MEDIAFILES'); ?></td>
                    </tr>
				<?php endif; ?>

                </tbody>
                <tfoot>
                <tr>
                    <td colspan="6">
						<?php $link = 'index.php?option=com_proclaim&amp;task=cwmmediafileform.edit&amp;sid='
							. $this->form->getValue('id') . '&amp;options=' . $options . '&amp;return=' .
							$return . '&amp;' . JSession::getFormToken() . '=1'; ?>
						<?php
						if (empty($this->item->id))
						{
							?> <a onClick="Joomla.submitbutton('message.apply');"
                                  href="#"> <?php echo Text::_('JBS_STY_SAVE_FIRST'); ?> </a> <?php
						}
						else
						{
							?>
                            <a class="btn btn-primary" href="<?php echo $link; ?>"
                               title="<?php echo Text::_('JBS_STY_ADD_MEDIA_FILE'); ?>">
								<?php echo Text::_('JBS_STY_ADD_MEDIA_FILE'); ?></a> <?php
						}
						?>
                    </td>
                </tr>
                </tfoot>
            </table>
            <!--            <div class="control-group">-->
            <!--                <div class="control-label">-->
            <!--					--><?php //echo $this->form->getLabel('download_id'); ?>
            <!--                </div>-->
            <!--                <div class="controls">-->
            <!--					--><?php //echo $this->form->getInput('download_id'); ?>
            <!--                </div>-->
            <!--            </div>-->
        </div>
    </div>

    <div class="form-horizontal">
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

        <!-- Begin Content -->
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('JBS_STY_DETAILS')); ?>
        <div class="row">
			<?php if (!$this->simple->mode) { ?>
                <div class="col-lg-7">
                    <div>
                        <fieldset class="adminform">
							<?php echo $this->form->getLabel('studyintro'); ?>
							<?php echo $this->form->getInput('studyintro'); ?>
                        </fieldset>
                        <fieldset class="adminform">
							<?php echo $this->form->getLabel('studytext'); ?>
							<?php echo $this->form->getInput('studytext'); ?>
                        </fieldset>
                    </div>
                </div>
			<?php } ?>
            <div class="col-lg-5">
                <div class="control-group">
                    <div class="control-label">
						<?php echo $this->form->getLabel('published'); ?>
                    </div>
                    <div class="controls">
						<?php echo $this->form->getInput('published'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
						<?php echo $this->form->getLabel('studydate'); ?>
                    </div>
                    <div class="controls span10 small">
						<?php echo $this->form->getInput('studydate'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
						<?php echo $this->form->getLabel('image'); ?>
                    </div>
                    <div class="controls">
						<?php echo $this->form->getInput('image', null, $thumbnailm); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
						<?php echo $this->form->getLabel('teacher_id'); ?>
                    </div>
                    <div class="controls">
						<?php echo $this->form->getInput(
							'teacher_id',
							null,
							$teacher_id
						); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
						<?php echo $this->form->getLabel('series_id'); ?>
                    </div>
                    <div class="controls">
						<?php echo $this->form->getInput(
							'series_id',
							null,
							$series_id
						); ?>
                    </div>
                </div>
                <div class="control-group">
					<?php echo $this->form->renderFieldset('scripture'); ?>
                </div>

            </div>
        </div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php if (!$this->simple->mode) { ?>
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'info', Text::_('JBS_CMN_INFO')); ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('location_id'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput(
								'location_id',
								null,
								$location_id
							); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('studynumber'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput('studynumber'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('comments'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput('comments'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('access'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput('access'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('language'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput('language'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('topics'); ?>
                        </div>
                        <div class="clr"></div>
                        <div class="controls">
							<?php echo $this->form->getInput('topics'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('messagetype'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput(
								'messagetype',
								null,
								$messagetype
							) ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
							<?php echo $this->form->getLabel('thumbnailm'); ?>
                        </div>
                        <div class="controls">
							<?php echo $this->form->getInput(
								'thumbnailm',
								null,
								$thumbnailm
							); ?>
                        </div>
                    </div>
                </div>
            </div>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php } ?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'publish', Text::_('JBS_STY_PUBLISH')); ?>
        <div class="row">
            <div class="col-lg-12">
				<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
            </div>
            <div class="span6">
                <div class="control-group">
                    <div class="control-label">
						<?php echo $this->form->getLabel('metakey', 'params'); ?>
                    </div>
                    <div class="clr"></div>
                    <div class="controls">
						<?php echo $this->form->getInput('metakey', 'params'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
						<?php echo $this->form->getLabel('metadesc', 'params'); ?>
                    </div>
                    <div class="clr"></div>
                    <div class="controls">
						<?php echo $this->form->getInput('metadesc', 'params'); ?>
                    </div>
                </div>
				<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
            </div>
        </div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php if ($this->canDo->get('core.admin')): ?>
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'permissions', Text::_('JBS_CMN_FIELDSET_RULES')); ?>
            <div class="row">
				<?php echo $this->form->getInput('rules'); ?>
            </div>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php endif; ?>
		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

        <!-- Hidden fields -->
		<?php echo $this->form->getInput('thumbnailm'); ?>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="return" value="<?php echo $input->getBase64('return'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>