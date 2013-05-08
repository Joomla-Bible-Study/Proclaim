<?php
/**
 * Teachers view subset main
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

?>
<div id="biblestudy" class="noRefTagger">
	<table class="table table-striped" id="bsm_teachertable_list">
		<tbody>
		<tr class="titlerow">
			<td style="text-align: center" colspan="3" class="title">
				<?php echo $this->params->get('teacher_title', JText::_('JBS_TCH_OUR_TEACHERS')); ?>
			</td>
		</tr>
		<?php
		$class1 = 'bsodd';
		$class2 = 'bseven';
		$oddeven = $class1;

		foreach ($this->items as $item)
		{
			$teacherdisplay = $item->teachername;

			if ($item->title)
			{
				$teacherdisplay .= ' - ' . $item->title;
			}
			// Alternate the row color
			$oddeven = ($oddeven == $class1) ? $class2 : $class1;
			?>

			<tr class="<?php echo $oddeven; ?> ">
				<td class="bsm_teacherthumbnail_list">
					<?php
					if ($item->thumb || $item->teacher_thumbnail)
					{
						echo $item->image;
					}
					?>
				</td>
				<td class="bsm_teachername">
					<table class="table table-striped">
						<tr>
							<td>
								<a href="<?php echo $item->teacherlink; ?>"><?php echo $teacherdisplay; ?></a>
							</td>
						</tr>
						<tr>
							<td style="text-align: left" class="bsm_short">
								<?php echo $item->short; ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		<?php
		} // End of foreach
		?>
		</tbody>
	</table>
	<div class="listingfooter">
		<?php
		echo $this->page->pagelinks;
		echo $this->page->counter;
		?>
	</div>
	<!--end of bsfooter div-->
</div>
