<?php
defined('_JEXEC') or die('Restricted Access');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="admintable">
	<tr>
		<td width="100" class="key"><?php echo JText::_('Published'); ?> </td>
		<td><?php echo $this->data['published']; ?></td>
	</tr>
	<tr>
		<td width="100" class="key"><?php echo JText::_('Type'); ?> </td>
		<td><?php echo $this->data['tmplTypes']; ?></td>
	</tr>
	<tr>
		<td width="100" class="key"><?php echo JText::_('Available Variables'); ?> </td>
		<td>
			<table class="admintable" width="100%">
				<tr>
					<td class="key"><?php echo JText::_('Studies List'); ?></td>
					<td class="key"><?php echo JText::_('Study'); ?></td>
					<td class="key"><?php echo JText::_('Teacher List')?></td>
					<td class="key"><?php echo JText::_('Teacher')?></td>
				</tr>
				<tr>
					<td valign="top">
						<ul>
							<li>[filterLocation]</li>
							<li>[filterBook]</li>
							<li>[filterTeacher]</li>
							<li>[filterSeries]</li>
							<li>[fillterType]</li>
							<li>[filterYear]</li>
							<li>[filterTopic]</li>
							<li>[filterOrder]</li>
							<li>[studiesList]</li>
						</ul>
					</td>
					<td valign="top">
						<ul>
							<li>[studyDate]</li>
							<li>[teacherName]</li>
							<li>[studyNumber]</li>
							<li>[reference1]</li>
							<li>[reference2]</li>
							<li>[studyDVD]</li>
							<li>[studyCD]</li>
							<li>[studyText]</li>
							<li>[studyHits]</li>
							<li>[location]</li>
							<li>[studyTitle]</li>
							<li>[studyIntro]</li>
							<li>[studyDuration]</li>
							<li>[studyType]</li>
							<li>[series]</li>
							<li>[topic]</li>
							<li>[studyText]</li>
							<li>[mediaFiles]</li>
							<li>[studyComments]</li>
						</ul>
					</td>
					<td valign="top">
						<ul>
							<li>[teacherList]</li>
						</ul>
					</td>
					<td valign="top">
						<ul>
							<li>[teacherName]</li>
							<li>[teacherTitle]</li>
							<li>[teacherPhone]</li>
							<li>[teacherEmail]</li>
							<li>[teacherWebsite]</li>
							<li>[teacherInformation]</li>
							<li>[teacherImage]</li>
						</ul>
					</td>
					
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td width="100" class="key"><?php echo JText::_('Template'); ?> </td>
		<td><textarea id="template" name="tmpl" cols="100" rows="10" class="text_area" id="studyintro"><?php echo $this->template->tmpl;?></textarea> </td>
	</tr>
</table>
<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="<?php echo $this->template->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="templateedit" />
</form>