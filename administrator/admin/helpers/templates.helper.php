<?php
defined('_JEXEC') or die('Restricted Access');

class bibleStudyTemplate extends JObject {

	var $_tags;

	//Template types
	var $tmplTypes = array(
		'tmplSingleStudy' => 'Single Study',
		'tmplStudiesList' => 'Studies List',
		'tmplSingleStudy[List]' => 'Single Study [List]',
		'tmplSingleTeacher' => 'Single Teacher',
		'tmplTeacherList' => 'Teacher List',
		'tmplSingleTeacher[List]' => 'Single Teacher [List]',
		'tmplModule' => 'Module');

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
	 * @desc Generates a list of tags that are being used in the input template
	 * @param $itemTmpl
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
}
?>