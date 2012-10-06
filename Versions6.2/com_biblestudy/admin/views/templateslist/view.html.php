<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');
class biblestudyViewtemplateslist extends JView {

	function display() {
		JHTML::_('stylesheet', 'icons.css', JURI::base().'components/com_biblestudy/css/');
		JToolBarHelper::title(JText::_('Templates'), 'templates.png');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList();
		JToolBarHelper::custom('copy', 'copy.png', 'copy_f2.png', 'Copy', true);
		JToolBarHelper::addNew();
        JToolBarHelper::help('biblestudy', true );
		//Initialize templating class
		$tmplEngine = $this->loadHelper('templates.helper');
		$tmplEngine =& bibleStudyTemplate::getInstance();

		$templates = $this->get('templates');

		$this->assignRef('tmplEngine', $tmplEngine);
		$this->assignRef('tmplTypes', $tmplTypes);
		$this->assignRef('templates', $templates);
		parent::display();
	}
}
?>