<?php
defined('_JEXEC') or die('Restricted Access');

class bibleStudyTemplate extends JObject {

	var $_tags;
	var $_DBO;

	//Template types
	var $tmplTypes = array(
		'tmplList' => 'List',
		'tmplListItem' => 'List Item',
		'tmplSingleItem' => 'Single Item',
		'tmplModuleList' => 'Module List',
		'tmplModuleItem' => 'Module List Item',
		'tmplPopup' => 'Popup Media Player'
	);
	/**
	 * @desc Builds arrays of all the possible tags.
	 * @return null
	 */
	function __construct() {
		include(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'tagDefinitions.helper.php');
		//Creates array of all the tags and their associated field names
		$tagsStudy = array(
			'[studyDate]' => array('fieldName' => 'studydate'), 
			'[studyTeacher]' => array('fieldName' => 'teacher_id'), 
			'[studyNumber]' => array('fieldName' => 'studynumber'), 
			'[studyScripture1]' => array('fieldName' => array('booknumber', 'chapter_begin', 'verse_begin', 'chapter_end')),
			'[studyScripture2]' => array('fieldName' => array('booknumber2', 'chapter_begin2', 'verse_begin2', 'chapter_end2')),
			'[secondaryReference]' => array('fieldName' => 'secondary_reference'),
			'[studyDVD]' => array('fieldName' => 'prod_dvd'),
			'[studyCD]' => array('fieldName' => 'prod_cd'),
			'[studyTitle]' => array('fieldName' => 'studytitle'),
			'[studyIntro]' => array('fieldName' => 'studyintro'),
			 '[studyComments]'=> array('fieldName' => 'comments'), 
			 '[studyHits]' => array('fieldName' => 'hits'),
			 '[studyUserAdded]' => array('fieldName' => 'user_id'),
			 '[studyLocation]' => array('fieldName' => 'location_id'), 
			 '[studyMediaDuration]' => array('fieldName' => array('media_hours', 'media_minutes', 'media_seconds')),
			 '[studyMessageType]' => array('fieldName' =>'messagetype'), 
			 '[studySeries]' => array('fieldName' => 'series_id'),
			 '[studyTopic]' => array('fieldName' => 'topic_id'),
			 '[studyText]' => array('fieldName' => 'studytext'),
			 '[studyMedia]');
		$tagsStudyList = array(
			'[filterLocation]', 
			'[filterBook]', 
			'[filterTeacher]', 
			'[filterSeries]', 
			'[filterType]', 
			'[filterYear]', 
			'[filterTopic]', 
			'[filterOrder]', 
			'[studiesList]', 
			'[pagination]');
		$tagsTeacher = array('
			[teacherName]', 
			'[teacherTitle]', 
			'[teacherPhone]', 
			'[teacherEmail]', 
			'[teacherWebsite]', 
			'[teacherInformation]', 
			'[teacherImage]', 
			'[teacherShortDescription]');
		$tagsTeacherList = array(
			'[teachersList]');

		//Creates an associative array of all the category tags and makes it available to the class
		$this->_tags = array('tagsStudy' => $tagsStudy, 'tagsStudyList' => $tagsStudyList, 'tagsTeacher' => $tagsTeacher, 'tagsTeacherList' => $tagsTeacherList);
		$this->_DBO = JFactory::getDBO();
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
	 * @param $itemTmpl	String	Raw Html template
	 * @param $category String  The category of possible tags: "tagsStudy", "tagsStudyList", "tagsTeacher", or "tagsTeacherList"
	 * @param $id Int  An Id of a template to load. This replaces the contents of the $itemTmpl
	 * @param $fieldNames Boolean  Default False. Set to True of you want to load the db fieldnames that correspond to the tags
	 * @return Array
	 */
	function loadTagList($itemTmpl = null, $id = null, $fieldNames = false) {
		if(isset($id)) {
			$itemTmpl = $this->queryTemplate($id);
			$itemTmpl = $itemTmpl->tmpl;
		}
		foreach($this->_tags as $tagCategory) {
			foreach($tagCategory as $tag) {
				if(!is_array($tag)) {
					$tagSearch = $tag;
				}else{
					$tagSearch = key($tagCategory);
				}
				if(stristr($itemTmpl, $tagSearch)) {
					if($fieldNames) {
						$tagArray[] = $tag['fieldName'];
					}else{
						$tagArray[] = $tagSearch;
					}
				}
				next($tagCategory);
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
	function loadTmplTypesOption($DefaultSelected) {
		foreach($this->tmplTypes as $type) {
			$i[] = JHTML::_('select.option', key($this->tmplTypes), $type);
			next($this->tmplTypes);
		}
		return JHTML::_('select.genericlist', $i, 'type', null, 'value', 'text', $DefaultSelected);
	}

	/**
	 * @desc Returns the template object from the database
	 * @param $id  Int  The id of the template to query
	 * @return Row Object
	 */
	function queryTemplate($id) {
		$query = 'SELECT * FROM #__bsms_templates WHERE `id`='.$id;
		$this->_DBO->setQuery($query);
		return $this->_DBO->loadObject();
	}

	/**
	 * @desc Builds list of fields to be used in the SELECT statement, so only the fields required
	 * by the template are selected
	 * @param $fields  Array  The fields to include in the SELECT
	 * @return String
	 */
	function buildSqlSELECT($fields) {
		foreach($fields as $field) {
			if(is_array($field)){
				$SELECT[] = implode(', ', $field);
			}else{
				$SELECT[] = $field;
			}
		}
		return implode(', ', $SELECT);
	}
	
	function studyDate() {
		return 'Some date';
	}
}
?>