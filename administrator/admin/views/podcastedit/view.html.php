<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

require_once (JPATH_ADMINISTRATOR  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.defines.php');
require_once (BIBLESTUDY_PATH_ADMIN_LIB .DS. 'biblestudy.podcast.class.php');


class biblestudyViewpodcastedit extends JView
{
	
	function display($tpl = null)
	{
		
		$podcastedit		=& $this->get('Data');
		$isNew		= ($podcastedit->id < 1);
	
		$limit = $podcastedit->podcastlimit;
			if ($limit > 0) {
				$limit = 'LIMIT '.$limit;
			}
			else {
				$limit = '';
			}
        $podcasts = new JBSPodcast();
        $episodes = $podcasts->getEpisodes($podcastedit->id, $limit);
        $model =& $this->getModel();
		$admin=& $this->get('Admin');
		$admin_params = new JParameter($admin[0]->params);
		$this->assignRef('admin_params', $admin_params);
		$this->assignRef('admin', $admin);	
		$text = $isNew ? JText::_( 'JBS_CMN_NEW' ) : JText::_( 'JBS_CMN_EDIT' );
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		JToolBarHelper::title(   JText::_( 'JBS_PDC_PODCAST_EDIT' ).': <small><small>[ ' . $text.' ]</small></small>', 'podcast.png' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			JToolBarHelper::apply();
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy', true );
		
		
		$template = $this->get('Template');
		$tem[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'JBS_CMN_SELECT_TEMPLATE' ) .' -' );
		$tem 			= array_merge( $tem, $template );
		$lists['templates']	= JHTML::_('select.genericlist',   $tem, 'detailstemplateid', 'class="inputbox" size="1" ', 'value', 'text', $podcastedit->detailstemplateid );
		
		
		$this->assignRef('podcastedit',		$podcastedit);
		$lists['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $podcastedit->published);
		$this->assignRef('lists',		$lists);
		$this->assignRef('episodes', $episodes);
		parent::display($tpl);
	}
}
?>