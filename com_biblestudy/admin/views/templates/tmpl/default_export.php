<?php
/**
 * Form for exporting and importing template settings and files
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JHtml::_('bootstrap.framework');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
/**
 * View class for Templates
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
?>
<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=templates'); ?>"
      method="post" name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
		<hr/>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
			<?php endif; ?>
			<div class="span6">
				<h2><?php echo JText::_('JBS_CMN_EXPORT'); ?></h2>
				<span class="btn btn-default"><?php echo $this->templates; ?>
					<input type="submit" class="btn btn-default" value="<?php echo JText::_('JBS_CMN_SUBMIT'); ?>"
					       onclick="Joomla.submitbutton('templates.template_export')"/></span>
			</div>
			<div class="input-append span6">
				<h2><?php echo JText::_('JBS_CMN_IMPORT'); ?></h2>
					<span class="btn btn-default btn-file">
						<input class="file" id="template_import" name="template_import" type="file" size="57"/>
							<input type="submit" class="btn btn-default"
							       value="<?php echo JText::_('JBS_CMN_SUBMIT'); ?>"
							       onclick="Joomla.submitbutton('templates.template_import')"/>
				</span>
			</div>
			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>
