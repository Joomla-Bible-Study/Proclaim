<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');
class biblestudyViewtemplateedit extends JView {
	function display() {
		//Get template if editing
		$template = $this->get('template');

		if(empty($template->id)) {
			JToolbarHelper::title(JText::_('Create Template'), 'generic.png');
		}else{
			JToolbarHelper::title(JText::_('Edit Template'), 'generic.png');
		}
		JToolbarHelper::preview();
		JToolbarHelper::save();
		JToolbarHelper::cancel();

		//Initialize templating class
		$tmplEngine = $this->loadHelper('templates.helper');
		$tmplEngine =& bibleStudyTemplate::getInstance();

		$data['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $template->published);
		$data['tmplTypes'] = $tmplEngine->loadTmplTypesOption($template->type); 

		$this->assignRef('template', $template);
		$this->assignRef('data', $data);

		//Include Edit Area Libraries
		$document = JFactory::getDocument();
		$document->addScript(JURI::base().'components/com_biblestudy/js/edit_area/edit_area_full.js');
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
		');
		parent::display();
	}
}
?>