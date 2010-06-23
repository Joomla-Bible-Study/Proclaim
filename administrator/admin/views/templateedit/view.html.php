<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');
class biblestudyViewtemplateedit extends JView {
	function display() {
		//Get template if editing
		$template = $this->get('template');
//dump ($template, 'template: ');
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		if(empty($template->id)) {
			JToolbarHelper::title(JText::_('Create Template'), 'templates.png');
		}else{
			JToolbarHelper::title(JText::_('Edit Template'), 'templates.png');
		}
		//JToolbarHelper::preview();
		JToolbarHelper::save();
		JToolbarHelper::apply();
		JToolbarHelper::cancel();
		JToolBarHelper::help('biblestudy', true );
		//Initialize templating class
		$tmplEngine = $this->loadHelper('templates.helper');
		$tmplEngine =& bibleStudyTemplate::getInstance();
		$lists = array();
		//Load the template params from its row and assign to $this
		$paramsdata = $template->params;
		$paramsdefs = JPATH_COMPONENT.DS.'models'.DS.'templateedit.xml';
		$params = new JParameter($paramsdata, $paramsdefs);
		$this->assignRef('params', $params);
		//dump ($template, 'template: ');
		$data['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $template->published);
		$data['tmplTypes'] = $tmplEngine->loadTmplTypesOption($template->type); 

		$this->assignRef('template', $template);
		$this->assignRef('data', $data);

		//create the list for the text image
		$javascript			= 'onchange="changeDisplayImage();"';
		$directory			= 'components/com_biblestudy/images';
		$lists['text']	= JHTML::_('list.images',  'text', $template->text, $javascript, $directory, "bmp|gif|jpg|png|swf"  );
		
		
		$lists['pdf']	= JHTML::_('list.images',  'pdf', $template->pdf, $javascript, $directory, "bmp|gif|jpg|png|swf"  );
		
		//Include the Jquery Library and Plugins
		$document =& JFactory::getDocument();
		$document->addScript(JURI::base().'components/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/noconflict.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/plugins/jquery.contextmenu.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/plugins/jquery.selectboxes.js');
			
		//BibleStudy Core
		$document->addScript(JURI::base().'components/com_biblestudy/js/biblestudy.js');
		
		//Biblestudy User Interface
		$document->addScript(JURI::base().'components/com_biblestudy/js/ui/jquery-ui.js');
		$document->addScript(JURI::base().'components/com_biblestudy/js/biblestudy-ui.js');
		$document->addStyleSheet(JURI::base().'components/com_biblestudy/js/ui/theme/ui.all.css');
		$document->addStyleSheet(JURI::base().'components/com_biblestudy/js/ui/theme/biblestudy.generic.css');
		
/*		$document->addScript(JURI::base().'components/com_biblestudy/js/edit_area/edit_area_full.js');
		$document->addScriptDeclaration('
		editAreaLoader.init(
			{	
				id: "tmplEdit",
				syntax: "html",
				start_highlight: true,
				min_width: 900,
				min_height: 500,
				allow_toggle: false,
				toolbar: \'fullscreen, |, undo, redo, |\',
				font_size: 12
			}
		);
		');*/
		$this->assignRef('lists', $lists);
		parent::display();
	}
}
?>