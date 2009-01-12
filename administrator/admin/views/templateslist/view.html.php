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
		
		$templates = $this->get('templates');
		
		//Template types
		$tmplTypes = array(
			'tmplSingleStudy' => 'Single Study',
			'tmplStudiesList' => 'Studies List',
			'tmplSingleStudy[List]' => 'Single Study [List]',
			'tmplSingleTeacher' => 'Single Teacher',
			'tmplTeacherList' => 'Teacher List',
			'tmplSingleTeacher[List]' => 'Single Teacher [List]',
			'tmplModule' => 'Module'
		);
		
		$tagsStudy = array('[studyDate]', '[studyTeacher]', '[studyNumber]', '[studyScripture1]', '[studyScripture2]', '[studyDVD]', '[studyCD]', '[studyTitle]', '[studyIntro]', '[studyComments]', '[studyHits]', '[studyUserAdded]', '[studyLocation]', '[studyMediaDuration]', '[studyMessageType]', '[studySeries]', '[studyTopic]', '[studyText]', '[studyMedia]');
		$tagsStudyList = array('[filterLocation]', '[filterBook]', '[filterTeacher]', '[filterSeries]', '[filterType]', '[filterYear]', '[filterTopic]', '[filterOrder]', '[studiesList]', '[pagination]');
		$tagsTeacher = array('[teacherName]', '[teacherTitle]', '[teacherPhone]', '[teacherEmail]', '[teacherWebsite]', '[teacherInformation]', '[teacherImage]', '[teacherShortDescription]');
		$tagsTeacherList = array('[teachersList]');
		
		$this->assignRef('tagsStudy', $tagsStudy);
		$this->assignRef('tagsStudyList', $tagsStudyList);
		$this->assignRef('tagsTeacherer', $tagsTeacher);
		$this->assignRef('tagsTeacherList', $tagsTeacherList);
		
		$this->assignRef('tmplTypes', $tmplTypes);
		$this->assignRef('templates', $templates);
		parent::display();
	}
}
?>