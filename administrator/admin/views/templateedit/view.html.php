<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');
class biblestudyViewtemplateedit extends JView {
	function display() {
		//Get template if editing
		$template = $this->get('template');
		if(empty($template->id)) {
			JToolbarHelper::title(JText::_('Create Template'), 'templates.png');
		}else{
			JToolbarHelper::title(JText::_('Edit Template'), 'templates.png');
		}
		JToolbarHelper::save();
		JToolbarHelper::apply();
		JToolbarHelper::cancel();
		JToolBarHelper::help('biblestudy', true );
		$lists = array();
		//Load the template params from its row and assign to $this
		$paramsdata = $template->params;
		$paramsdefs = JPATH_COMPONENT.DS.'models'.DS.'templateedit.xml';
		$params = new JParameter($paramsdata, $paramsdefs);
		$this->assignRef('params', $params);
		$data['published'] = JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $template->published);

		$this->assignRef('template', $template);
		$this->assignRef('data', $data);

		//create the list for the text image
		$javascript			= 'onchange="changeDisplayImage();"';
		$directory			= 'components/com_biblestudy/images';
		$lists['text']	= JHTML::_('list.images',  'text', $template->text, $javascript, $directory, "bmp|gif|jpg|png|swf"  );
		
		
		$lists['pdf']	= JHTML::_('list.images',  'pdf', $template->pdf, $javascript, $directory, "bmp|gif|jpg|png|swf"  );
		
		//Include the Jquery Library and Plugins
		$document =& JFactory::getDocument();
        $document->addStyleSheet(JURI::base().'components/com_biblestudy/css/icons.css');
		$this->assignRef('lists', $lists);
		parent::display();
	}
}
?>