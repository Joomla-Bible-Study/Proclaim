<?php

/**
 * @version $Id: default_main.php 1 $
 * @package COM_JBSMIGRATION
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die('Restricted access'); ?>

<form enctype="multipart/form-data" action="index.php" method="post"
	name="adminForm">
	<table>

		<tr>
			<td width="10%"><a
				href="index.php?option=com_jbsmigration&task=export&run=1"><img
					src="<?php echo JURI::base().'/components/com_jbsmigration/images/export.jpg';?>"
					alt="Export" height="48" width="48" /> </a></td>
			<td><a href="index.php?option=com_jbsmigration&task=export&run=1"> <?php echo JText::_('JBS_EI_EXPORT'); ?>
			</a>
			</td>
			<td></td>
			<td></td>
		</tr>
		
		
		
		
   <?php  
   if (substr_count(JVERSION,'1.5') == 0)
   { ?> 
    <tr>
        <td><img src="<?php echo JURI::base().'/components/com_jbsmigration/images/import.jpg';?>" alt="Import" height="48" width="48" /></td>
        <td>
            <?php echo JText::_('JBS_EI_IMPORT'); ?>
        </td>
        <td>
            <input class="input_box" id="importdb" name="importdb" type="file" size="57" />
        </td>
    </tr>
    <tr><td><img src="<?php echo JURI::base().'/components/com_jbsmigration/images/folder.jpg';?>" alt="Tmp Folder" height="48" width="48" /></td><td>
    <?php echo JText::_('JBS_EI_IMPORT_FROM_FOLDER');?>
    </td><td>
    <input type="text" id="install_directory" name="install_directory" class="input_box" size="70" value="<?php echo $this->tmp_dest .DS; ?>" />
			
    </td></tr>
    <tr><td><td><td>
    <input type="submit" value="<?php echo JText::_( 'JBS_EI_SUBMIT' ); ?>" name="submit"  />
    </td></td><td></tr>
    <tr><td><td><td><?php echo JText::_('JBS_EI_UPLOAD_MAX').': '.ini_get('upload_max_filesize');?><br />
    <?php echo JText::_('JBS_EI_MAX_EXECUTION_TIME').': '.ini_get('max_execution_time');?>
    </td></td><td></tr>
    <tr><td><img src="<?php echo JURI::base().'/components/com_jbsmigration/images/migrate.jpg';?>" alt="Import" height="48" width="48" /></td><td><a href="index.php?option=com_jbsmigration&task=migrate&run=1"><?php echo JText::_('JBS_EI_MIGRATE_ONLY');?></a></td><td></td></tr>
    <?php } ?>
    <?php $ver = JVERSION; echo '<tr><td colspan="2"><strong> Current Joomla Version: </strong>'.$ver.'</td></tr>'; ?>
</table>

	<input type="hidden" name="option" value="com_jbsmigration" /> <input
		type="hidden" name="task" value="doimport" />


</form>
</td>
</tr>
</table>
