<?php
/**
 * @version $Id: biblestudy.install.php 1 $
 * Bible Study Component
 * @package Bible Study
 *
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 *
 * Install Based on Kunena Component
 * Reference http://svn.joomla.org/project/cms/development/trunk/tests/_data/installer_packages/
 **/
 //
 // Dont allow direct linking
 defined( '_JEXEC' ) or die('Restricted access');
 ?>
      <script type="text/javascript">
window.addEvent('domready', function(){ new Accordion($$('div#content-sliders-1.pane-sliders > .panel > h3.pane-toggler'), $$('div#content-sliders-1.pane-sliders > .panel > div.pane-slider'), {onActive: function(toggler, i) {toggler.addClass('pane-toggler-down');toggler.removeClass('pane-toggler');i.addClass('pane-down');i.removeClass('pane-hide');Cookie.write('jpanesliders_content-sliders-1',$$('div#content-sliders-1.pane-sliders > .panel > h3').indexOf(toggler));},onBackground: function(toggler, i) {toggler.addClass('pane-toggler');toggler.removeClass('pane-toggler-down');i.addClass('pane-hide');i.removeClass('pane-down');if($$('div#content-sliders-1.pane-sliders > .panel > h3').length==$$('div#content-sliders-1.pane-sliders > .panel > h3.pane-toggler').length) Cookie.write('jpanesliders_content-sliders-1',-1);},duration: 300,display: 1,show: 1,opacity: false,alwaysHide: true}); });
 </script>
 <?php
 
// Bible Study wide defines

class com_biblestudyInstallerScript {

	function install($parent) {
		echo '<p>'. JText::_('JBS_INS_16_CUSTOM_INSTALL_SCRIPT') . '</p>';
			$db =& JFactory::getDBO();
			$query = file_get_contents(JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'sql' .DS. 'jbs7.0.0.sql');
			$db->setQuery($query);
			$db->queryBatch();
			echo JHtml::_('sliders.panel', JText::_('JBS_INS_16_INSTALLING_VERSION_700') , 'publishing-details');
	}

	function uninstall($parent) {
		require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
		require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'helpers' .DS. 'params.php');

        
        
		$db =& JFactory::getDBO();
				$db->setQuery ("SELECT * FROM #__bsms_admin WHERE id = 1");
				$db->query();
				$admin = $db->loadObject();
       

				$drop_tables = $admin->drop_tables;

