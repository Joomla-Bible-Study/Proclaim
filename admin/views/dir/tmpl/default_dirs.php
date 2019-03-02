<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No direct access
defined('_JEXEC') or die();
$first = true;
?>
<?php if (is_array($this->folders)) : ?>
	<?php foreach ($this->folders as $index => $folder) : ?>
		<?php if ($first)
		{
			?>
			<tr>
				<td class="file_icon folder">
					<a class="folder_link"
					   href="index.php?option=com_biblestudy&view=dir&tmpl=component&dir=<?php echo $folder->parentShort; ?>"><img
							src="<?php echo $this->imgURL . 'ext/_folder_up.png'; ?>"/></a>
				</td>
				<td class="file_name">
					<a class="folder_link"
					   href="index.php?option=com_biblestudy&view=dir&tmpl=component&dir=<?php echo $folder->parentShort; ?>">..</a>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<?php
			$first = false;
		}
		else
		{
			?>
			<tr>
				<td class="file_icon folder">
					<a class="finfo" id="folder_info<?php echo $index; ?>"
					   href="index.php?option=com_biblestudy&view=dir&tmpl=component&dir=<?php echo $folder->folderLink; ?>"><img
							src="<?php echo ($folder->fileCount == 0 && $folder->folderCount == 0) ? $this->imgURL .
								'ext/_folder.png' : $this->imgURL . 'ext/_folder_open.png'; ?>"/></a>

					<div class="folder_info<?php echo $index; ?> tooltip">
						<div class="prev_header">
							<?php echo $folder->basename; ?>
						</div>
						<div class="prev_left">
							<img alt="preview not available"
							     src="<?php echo ($folder->fileCount == 0 && $folder->folderCount == 0) ? $this->imgURL .
								     'ext/_folder.png' : $this->imgURL . 'ext/_folder_open.png'; ?>"/>
						</div>
						<div class="prev_right">
							<span class="label"><?php echo JText::_('COM_MEDIAMU_FINFO_CONTAINS'); ?></span><br/>
							<?php echo $folder->fileCount; ?> <span
								class="label"><?php echo JText::_('COM_MEDIAMU_FINFO_FILES_AND'); ?></span> <?php echo $folder->folderCount; ?>
							<span class="label"><?php echo JText::_('COM_MEDIAMU_FINFO_FOLDERS'); ?></span> <br/>

						</div>
						<div class="prev_footer">
							<a href="index.php?option=com_biblestudy&view=dir&tmpl=component&dir=<?php echo $folder->folderLink; ?>">
								<?php echo JText::_('COM_MEDIAMU_FINFO_OPEN'); ?></a>
							<span>|</span> <a class="path_rm_btn" name="<?php echo base64_encode($folder->basename); ?>"
							                  href="#"><?php echo JText::_('COM_MEDIAMU_FINFO_DELETE'); ?></a>
						</div>
					</div>
				</td>
				<td class="file_name">
					<a class="folder_link"
					   href="index.php?option=com_biblestudy&view=dir&tmpl=component&dir=<?php echo $folder->folderLink; ?>">
						<?php echo $folder->basename; ?></a>
				</td>
				<td>&nbsp;</td>
				<td class="selection">
					<input class="delete" name="paths[]" type="checkbox"
					       value="<?php echo base64_encode($folder->basename); ?>"/>
				</td>
			</tr>

		<?php } //endif ?>

	<?php endforeach; ?>

<?php endif; ?>
