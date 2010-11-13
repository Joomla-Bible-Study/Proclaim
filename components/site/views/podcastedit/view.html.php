<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.podcast.class.php');
jimport( 'joomla.application.component.view' );


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
		/*
		$text = $isNew ? JText::_( 'JBS_CMN_NEW' ) : JText::_( 'JBS_CMN_NEW' );
		JToolBarHelper::title(   JText::_( 'Podcast Edit' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		//JToolBarHelper::custom('writeXML','save.png','writeXML','Write XML', false, false);
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		jimport( 'joomla.i18n.help' );
		JToolBarHelper::help( 'biblestudy.podcasts', true );
		*/
		$template = $this->get('Template');
		$tem[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select a Template' ) .' -' );
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