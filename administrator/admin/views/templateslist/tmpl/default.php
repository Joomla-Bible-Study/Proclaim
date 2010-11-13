<form action="index.php" method="post" name="adminForm">
	<table class="adminlist">
	      <thead>
	        <tr> 
	          <th width="5"> <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->templates); ?>);" />
	          </th>
	          <th width="5">
	          	<?php echo JText::_('Template ID'); ?>
	          </th>
	          <!--<th width="10">
	          	<?php //echo JText::_('Template Type'); ?>
	          </th>-->
	          <th width="40">
	          	<?php //echo JText::_('Variable Summary'); ?>
	          </th>
	          <th width="5" align="center">
	          	<?php echo JText::_('JBS_CMN_PUBLISHED'); ?> 
	          </th>
	        </tr>
	      </thead>
<?php 
$k = 0;
$i = 0;
foreach($this->templates as $template) {
		$link 			= JRoute::_( 'index.php?option=com_biblestudy&controller=templateedit&task=edit&cid[]='. $template->id);
		$checked 		= JHTML::_('grid.id',   $i, $template->id );
		$published 		= JHTML::_('grid.published', $template, $i );
		//$tmplSnippet 	= implode(', ', $this->tmplEngine->loadTagList($template->tmpl));
	
?>
<tr class="row<?php echo $k; ?>">
  	<td align="center"><?php echo $checked; ?></td>
	<td align="center"><a href="<?php echo $link; ?>"><?php echo $template->id.'-'.$template->title ; ?></a></td>
	<!--<td align="center"><?php //echo $this->tmplEngine->tmplTypes[$template->type]; ?></td>-->
	<td align="center"><?php //echo $tmplSnippet; ?></td>
	<td align="center"><?php echo $published; ?></td>
</tr>
<?php
$i++;
$k = 1 - $k;
}
?> 
	</table>
<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="templateedit" />
</form>