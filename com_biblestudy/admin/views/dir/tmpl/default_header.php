<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No direct access
defined('_JEXEC') or die();
$first_bc = true;
?>
<div id="fm_breadcrumbs">

	<!---BreadCrumbs-->
	<ul class="bc_list">
		<?php foreach ($this->breadcrumbs as $bc) : ?>
			<li>
				<?php if (!$first_bc) : ?>
					&GT;
				<?php endif; ?>
				<a href="index.php?option=com_biblestudy&view=dir&tmpl=component&dir=<?php echo $bc->link; ?>"><?php echo $bc->name ?></a>
			</li>
			<?php $first_bc = false; endforeach; ?>
		<li><a id="new_folder" href="#"><img src="<?php echo $this->imgURL ?>folder_new.png"/></a></li>
	</ul>
	<!---/BreadCrumbs-->

	<!---New Folder-->
	<div id="folder_input_form">
		<form id="input_form" action="" method="post">
			<label for="folder_name"><?php echo JText::_('COM_MEDIAMU_DIR_BROSWER_LB_FOLDER_NAME'); ?></label>
			<input id="folder_name" type="text" name="folder_name" value=""/>
			<input id="create_folder" type="submit"
			       value="<?php echo JText::_('COM_MEDIAMU_DIR_BROSWER_BTN_CREATE'); ?>"/>
			<input id="current_folder" type="hidden" name="current_folder" value="<?php echo $this->currentFolder; ?>"/>
			<input id="token" type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1"/>
		</form>
	</div>
	<!---/New Folder-->
</div>

<!---Files and Folders table-->
<form id="delete_form" action="" method="post">
	<table id="files">
		<thead>
		<tr>
			<th scope="row" class="file_icon"><span id="proccess"> </span></th>
			<th scope="row" class="file_name">
				<?php echo JText::_('COM_MEDIAMU_DIR_BROSWER_FILE_NAME'); ?>
			</th>
			<th scope="row" class="size">
				<?php echo JText::_('COM_MEDIAMU_DIR_BROSWER_SIZE'); ?>
			</th>
			<th scope="row" class="selection"><a class="select" id="select" href="#"></a></th>
		</tr>
		</thead>
		<tbody>
