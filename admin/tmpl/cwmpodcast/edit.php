<?php
/**
 * Form
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2007 - 2012 CWM Team All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           https://www.christianwebministries.org
 * */

// No Direct Access
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

// Create shortcut to parameters.
/** @type Joomla\Registry\Registry $params */
$params = $this->state->get('params');
$params = $params->toArray();
$app    = Factory::getApplication();
$input  = $app->input;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate')
	->addInlineScript('
	Joomla.submitbutton = function (task) {
		if (task == "cwmpodcast.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"))
		}
		else
		{
			alert("' . $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')) . '")
		}
	}
');
?>

<form action="<?php echo Route::_('index.php?option=com_proclaim&layout=edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="item-form" class="form-validate">


		<!-- Begin Content -->


					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('title'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('title'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('description'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('description'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('website'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('website'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('podcastlink'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('podcastlink'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('author'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('author'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('editor_name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('editor_name'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('editor_email'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('editor_email'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('podcastsearch'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('podcastsearch'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('podcastlanguage'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('podcastlanguage'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('detailstemplateid'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('detailstemplateid'); ?>
						</div>
					</div>


    <?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'images')); ?>

        <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'images', Text::_('JBS_STY_IMAGES')); ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('podcastimage'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('podcastimage'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('podcast_image_subscribe'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('podcast_image_subscribe'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('podcast_subscribe_desc'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('podcast_subscribe_desc'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('linktype'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('linktype'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('alternatelink'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('alternatelink'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('alternateimage'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('alternateimage'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('podcast_subscribe_show'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('podcast_subscribe_show'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('alternatewords'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('alternatewords'); ?>
						</div>
					</div>

    <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
				<?php if ($this->canDo->get('core.admin')): ?>
                    <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'Permissions', Text::_('JBS_CMN_PERMISSIONS')); ?>

						<?php echo $this->form->getInput('rules'); ?>

                    <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
				<?php endif; ?>


			<!-- End Content -->

		<!-- Begin Tab -->
    <?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('JBS_CMN_DETAILS')); ?>
			<div class="tab-pane" id="publishing">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('id'); ?>
					</div>
				</div>
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
						<?php echo $this->form->getLabel('filename'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('filename'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('podcastlimit'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('podcastlimit'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('episodetitle'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('episodetitle'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('custom'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('custom'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('episodesubtitle'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('episodesubtitle'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('customsubtitle'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('customsubtitle'); ?>
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
                        <?php echo $this->form->getLabel('linktype'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('linktype'); ?>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('podcast_subscribe_show'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('podcast_subscribe_show'); ?>
                    </div>
                </div>
			</div>

    <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
    <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

		<!-- End Sidebar -->

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
    <?php echo HTMLHelper::_('form.token'); ?>

</form>
