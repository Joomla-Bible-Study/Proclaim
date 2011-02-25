<?php defined('_JEXEC') or die('Restricted access'); ?>

<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm">
<table>

    <tr>
    <td><img src="<?php echo JURI::base().'/components/com_jbsmigration/images/export.jpg';?>" alt="Export" height="48" width="48" /></td>
        <td>
           <a href="index.php?option=com_jbsmigration&task=export&run=1"> <?php echo JText::_('JBS_EI_EXPORT'); ?></a>
        </td><td></td><td></td>
    </tr>
    
    <tr>
        <td><img src="<?php echo JURI::base().'/components/com_jbsmigration/images/import.jpg';?>" alt="Import" height="48" width="48" /></td>
        <td>
            <?php echo JText::_('JBS_EI_IMPORT'); ?>
        </td>
        <td>
            <input class="input_box" id="importdb" name="importdb" type="file" size="57" />
        </td>
    </tr>
    <tr><td></td><td>
    <?php echo JText::_('JBS_EI_IMPORT_FROM_FOLDER');?>
    </td><td>
    <input type="text" id="install_directory" name="install_directory" class="input_box" size="70" value="<?php echo $this->tmp_dest .DS; ?>" />
			<input type="submit" value="<?php echo JText::_( 'JBS_EI_SUBMIT' ); ?>" name="submit"  />
    </td></tr>
    <tr><td><td><td><?php echo JText::_('JBS_EI_UPLOAD_MAX').': '.ini_get('upload_max_filesize');?><br />
    <?php echo JText::_('JBS_EI_MAX_EXECUTION_TIME').': '.ini_get('max_execution_time');?>
    </td></td><td></tr>
    <tr><td><img src="<?php echo JURI::base().'/components/com_jbsmigration/images/migrate.jpg';?>" alt="Import" height="48" width="48" /></td><td><a href="index.php?option=com_jbsmigration&task=migrate&run=1"><?php echo JText::_('JBS_EI_MIGRATE_ONLY');?></a></td><td></td></tr>
</table>

<input type="hidden" name="option" value="com_jbsmigration" />

<input type="hidden" name="task" value="doimport" />


</form>
</td></tr>
</table>