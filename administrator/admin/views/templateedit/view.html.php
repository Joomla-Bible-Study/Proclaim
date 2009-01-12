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
		
		$tmplTypes = array();
		$tmplTypes[] = JHTML::_('select.option', 'tmplSingleStudy', 'Single Study');
		$tmplTypes[] = JHTML::_('select.option', 'tmplStudiesList', 'Studies List');
		$tmplTypes[] = JHTML::_('select.option', 'tmplSingleStudy[List]', 'Single Study [List]');
		$tmplTypes[] = JHTML::_('select.option', 'tmplSingleTeacher', 'Single Teacher');
		$tmplTypes[] = JHTML::_('select.option', 'tmplTeacherList', 'Teacher List');
		$tmplTypes[] = JHTML::_('select.option', 'tmplSingleTeacher[List]', 'Single Teacher [List]');
		$tmplTypes[] = JHTML::_('select.option', 'tmplModule', 'Module');
		
		
		$data['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $template->published);
		$data['tmplTypes'] = JHTML::_('select.genericlist', $tmplTypes, 'type', null, 'value', 'text', $template->type);
	
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