	if ($drop_tables > 0)
	{		
	   //We must remove the assets manually each time
        $db =& JFactory::getDBO();
        $query = "SELECT id FROM #__assets WHERE name = 'com_biblestudy'";
        $db->setQuery($query);
        $db->query();
        $parent_id = $db->loadResult();
        $query = "DELETE FROM #__assets WHERE parent_id = ".$parent_id;
        $db->setQuery($query);
        $db->query();
   		$query = file_get_contents(JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'sql' .DS. 'uninstall-dbtables.sql');
		$db->setQuery($query);
		$db->queryBatch();
		$drop_result .= '<p>db Error: '.$db->stderr().'</p>';
		$drop_result .= '<H3>'. JText::_('JBS_INS_CUSTOM_UNINSTALL_SCRIPT') .'</H3>';
	}
	else
	{
		$drop_result = '<H3>'.JText::_('JBS_INS_NO_DATABASE_REMOVED').'</H3>';
	}
  echo '<h2>'. JText::_('JBS_INS_UNINSTALLED').'</h2> <div>'.$drop_result.'</div>';
	
 
	} //end of function uninstall()

	function update($parent) {
	//	echo '<p>'. JText::_('JBS_INS_16_CUSTOM_UPDATE_SCRIPT') .'</p>';
 
	} // End Update

	function preflight($type, $parent) {
	//	echo '<p>'. JText::sprintf('JBS_INS_16_CUSTOM_PREFLIGHT', $type) .'</p>';
	}

	function postflight($type, $parent) {
        //We see if one of the study records matches the parent_id, if not, we need to reset them
    	
        $db = JFactory::getDBO();
        $query = "SELECT id FROM #__assets WHERE name = 'com_biblestudy'";
        $db->setQuery($query);
        $parent_id = $db->loadResult();
        
        // Check to see if assets have been fixed and if they match the parent_id
        $query = 'SELECT t.asset_id, a.parent_id FROM #__bsms_templates AS t LEFT JOIN #__assets AS a ON (t.asset_id = a.id) WHERE t.id = 1';
        $db->setQuery($query);
        $asset = $db->loadObject();
        
        if ($parent_id != $asset->parent_id)
        {
            $query = 'UPDATE #__assets SET parent_id = '.$parent_id.' WHERE parent_id = '.$asset->parent_id;
            $db->setQuery($query);
            if ($result = $db->query()){return true;}else{return false;}
        }
        elseif ( $asset_id == 0 || !$asset->asset_id)
        {
			require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_biblestudy' .DS. 'install' .DS. 'biblestudy.assets.php');
			$assetfix = new fixJBSAssets();
            $assetdofix = $assetfix->AssetEntry();
            if ($assetdofix){echo '<font color="green">'.JText::_('JBS_INS_16_ASSET_SUCCESS').'</font>';}else{echo '<font color="red">'.JText::_('JBS_INS_16_ASSET_FAILURE').'</font>';} 
        }
?>
		<fieldset class="panelform">
		<legend><?php echo JText::sprintf('JBS_INS_INSTALLATION_RESULTS', $type); ?></legend>  
    
		<?php echo JHtml::_('sliders.start','content-sliders-1',array('useCookie'=>1)); 

		echo JHtml::_('sliders.panel','CSS', 'publishing-details');
	
		//Check for presence of css or backup
		jimport('joomla.filesystem.file');
		$src = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css.dist';
		$dest = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
		$backup = JPATH_SITE.DS.'media'.DS.'com_biblestudy'.DS.'backup'.DS.'biblestudy.css';
		$cssexists = JFile::exists($dest);  
		$backupexists = JFile::exists($backup);
		if (!$cssexists)
		{
			echo '<p><font color="red"><strong>'.JText::_('JBS_INS_16_CSS_FILE_NOT_FOUND').'</strong> </font></p>';
			if ($backupexists)
			{
				echo '<p>' . JText::_('JBS_INS_16_BACKUPCSS') .' /images/biblestudy.css <a href="index.php?option=com_biblestudy&view=cssedit&controller=cssedit&task=copycss">'. JText::_('JBS_INS_16_CSS_BACKUP') . '</a></p>';
			}
			else
			{
				$copysuccess = JFile::copy($src, $dest);
				if ($copysuccess)
				{
					echo '<p>'. JText::_('JBS_INS_16_CSS_COPIED_SOURCE') . '</p>';
				}
				else
				{
					echo '<P>'. JText::_('JBS_INS_16_CSS_COPIED_DISCRIPTION1') . '&frasl;components&frasl;com_biblestudy&frasl;assets&frasl;css&frasl;biblestudy.css.dist' . JText::_('JBS_INS_16_CSS_COPIED_DISCRIPTION2') . '</p>';
				}
			}
		}    
		
		//Check for default details text link image and copy if not present
		$src = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'images'.DS.'textfile24.png';
		$dest = JPATH_SITE.DS.'images'.DS.'textfile24.png';
		$imageexists = JFile::exists($dest);
		if (!$imageexists)
		{
			echo '<br /><br />' . JText::_('JBS_INS_16_COPYING_IMAGE');
			if ($imagesuccess = JFile::copy($src, $dest))
			{
				echo '<br />' . JText::_('JBS_INS_16_COPYING_SUCCESS');
			}
			else
			{
				echo '<br />' . JText::_('JBS_INS_16_COPYING_PROBLEM_FOLDER1') . '/components/com_biblestudy/images/textfile24.png' . JText::_('JBS_INS_16_COPYING_PROBLEM_FOLDER2');
			}
		}
	?>
        
	
		<?php echo JHtml::_('sliders.end'); ?>
		</fieldset>
		</div> <!--end of div for panelform -->

			<!-- Rest of footer -->
		<div style="border: 1px solid #99CCFF; background: #D9D9FF; padding: 20px; margin: 20px; clear: both;">
		<img src="components/com_biblestudy/images/openbible.png" alt="Bible Study" border="0" class="flote: left" />
		<strong><?php echo JText::_('JBS_INS_16_THANK_YOU'); ?></strong>
		<br />
		<?php echo JText::_('JBS_INS_16_CONGRATULATIONS'); ?> 
		<p>
		<?php echo JText::_('JBS_INS_16_STATEMENT1'); ?> </p>
		<p>
		<?php echo JText::_('JBS_INS_16_STATEMENT2'); ?></p>
		<p>
		<?php echo JText::_('JBS_INS_16_STATEMENT3'); ?></p>
		<p>
		<?php echo JText::_('JBS_INS_16_STATEMENT4'); ?></p>
		<p>
		</p>
		<p><a href="http://www.joomlabiblestudy.org/forums.html" target="_blank"><?php echo JText::_('JBS_INS_16_VISIT_FORUM'); ?></a></p>
		<p><a href="http://www.joomlabiblestudy.org" target="_blank"><?php echo JText::_('JBS_INS_16_GET_MORE_HELP'); ?></a></p>
		<p><a href="http://www.JoomlaBibleStudy.org/jbsdocs" target="_blank"><?php echo JText::_('JBS_INS_16_VISIT_DOCUMENTATION'); ?></a></p>
		<p>Bible Study Component <em>for Joomla! </em> &copy; by <a
			href="http://www.JoomlaBibleStudy.org" target="_blank">www.JoomlaBibleStudy.org</a>.
		All rights reserved.</p>
		</div>
		<?php
		// An example of setting a redirect to a new location after the install is completed
		//$parent-&gt;getParent()-&gt;set('redirect_url', 'http://www.google.com');
	}
  
} // end of class