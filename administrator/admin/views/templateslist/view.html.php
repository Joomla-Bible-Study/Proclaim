<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');
class biblestudyViewtemplateslist extends JView {

	function display() {
		JToolBarHelper::title(JText::_('Templates'), 'generic.png');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList();
		JToolBarHelper::addNew();

		//Initialize templating class
		JView::loadHelper('templates.helper');
		$tmplEngine = bibleStudyTemplate::getInstance();

		$templates = $this->get('templates');

		$this->assignRef('tmplEngine', $tmplEngine);
		$this->assignRef('tmplTypes', $tmplTypes);
		$this->assignRef('templates', $templates);
		parent::display();
	}
}
?>