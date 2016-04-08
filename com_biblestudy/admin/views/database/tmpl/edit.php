<?php
/**
 * Admin form subset database
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * @since      7.1.0
 * */
// No direct access
defined('_JEXEC') or die;
?>
<div id="installer-database">
	<form action="index.php" method="post" name="adminForm" id="item-database">
		<div id="j-main-container">
			<?php if ($this->errorCount === 0) : ?>
			<div class="alert alert-info">
				<a class="close" data-dismiss="alert" href="#">&times;</a>
				<?php echo JText::_('COM_INSTALLER_MSG_DATABASE_OK'); ?>
			</div>

			<ul class="nav nav-tabs">
				<li class="active"><a href="#other"
				                      data-toggle="tab"><?php echo JText::_('COM_INSTALLER_MSG_DATABASE_INFO'); ?></a>
				</li>
			</ul>

			<div class="tab-content">

				<?php else : ?>
				<div class="alert alert-error">
					<a class="close" data-dismiss="alert" href="#">&times;</a>
					<?php echo JText::_('COM_INSTALLER_MSG_DATABASE_ERRORS'); ?>
				</div>

				<ul class="nav nav-tabs">
					<li class="active"><a href="#problems"
					                      data-toggle="tab"><?php echo JText::plural('COM_INSTALLER_MSG_N_DATABASE_ERROR_PANEL', $this->errorCount); ?>
							<span class="badge badge-info"><?php echo $this->errorCount; ?></span></a></li>
					<li><a href="#other"
					       data-toggle="tab"><?php echo JText::_('COM_INSTALLER_MSG_DATABASE_INFO'); ?></a>
					</li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane active" id="problems">
						<fieldset class="panelform">
							<ul>
								<?php if (!$this->filterParams) : ?>
								<li><?php echo JText::_('COM_INSTALLER_MSG_DATABASE_FILTER_ERROR'); ?>
									<?php endif; ?>

									<?php if (!(strncmp($this->schemaVersion, $this->jversion, 5) === 0)) : ?>
								<li><?php echo JText::sprintf('JBS_INS_DATABASE_SCHEMA_DOES_NOT_MATCH', $this->schemaVersion, $this->jversion); ?></li>
							<?php endif; ?>

								<?php if (($this->updateVersion != $this->jversion)) : ?>
									<li><?php echo JText::sprintf('JBS_INS__MSG_DATABASE_UPDATEVERSION_ERROR', $this->updateVersion, $this->jversion); ?></li>
								<?php endif; ?>

								<?php foreach ($this->errors as $line => $error) : ?>
									<?php
									$key     = 'COM_INSTALLER_MSG_DATABASE_' . $error->queryType;
									$msgs    = $error->msgElements;
									$file    = basename($error->file);
									$msg0    = (isset($msgs[0])) ? $msgs[0] : ' ';
									$msg1    = (isset($msgs[1])) ? $msgs[1] : ' ';
									$msg2    = (isset($msgs[2])) ? $msgs[2] : ' ';
									$message = JText::sprintf($key, $file, $msg0, $msg1, $msg2);
									?>
									<li><?php echo $message; ?></li>
								<?php endforeach; ?>
							</ul>
						</fieldset>
					</div>
					<?php endif; ?>

					<div class="tab-pane" id="other">
						<fieldset class="panelform">
							<ul>
								<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_VERSION', $this->schemaVersion); ?></li>
								<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_UPDATE_VERSION', $this->updateVersion); ?></li>
								<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_DRIVER', JFactory::getDbo()->name); ?></li>
								<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_CHECKED_OK', count($this->results['ok'])); ?></li>
								<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SKIPPED', count($this->results['skipped'])); ?></li>
							</ul>
						</fieldset>
					</div>
				</div>
				<input type="hidden" name="option" value="com_biblestudy"/>
				<input type="hidden" name="task" value="fix"/>
				<input type="hidden" name="boxchecked" value="0"/>
				<?php echo JHtml::_('form.token'); ?>
			</div>
	</form>
</div>

