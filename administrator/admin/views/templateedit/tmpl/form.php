<?php
defined('_JEXEC') or die('Restricted Access');
JHTML::_('behavior.tooltip');

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">  
  <div id="tmplDesignSide">
	<table class="admintable" width="100%">
		<tr>
			<td width="100" class="key"><?php echo JText::_('Published'); ?> </td>
			<td><?php echo $this->data['published']; ?></td>
		</tr>
		<!--<tr>
			<td width="100" class="key"><?php //echo JText::_('Type'); ?> </td>
			<td><?php //echo $this->data['tmplTypes']; ?></td>
		</tr>-->
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
jimport('joomla.html.pane');
$pane =& JPane::getInstance( 'sliders');
 
echo $pane->startPane( 'content-pane' );
 
// First slider panel
// Create a slider panel with a title of SLIDER_PANEL_1_TITLE and a title id attribute of SLIDER_PANEL_1_NAME
echo $pane->startPanel( JText::_( 'JBS_CMN_GENERAL' ), 'GENERAL' );
// Display the parameters defined in the <params> group with no 'group' attribute
echo $this->params->render( 'params');
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

echo $pane->startPanel( JText::_('ToolTip Items'), 'TOOLTIP');
echo $this->params->render( 'params', 'TOOLTIP');
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

echo $pane->startPanel( JText::_('Studies List Custom View'), 'STUDIESVIEW');
echo $this->params->render( 'params', 'STUDIESVIEW');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Details View'), 'DETAILS');
echo $this->params->render( 'params', 'DETAILS');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Details List Row 1'), 'DETAILSROW1');
echo $this->params->render('params', 'DETAILSROW1');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Details List Row 2'), 'DETAILSROW2');
echo $this->params->render('params', 'DETAILSROW2');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Details List Row 3'), 'DETAILSROW3');
echo $this->params->render('params', 'DETAILSROW3');
echo $pane->endPanel();

echo $pane->startPanel( JText::_('Details List Row 4'), 'DETAILSROW4');
echo $this->params->render('params', 'DETAILSROW4');
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

echo $pane->startPanel( JText::_('Landing Page'), 'LANDINGPAGE');
echo $this->params->render( 'params', 'LANDINGPAGE');
echo $pane->endPanel();

//This ends the parameter panes
echo $pane->endPane();

?>
</td></tr>

</table>

  
	<input type="hidden" name="option" value="com_biblestudy" />
	<input type="hidden" name="id" value="<?php echo $this->template->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="templateedit" />
	</form>
</div>