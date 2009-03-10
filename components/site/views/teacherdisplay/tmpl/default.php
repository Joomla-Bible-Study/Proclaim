<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php global $mainframe;

$pathway =& $mainframe->getPathWay();
$uri 		=& JFactory::getURI();
$database	= & JFactory::getDBO();
$teacher = $this->teacher;
?>
<table width="100%" class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr><td align="center"><h1><?php echo $this->params->get('teacher_title');?></h1></td></tr></table>
<table width="100%" class="contentpaneopen<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
<td align="center" width="<?php echo $this->params->get('imagew');?>">
<?php if ($teacher->image) 
		{ ?>
        <img src="<?php echo $teacher->image?>" border="1" alt="<?php echo $teacher->teachername.' - '.$teacher->title;?>" />
        <?php 
		}?>
     
</td>
<td align="left">
<strong><?php echo $teacher->teachername;?></strong>
     <?php echo ' - '.$teacher->title;?><br />
<?php echo $teacher->phone;?><br /><br />
<?php if ($teacher->email) {
		if (!stristr($teacher->email,'@') ) { ?>
		<a href="<?php echo $teacher->email;?>"><?php echo JText::_('Contact');?></a>
    <?php }
    else { ?>
    	<a href=mailto:"<?php echo $teacher->email;?>"><?php echo JText::_('Contact ');?></a>
        <?php } 
	} //end if $teacher->email?>
    <br />
<?php if ($teacher->website) { ?>
	<a href="<?php echo $teacher->website;?>"><?php echo JText::_('Site');?></a>
    <?php } ?>
	</td>
</tr>
</table>
<table width="100%">
<?php if ($teacher->information) { ?>
<tr>
	<td>
		<img src="<?php echo JURI::base().'components/com_biblestudy/images/square.gif'?>" height="1" width="100%" />
	</td>
</tr>
<tr>
	<td>
		<?php echo $teacher->information;?>
	</td>
</tr>
<?php } // end of if for teacher->information?>
<tr>
	<td>
    	<?php if ($this->params->get('show_teacher_studies') > 0) { ?>
		<img src="<?php echo JURI::base().'components/com_biblestudy/images/square.gif'?>" height="3" width="100%" />
	</td>
</tr>
<?php $result = count($this->studies); 
if ($result < 1 ){ }
else { ?>
<tr><td><h2><?php echo $this->params->get('label_teacher');?></h2></td></tr>
</table>

<table cellpadding="1" width="100%">
<tr>
<td> <strong><?php echo JText::_('Title');?></strong></td>
<td> <strong><?php echo JText::_('Scripture');?></strong></td>
<td> <strong><?php echo JText::_('Date');?></strong></td>
</tr>
<?php foreach ($this->studies as $study) { ?>
 <tr>
  <td> <a href="index.php?option=com_biblestudy&view=studydetails&id=<?php echo $study->sid;?>"><?php echo $study->studytitle; ?></a></td>
  <td> <?php echo $study->bookname.' '.$study->chapter_begin;?></td>
  <td> <?php $date = JHTML::_('date', $study->studydate, JText::_('DATE_FORMAT_LC') , '$offset'); echo $date;?></td>
 </tr>
<?php } // end of foreach ?>
<?php	} // end of if show_teacher_studies ?>
<?php } // end of else testing if $result 
if ($this->menuid){$link = '&Itemid='.$this->menuid;}?>
<tr><td align="center" colspan="0"><br /><a href="index.php?option=com_biblestudy&view=teacherlist<?php echo $link;?>"><?php echo JText::_('<-Return to Teacher List');?></a>
</table>