<?php
defined('_JEXEC') or die('Restricted Access');
?>
<div id="templateTagsContainer">
	<div id="tabs">
	<ul>
		<li><a href="#tabs-1">BSM Tags</a></li>
		<li><a href="#tabs-2">Properties</a></li>
	</ul>
	<div id="tabs-1">
		<div>
			<h3>Study Tags</h3>
			<div>
				<div id="studyDate" class="tmplTag">[studyDate]</div>
				<div id="studyNumber" class="tmplTag">[studyNumber]</div>
				<div id="studyScriptures" class="tmplTag">[studyScriptures]</div>
				<div id="studyDVD" class="tmplTag">[studyDVD]</div>
				<div id="secondaryReference" class="tmplTag">[secondaryReference]</div>
				<div id="studyCD" class="tmplTag">[studyCD]</div>
				<div id="hits" class="tmplTag">[hits]</div>
				<div id="userName" class="tmplTag">[userName]</div>
				<div id="studyLocation" class="tmplTag">[studyLocation]</div>
				<div id="studyIntro" class="tmplTag">[studyIntro]</div>
				<div id="studyDuration" class="tmplTag">[studyDuration]</div>
				<div id="studySeries" class="tmplTag">[studySeries]</div>
				<div id="studyTitle" class="tmplTag">[studyTitle]</div>
				<div id="studyType" class="tmplTag">[studyType]</div>
				<div id="studyTopic" class="tmplTag">[studyTopic]</div>
				<div id="studyText" class="tmplTag">[studyText]</div>
				<div id="studyMedia" class="tmplTag">[studyMedia]</div>		
			</div>
		</div>
		<div>
			<h3>Teacher Tags</h3>
			<div>
				<div id="teacherName" class="tmplTag">[teacherName]</div>
				<div id="teacherTitle" class="tmplTag">[teacherTitle]</div>
				<div id="teacherPhone" class="tmplTag">[teacherPhone]</div>
				<div id="teacherEmail" class="tmplTag">[teacherEmail]</div>
				<div id="teacherWebsite" class="tmplTag">[teacherWebsite]</div>
				<div id="teacherInformation" class="tmplTag">[teacherInformation]</div>
				<div id="teacherImage" class="tmplTag">[teacherImage]</div>
				<div id="teacherShortBio" class="tmplTag">[teacherShortBio]</div>	
			</div>
		</div>
		<div>
			<h3>Media Tags</h3>
			<div>
				<div id="mediaImage" class="tmplTag">[mediaImage]</div>
				<div id="mediaServer" class="tmplTag">[mediaServer]</div>
				<div id="mediaPath" class="tmplTag">[mediaPath]</div>
				<div id="mediaFilename" class="tmplTag">[mediaFilename]</div>
				<div id="mediaSize" class="tmplTag">[mediaSize]</div>
				<div id="mediaMimeType" class="tmplTag">[mediaMimeType]</div>
				<div id="mediaPodcast" class="tmplTag">[mediaPodcast]</div>
				<div id="mediaCreatedDate" class="tmplTag">[mediaCreatedDate]</div>
				<div id="mediaHits" class="tmplTag">[mediaHits]</div>	
			</div>
		</div>	
	</div>
	<div id="tabs-2">
	Tab 2 content
	</div>
	</div>
</div>
<div id="tmplDesignSide">
	<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="admintable" width="100%">
		<tr>
			<td width="100" class="key"><?php echo JText::_('Published'); ?> </td>
			<td><?php echo $this->data['published']; ?></td>
		</tr>
		<tr>
			<td width="100" class="key"><?php echo JText::_('Type'); ?> </td>
			<td><?php echo $this->data['tmplTypes']; ?></td>
		</tr>
		<tr>
			<td width="100" class="key"><?php echo JText::_('Template Designer'); ?> </td>
			<td colspan="2">
				<div id="tmplCanvas">
					<ul id="tmplTagCanvas">
						<li class="canvasRow"></li>
					</ul>
				</div>
			</td>
		</tr>
	</table>
	<input type="hidden" name="option" value="com_biblestudy" />
	<input type="hidden" name="id" value="<?php echo $this->template->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="templateedit" />
	</form>
</div>