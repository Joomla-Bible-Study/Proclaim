<?php
defined('_JEXEC') or die('Restricted Access');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div id="tmplDesignSide">
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
			<td width="100" class="key"><?php echo JText::_('Template Name'); ?> </td>
			<td colspan="2"><input type="text" name="title" id="title" length="100" value="<?php echo $this->template->title; ?>" />
				<!--<div id="tmplCanvas">
					<ul id="tmplTagCanvas">
						<li class="canvasRow"></li>
					</ul>
				</div>-->
			</td>
		</tr>
        <tr><td class="key"><strong><?php echo JText::_('Details Link Image');?></strong></td>
<td><?php echo $this->lists['text']; echo '  '.JText::_('Uses Media folder'); ?><a href="index.php?option=com_biblestudy&amp;view=admin&amp;layout=form" target="_blank"><?php echo '  '.JText::_('Set Here');?></a></td></tr>
<tr><td class="key"><?php echo JText::_('PDF Image');?></td><td><?php echo $this->lists['pdf']; echo '  '.JText::_('Uses Media folder'); ?><a href="index.php?option=com_biblestudy&amp;view=admin&amp;layout=form" target="_blank"><?php echo '  '.JText::_('Set Here');?></a></td></tr>
	</table>

<table><tr><td>
<?php
$pane =& JPane::getInstance( 'sliders' );
 
echo $pane->startPane( 'content-pane' );
 
// First slider panel
// Create a slider panel with a title of SLIDER_PANEL_1_TITLE and a title id attribute of SLIDER_PANEL_1_NAME
echo $pane->startPanel( JText::_( 'General' ), 'GENERAL' );
// Display the parameters defined in the <params> group with no 'group' attribute
echo $this->params->render( 'params' );
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'Templates' ), 'TEMPLATES' );
// Display the parameters defined in the <params> group with the 'group' attribute of 'GROUP_NAME'
echo $this->params->render( 'params', 'TEMPLATES' );
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'Filters' ), 'FILTERS' );
// Display the parameters defined in the <params> group with the 'group' attribute of 'GROUP_NAME'
echo $this->params->render( 'params', 'FILTERS' );
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Front Page Images'), 'FP_IMAGES');
echo $this->params->render( 'params', 'FP_IMAGES');
echo $pane->endPanel();
// Repeat for each additional slider panel required

echo $pane->startPanel( JText::_('Verses, Dates, CSS'), 'VERSES');
echo $this->params->render( 'params', 'VERSES');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Media'), 'MEDIA');
echo $this->params->render( 'params', 'MEDIA');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('List Items'), 'LISTITEMS');
echo $this->params->render( 'params', 'LISTITEMS');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Front End Submission'), 'SUBMISSION');
echo $this->params->render( 'params', 'SUBMISSION');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Details View'), 'DETAILS');
echo $this->params->render( 'params', 'DETAILS');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Module'), 'MODULE');
echo $this->params->render( 'params', 'MODULE');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Teacher View'), 'TEACHER');
echo $this->params->render( 'params', 'TEACHER');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Series List'), 'SERIES');
echo $this->params->render( 'params', 'SERIES');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Series Detail'), 'SERIESDETAIL');
echo $this->params->render( 'params', 'SERIESDETAIL');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Study List Row 1'), 'ROW1');
echo $this->params->render( 'params', 'ROW1');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Study List Row 2'), 'ROW2');
echo $this->params->render( 'params', 'ROW2');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Study List Row 3'), 'ROW3');
echo $this->params->render( 'params', 'ROW3');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Study List Row 4'), 'ROW4');
echo $this->params->render( 'params', 'ROW4');
echo $pane->endPanel();


//This ends the parameter panes
echo $pane->endPane();

?>
</td></tr>

</table>
<!--<div id="templateTagsContainer">
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
</div>-->

	
	<input type="hidden" name="option" value="com_biblestudy" />
	<input type="hidden" name="id" value="<?php echo $this->template->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="templateedit" />
	</form>
</div>