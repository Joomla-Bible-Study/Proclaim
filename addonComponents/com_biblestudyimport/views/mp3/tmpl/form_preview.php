<?php defined('_JEXEC') or die('Restricted access'); ?>
	<div class="col100">
	<div style="width: 100%; text-align: center;">
		<label for="series" >
			<?php echo JText::_('Directory to Scan'); ?>:
		</label>
		<input type="text" name="directoryname" size="50" value="<?php echo JPATH_SITE.DS.'media'.DS; ?>" />
		<span id="dirStatus" class="st"></span>
		<input type="submit" name="preview" value="Preview"/>

	<div id="fileContainer">
	<fieldset>
		<legend>Configuration</legend>
		<table class="admintable">
			<tr>
				<td class="key">
				Title
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'fileTitle', 'class="availableTags"', 'value', 'text', 'id3v1.title', false)?>
				Custom: <input type="text" class="globalCustom" name="fileTitle" />
				</td>
			</tr>
			<tr>
				<td class="key">
				Study #
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'studyNumber', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <input type="text" class="globalCustom" name="studyNumber" />
				</td>
			</tr>
			<tr>
				<td class="key">
				Date
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'studyDate', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <?php echo JHTML::_('calendar', date('Y-m-d H:i:s'), 'studyDate', 'studyDate');?>
				</td>
			</tr>
			<tr>
				<td class="key">
				Description
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'studyDescription', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <input type="text" class="globalCustom" name="studyDescription" />
				</td>
			</tr>
			<tr>
				<td class="key">
				Scripture
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'scripture', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <input type="text" class="globalCustom" name="scripture" />
				</td>
			</tr>
			<tr>
				<td class="key">
				Secondary Reference
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'secondaryRef', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <input type="text" class="globalCustom" name="secondaryRef" />
				</td>
			</tr>
			<tr>
				<td class="key">
				Teacher
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'teacher', 'class="availableTags"', 'value', 'text', 'id3v1.artist', false)?>
				Custom: <input type="text" class="globalCustom" name="teacher" />
				<?php echo JHTML::_('select.genericlist', $this->availableTeachers, 'teacher', 'class="existing"', 'id', 'teachername', null, false)?>
				</td>
			</tr>
			<tr>
				<td class="key">
				Location
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'location', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <input type="text" class="globalCustom" name="location" />
		        <?php echo JHTML::_('select.genericlist', $this->availableLocations, 'location', 'class="existing"', 'id', 'location_text', null, false)?>
				</td>
			</tr>
			<tr>
				<td class="key">
				Series
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'series', 'class="availableTags"', 'value', 'text', 'id3v1.album', false)?>
				Custom: <input type="text" class="globalCustom" name="series" />
		        <?php echo JHTML::_('select.genericlist', $this->availableSeries, 'series', 'class="existing"', 'id', 'series_text', null, false)?>
				</td>
			</tr>
			<tr>
				<td class="key">
				Topic
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'topic', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <input type="text" class="globalCustom" name="topic" />
				<?php echo JHTML::_('select.genericlist', $this->availableTopics, 'topic', 'class="existing"', 'id', 'topic_text', null, false)?>
				</td>
			</tr>
			<tr>
				<td class="key">
				Type
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'type', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <input type="text" class="globalCustom" name="type" />
		        <?php echo JHTML::_('select.genericlist', $this->availableTypes, 'type', 'class="existing"', 'id', 'message_type', null, false)?>
				</td>
			</tr>
			<tr>
				<td class="key">
				Duration
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'duration', 'class="availableTags"', 'value', 'text', 'playtime_string', false)?>
				Custom: <input type="text" class="globalCustom" name="duration" />
				</td>
			</tr>
			<tr>
				<td class="key">
				Study Text
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'text', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <input type="text" class="globalCustom" name="text" />
				</td>
			</tr>
			<tr>
				<td class="key">
				AVR Code
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'avr', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <input type="text" class="globalCustom" name="avr" />
				</td>
			</tr>
			<tr>
				<td class="key">
				FileSize
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'fileSize', 'class="availableTags"', 'value', 'text', 'filesize', false)?>
				Custom: <input type="text" class="globalCustom" name="fileSize" />
				</td>
			</tr>
			<tr>
				<td class="key">
				Server
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'server', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <input type="text" class="globalCustom" name="server" />
		        <?php echo JHTML::_('select.genericlist', $this->availableServers, 'server', 'class="existing"', 'id', 'server_name', null, false)?>
				</td>
			</tr>
			<tr>
				<td class="key">
				Folder
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'folder', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <input type="text" class="globalCustom" name="folder" />
		        <?php echo JHTML::_('select.genericlist', $this->availableFolders, 'folder', 'class="existing"', 'id', 'foldername', null, false)?>
				</td>
			</tr>
			<tr>
				<td class="key">
				Podcast
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'podcast', 'class="availableTags"', 'value', 'text', null, false)?>
				Custom: <input type="text" class="globalCustom" name="podcast" />
		        <?php echo JHTML::_('select.genericlist', $this->availablePodcasts, 'podcast', 'class="existing"', 'id', 'title', null, false)?>
				</td>
			</tr>
			<tr>
				<td class="key">
				MimeType
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'mimeType', 'class="availableTags"', 'value', 'text', 'mime_type', false)?>
				Custom: <input type="text" class="globalCustom" name="mimeType" />
				<?php echo JHTML::_('select.genericlist', $this->availableMimeTypes, 'mimeType', 'class="existing"', 'id', 'mimetext', null, false)?>
				</td>
			</tr>
			<tr>
				<td class="key">
				Comment
				</td>
				<td>
				<?php echo JHTML::_('select.genericlist', $this->availableTags, 'comment', 'class="availableTags"', 'value', 'text', 'id3v1.comment', false)?>
				Custom: <input type="text" class="globalCustom" name="comment" />
				</td>
			</tr>
			<tr>
				<td class="key">
				Move Files to Server				
				</td>
				<td>
				<input type="checkbox" id="moveFiles"/> (Server field will be ignored, because its assumed that it will be the current server)
				<td>
			</tr>
		</table>
	</fieldset>
	<form action="index.php" method="post" name="adminForm" id="adminForm">
		<table class="adminlist" id="files">
			<thead>
		        <tr> 
		          <th>Filename</th>
		          <th width="5">Title</th>
		          <th width="5">Study #</th>
		          <th width="5">Date</th>
		          <th width="5">Description</th>
		          <th width="5">Scripture</th>
		          <th width="5">Sec Ref</th>
		          <th width="5">Teacher</th>          
		          <th width="5">Location</th>
		          <th width="5">Series</th>
		          <th width="5">Topic</th>
		          <th width="5">Type</th>
		          <th width="20">Duration</th>
		          <th width="5">Study Text</th>
				  <th width="5">AVR</th>
				  <th width="5">FileSize</th>
				  <th width="5">Server</th>
				  <th width="5">Folder</th>		  
				  <th width="5">Podcast</th>
				  <th width="5">MimeType</th>
				  <th width="5">Comment</th>
				  <th width="5">Move File</th>
		        </tr>
		      </thead>
			  <?php 
			  $id = 0;
			  foreach($this->id3Info as $id3) {
			  	echo('<tr><td><input type="hidden" name="file[]" alt="'.$id.'" value="'.$id3['filenamepath'].'"/><a href="#" class="viewFile" name="'.$id.'">'.$id3['filename'].'</a></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="fileTitle" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="studyNumber" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="studyDate" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="studyDescription" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="scripture" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="secondaryRef" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="teacher" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="location" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="series" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="topic" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="type" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="duration" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="text" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="avr" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="fileSize" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="server" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="folder" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="podcast" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="mimeType" value=""/></td>');
				echo('<td align="center"><input name="file[]" alt="'.$id.'" class="comment" value=""/></td>');
			  	echo('<td align="center"><input type="checkbox" name="file[]" alt="'.$id.'" class="moveFile"/></td>');				
				echo('</tr>');
			  	$id++;
			  }
			  ?>  
		</table>
		<hr/>
		<div style="text-align:center"><input type="button" id="import" name="import" value="import"/></div>
	</div>
	<div class="clr"></div>
	
	<input type="hidden" name="option" value="com_biblestudyimport" />
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="view" value="mp3"/>
	<input type="hidden" name="controller" value="mp3" />
</form>