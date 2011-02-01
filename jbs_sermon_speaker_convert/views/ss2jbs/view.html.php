<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.view' );


class ss2jbsViewss2jbs extends JView {
	
	function display($tpl = null) {
		

		
		JToolBarHelper::title(   JText::_( 'Convert from Sermon Speaker to Joomla Bible Study' ) );
		
        //Check for version of Sermon Speaker 3.4 or higher
       $ssversion = $this->versionXML($component='sermonspeaker');
       if (!$ssversion){$ssversion = JText::_('No Sermon Speaker version found');}
       $jbsversion = $this->versionXML($component = 'jbs');
       if (!$ssversion){$jbsversion = JText::_('No Joomla Bible Study version found');}
       $this->assignRef('jbsversion', $jbsversion);
       $this->assignRef('ssversion',$ssversion);
       
        	
        
		parent::display($tpl);
	}

function versionXML($component)
	{
	   switch ($component)
       {
        case 'sermonspeaker':
        if ($data = JApplicationHelper::parseXMLInstallFile(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_sermonspeaker'.DS.'sermonspeaker.xml'))
        {return $data['version'];}
        else {return FALSE;}
        break;
        
        case 'jbs':
        if ($data = JApplicationHelper::parseXMLInstallFile(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_biblestudy'.DS.'manifest.xml'))
        {return $data['version'];}
        else {return FALSE;}
        break;
       }
	}
}
?>