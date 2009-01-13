<?php
defined('_JEXEC') or die('Restricted Access');

class bibleStudyTemplate extends JObject {

	var $_tags;

	//Template types
	var $tmplTypes = array(
		'tmplStudiesList' => 'Studies List',
		'tmplSingleStudyList' => 'Single Study List',
		'tmplSingleStudy' => 'Single Study',
		'tmplTeachersList' => 'Teacher List',
		'tmplSingleTeacherList' => 'Single Teacher List',
		'tmplSingleTeacher' => 'Single Teacher',
		'tmplModule' => 'Module');
	
	/**
	 * @desc Builds arrays of all the possible tags.
	 * @return null
	 */
	function __construct() {
		//Creates array of all the tags for the 4 main tag categories
		$tagsStudy = array('[studyDate]', '[studyTeacher]', '[studyNumber]', '[studyScripture1]', '[studyScripture2]', '[studyDVD]', '[studyCD]', '[studyTitle]', '[studyIntro]', '[studyComments]', '[studyHits]', '[studyUserAdded]', '[studyLocation]', '[studyMediaDuration]', '[studyMessageType]', '[studySeries]', '[studyTopic]', '[studyText]', '[studyMedia]');
		$tagsStudyList = array('[filterLocation]', '[filterBook]', '[filterTeacher]', '[filterSeries]', '[filterType]', '[filterYear]', '[filterTopic]', '[filterOrder]', '[studiesList]', '[pagination]');
		$tagsTeacher = array('[teacherName]', '[teacherTitle]', '[teacherPhone]', '[teacherEmail]', '[teacherWebsite]', '[teacherInformation]', '[teacherImage]', '[teacherShortDescription]');
		$tagsTeacherList = array('[teachersList]');

		//Creates an associative array of all the category tags and makes it available to the class
		$this->_tags = array('tagsStudy' => $tagsStudy, 'tagsStudyList' => $tagsStudyList, 'tagsTeacher' => $tagsTeacher, 'tagsTeacherList' => $tagsTeacherList);
	}

	function &getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new bibleStudyTemplate();
		}
		return $instance;
	}

	/**
	 * @desc Generates a list of tags that are being used in the input template.
	 * Used for the "Variable Summary" in the backend
	 * @param $itemTmpl	String	Raw Html template
	 * @return Array
	 */
	function loadTagList($itemTmpl) {
		foreach($this->_tags as $tagCategory) {
			foreach($tagCategory as $tag) {
				if(stristr($itemTmpl, $tag)) {
					$tagArray[] = $tag;
				}
			}
		}
		return $tagArray;
	}
	
	/**
	 * @desc Generates a drop down list of all the template types. Used in TemplateEdit View to
	 * generate the dropdown box of template types.
	 * @param $selected	 String	Defines the default item
	 * @return HTML Dropdown box
	 */
	function loadTmplTypesOption($selected) {
		foreach($this->tmplTypes as $type) {
			$i[] = JHTML::_('select.option', key($this->tmplTypes), $type);
			next($this->tmplTypes);
		}
		return JHTML::_('select.genericlist', $i, 'type', null, 'value', 'text', $selected);
	}
}
?>