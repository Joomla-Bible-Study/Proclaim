<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No direct access
defined('_JEXEC') or die();

if (is_array($this->files))
	: ?>
	<?php foreach ($this->files as $i => $file)
		: ?>
		<tr>
			<td class="file_icon file">
				<a class="finfo" id="file_info<?php echo $i; ?>" href="#"><img
						src="<?php echo $this->imgURL . 'ext/' . strtolower($file->ext) . '.png'; ?>"/></a>

				<div class="file_info<?php echo $i; ?> tooltip">
					<div class="prev_header">
						<?php echo $file->basename; ?>
					</div>
					<div class="prev_left">
						<img alt="preview not available"
						     src="<?php echo (!is_array($file->imgInfo)) ? $this->imgURL . 'ext/' . $file->ext . '.png' : $file->link; ?>"/>
					</div>
					<div class="prev_right">
						<?php if (is_array($file->imgInfo))
							: ?>
							<span><?php echo JText::_('COM_MEDIAMU_FINFO_DIMS') . '</span> ' . JText::_('COM_MEDIAMU_FINFO_WIDTH'); ?>
							<?php echo $file->imgInfo[0] . JText::_('COM_MEDIAMU_FINFO_HEIGHT') . ' ' . $file->imgInfo[1]; ?>
							<br/>
						<?php
endif;
						?>
						<span><?php echo JText::_('COM_MEDIAMU_FINFO_SIZE') . '</span>' . $file->size; ?><br/>
						<span><?php echo JText::_('COM_MEDIAMU_FINFO_L_ACCESSED') . '</span> ' . $file->accessTime; ?>
						<br/>
						<span><?php echo JText::_('COM_MEDIAMU_FINFO_L_MODIFIED') . '</span> ' . $file->modifiedTime; ?>
						<br/>
					</div>
					<div class="prev_footer">
						<a class="open_btn" target="_blank" href="<?php echo $file->link; ?>">
							<?php echo JText::_('COM_MEDIAMU_FINFO_OPEN'); ?></a>
						<span>|</span> <a class="path_rm_btn" name="<?php echo base64_encode($file->basename); ?>"
						                  href="#"><?php echo JText::_('COM_MEDIAMU_FINFO_DELETE'); ?></a>
					</div>
				</div>
			</td>
			<td class="file_name">
				<?php echo $file->basename; ?>
			</td>
			<td class="size">
				<?php echo $file->size; ?>
			</td>
			<td class="selection">
				<input class="delete" name="paths[]" type="checkbox"
				       value="<?php echo base64_encode($file->basename); ?>"/>
			</td>
		</tr>
	<?php
endforeach; ?>
<?php
endif;
?>

