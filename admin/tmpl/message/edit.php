<?php
/**
 * Form
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Load the tooltip behavior.

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$this->configFieldsets  = array('editorConfig');
$this->hiddenFieldsets  = array('basic-limited');
$this->ignore_fieldsets = array('jmetadata', 'item_associations');

// Create shortcut to parameters.
$params = $this->form->getFieldsets('params');

$app   = Factory::getApplication();
$input = $app->input;

$return  = base64_encode('index.php?option=com_proclaim&task=message.edit&id=' . (int) $this->item->id);
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

Factory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function (task)
	{
		if (task == "message.cancel" || document.formvalidator.isValid(document.getElementById("message-form")))
		{
			Joomla.submitform(task, document.getElementById("message-form"));
		}
	};
');

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo JRoute::_('index.php?option=com_proclaim&view=message&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="message-form" class="form-validate" enctype="multipart/form-data">
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
				<?php echo JText::_('JBS_STY_HITS'); ?>
            </div>
            <div class="controls small">
				<?php echo '<input type="text" name="jform[hits]" id="jform_hits" value="' . $this->item->hits .
					'" class="readonly" size="10" readonly="" aria-invalid="false">';
				?>
            </div>
        </div>
        <div class="row-fluid" id="media">
            <table class="adminlist table table-striped">
                <thead>
                <tr>
                    <th class="center"><?php echo JText::_('JBS_CMN_EDIT_MEDIA_FILE'); ?></th>
                    <th class="center"><?php echo JText::_('JSTATUS'); ?></th>
                    <th class="center"><?php echo JText::_('JBS_CMN_MEDIA_CREATE_DATE'); ?></th>
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
								<?php $link = 'index.php?option=com_proclaim&amp;task=mediafile.edit&amp;id='
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
								<?php echo JHtml::_('jgrid.published', $item->published, $i, 'message.', true, 'cb', '', ''); ?>
                            </td>
                            <td class="center">
								<?php echo JHtml::_('date', $item->createdate, JText::_('DATE_FORMAT_LC4')); ?>
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
                        <td colspan="5" class="center"><?php echo JText::_('JBS_STY_NO_MEDIAFILES'); ?></td>
                    </tr>
				<?php endif; ?>

                </tbody>
                <tfoot>
                <tr>
                    <td colspan="5">
						<?php $link = 'index.php?option=com_proclaim&amp;task=mediafile.edit&amp;sid='
							. $this->form->getValue('id') . '&amp;options=' . $options . '&amp;return=' .
							$return . '&amp;' . JSession::getFormToken() . '=1'; ?>
						<?php
						if (empty($this->item->id))
						{
							?> <a onClick="Joomla.submitbutton('message.apply');"
                                  href="#"> <?php echo JText::_('JBS_STY_SAVE_FIRST'); ?> </a> <?php
						}
						else
						{
							?>
                            <a class="btn btn-primary" href="<?php echo $link; ?>"
                               title="<?php echo JText::_('JBS_STY_ADD_MEDIA_FILE'); ?>">
								<?php echo JText::_('JBS_STY_ADD_MEDIA_FILE'); ?></a> <?php
						}
						?>
                    </td>
                </tr>
                </tfoot>
            </table>
            <div class="control-group">
                <div class="control-label">
					<?php echo $this->form->getLabel('download_id'); ?>
                </div>
                <div class="controls">
					<?php echo $this->form->getInput('download_id'); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

        <!-- Begin Content -->
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('JBS_STY_DETAILS')); ?>
        <div class="row-fluid form-horizontal-desktop">
			<?php if (!$this->simple->mode){ ?>
                <div class="span6">
                    <div class="control-group">
						<?php echo $this->form->getLabel('studyintro'); ?>
						<?php echo $this->form->getInput('studyintro'); ?>
                    </div>
                    <div class="control-group">
						<?php echo $this->form->getLabel('studytext'); ?>
						<?php echo $this->form->getInput('studytext'); ?>
                    </div>
                </div>
			<?php } ?>
            <div class="span6 ">
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
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php if (!$this->simple->mode){ ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'info', JText::_('JBS_CMN_INFO')); ?>
            <div class="row-fluid">
                <div class="span6">
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
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php } ?>

	    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publish', JText::_('JBS_STY_PUBLISH')); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
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
	    <?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php if ($this->canDo->get('core.cwmadmin')): ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('JBS_CMN_FIELDSET_RULES')); ?>
            <div class="row-fluid">
				<?php echo $this->form->getInput('rules'); ?>
            </div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

        <!-- Hidden fields -->
		<?php echo $this->form->getInput('thumbnailm'); ?>
        <input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>
    </div>
</form>