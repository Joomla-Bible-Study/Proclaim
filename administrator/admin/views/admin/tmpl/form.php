<?php defined('_JEXEC') or die('Restricted access');


//dump ($this->admin, 'admin: ');?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Administration' ); //dump ($this->admin, 'admin: ');?></legend>

		
    <table class="admintable">
    <tr><td>
    <?php
	$pane =& JPane::getInstance( 'sliders' );
 
echo $pane->startPane( 'content-pane' );
 
// First slider panel
// Create a slider panel with a title of SLIDER_PANEL_1_TITLE and a title id attribute of SLIDER_PANEL_1_NAME
echo $pane->startPanel( JText::_( 'General' ), 'GENERAL' );
// Display the parameters defined in the <params> group with no 'group' attribute
echo $this->params->render( 'params' );
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'Form Fillin' ), 'FILLIN-SITE' );
// Display the parameters defined in the <params> group with no 'group' attribute
echo $this->params->render( 'params' , 'FILLIN-SITE');
echo $pane->endPanel();

echo $pane->endPane();

?>
</td></tr>
   
     
      <tr> <td class="key"><?php echo JText::_('Version');?></td>
      	<td><?php echo JText::_('Your Version: ').'6.1.0 - ';?>
        <strong>
		<?php echo JText::_('Current Version:').' '; $bsmsversion = include("http://www.joomlaoregon.org/bsmsversion.php"); ?></strong><br />
        <a href="http://joomlacode.org/gf/project/biblestudy/" target="_blank"><?php echo JText::_('Get Latest Version');?></a></td>
      </tr>
    </table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_biblestudy" />
<input type="hidden" name="id" value="1" />
<input type="hidden" name="controller" value="admin" />
<input type="hidden" name="task" value="save" />
</form>
