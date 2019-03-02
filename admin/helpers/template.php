<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JFactory::getApplication()->enqueueMessage('Use of old function replace ASAP', 'error');
/**
 * Template helper class
 *
 * @package  Proclaim.Admin
 * @since    7.0.1
 *
 * @deprecate 8.0.1
 */
class JBSMTemplate
{
	/**
	 * Extension Deceleration
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Template types
	 *
	 * @var array
	 *
	 * @since 1.5
	 */
	protected $tmplTypes = array(
		'tmplList'       => 'List', 'tmplListItem' => 'List Item', 'tmplSingleItem' => 'Single Item',
		'tmplModuleList' => 'Module List', 'tmplModuleItem' => 'Module List Item', 'tmplPopup' => 'Popup Media Player'
	);

	/**
	 * Tags
	 *
	 * @var array
	 *
	 * @since 1.5
	 */
	private $tags;

	/**
	 *  DBO
	 *
	 * @var JDatabaseDriver
	 *
	 * @since 1.5
	 */
	private $DBO;

	/**
	 * Builds arrays of all the possible tags.
	 *
	 * @since 1.5
	 */
	public function __construct()
	{
		include_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/tagDefinitions.helper.php';

		// Creates array of all the tags and their associated field names
		$tagsStudy       = array(
			'[studyDate]'             => array('fieldName' => 'studydate'), '[studyTeacher]' => array('fieldName' => 'teacher_id'),
			'[studyNumber]'           => array('fieldName' => 'studynumber'), '[studyScripture1]' => array(
				'fieldName' => array(
					'booknumber', 'chapter_begin', 'verse_begin', 'chapter_end'
				)
			), '[studyScripture2]'    => array(
				'fieldName' => array(
					'booknumber2', 'chapter_begin2', 'verse_begin2', 'chapter_end2'
				)
			), '[secondaryReference]' => array('fieldName' => 'secondary_reference'),
			'[studyDVD]'              => array('fieldName' => 'prod_dvd'), '[studyCD]' => array('fieldName' => 'prod_cd'),
			'[studyTitle]'            => array('fieldName' => 'studytitle'), '[studyIntro]' => array('fieldName' => 'studyintro'),
			'[studyComments]'         => array('fieldName' => 'comments'), '[studyHits]' => array('fieldName' => 'hits'),
			'[studyUserAdded]'        => array('fieldName' => 'user_id'),
			'[studyLocation]'         => array('fieldName' => 'location_id'),
			'[studyMediaDuration]'    => array('fieldName' => array('media_hours', 'media_minutes', 'media_seconds')),
			'[studyMessageType]'      => array('fieldName' => 'messagetype'),
			'[studySeries]'           => array('fieldName' => 'series_id'), '[studyTopic]' => array('fieldName' => 'topic_id'),
			'[studyText]'             => array('fieldName' => 'studytext'), '[studyMedia]'
		);
		$tagsStudyList   = array(
			'[filterLocation]', '[filterBook]', '[filterTeacher]', '[filterSeries]', '[filterType]', '[filterYear]',
			'[filterTopic]', '[filterOrder]', '[studiesList]', '[pagination]'
		);
		$tagsTeacher     = array(
			'
			[teacherName]', '[teacherTitle]', '[teacherPhone]', '[teacherEmail]', '[teacherWebsite]',
			'[teacherInformation]', '[teacherImage]', '[teacherShortDescription]'
		);
		$tagsTeacherList = array(
			'[teachersList]'
		);

		// Creates an associative array of all the category tags and makes it available to the class
		$this->tags = array(
			'tagsStudy'       => $tagsStudy, 'tagsStudyList' => $tagsStudyList, 'tagsTeacher' => $tagsTeacher,
			'tagsTeacherList' => $tagsTeacherList
		);
		$this->DBO  = JFactory::getDbo();
	}

	/**
	 * Get Instance
	 *
	 * @staticvar bibleStudyTemplate $instance
	 * @return JBSMTemplate
	 *
	 * @since 1.5
	 */
	public function &getInstance()
	{
		static $instance;

		if (!$instance)
		{
			$instance = new JBSMTemplate;
		}

		return $instance;
	}

	/**
	 * Generates a list of tags that are being used in the input template.
	 *
	 * @param   string   $itemTmpl    String    Raw Html template
	 * @param   int      $id          Int  An Id of a template to load. This replaces the contents of the $itemTmpl
	 * @param   boolean  $fieldNames  Boolean  Default False. Set to True of you want to load the db fieldnames that correspond to the tags
	 *
	 * @return array
	 *
	 * @since 1.5
	 */
	public function loadTagList($itemTmpl = null, $id = null, $fieldNames = false)
	{
		$tagArray = null;

		if (isset($id))
		{
			$itemTmpl = $this->queryTemplate($id);
			$itemTmpl = $itemTmpl->tmpl;
		}

		foreach ($this->tags as $tagCategory)
		{
			foreach ($tagCategory as $tag)
			{
				if (!is_array($tag))
				{
					$tagSearch = $tag;
				}
				else
				{
					$tagSearch = key($tagCategory);
				}

				if (stristr($itemTmpl, $tagSearch))
				{
					if ($fieldNames)
					{
						$tagArray[] = $tag['fieldName'];
					}
					else
					{
						$tagArray[] = $tagSearch;
					}
				}

				next($tagCategory);
			}
		}

		return $tagArray;
	}

	/**
	 * Returns the template object from the database
	 *
	 * @param   int  $id  The id of the template to query
	 *
	 * @return Object  Row Object list
	 *
	 * @since 1.5
	 */
	public function queryTemplate($id)
	{
		$query = $this->DBO->getQuery(true);
		$query->select('*')
			->from('#__bsms_templates')
			->from('id = ' . (int) $id);
		$this->DBO->setQuery($query);

		return $this->DBO->loadObject();
	}

	/**
	 * Generates a drop down list of all the template types. Used in TemplateEdit View to
	 * generate the dropdown box of template types.
	 *
	 * @param   string  $DefaultSelected  Defines the default item
	 *
	 * @return  string  HTML Dropdown box
	 *
	 * @since 1.5
	 */
	public function loadTmplTypesOption($DefaultSelected)
	{
		$i = null;

		foreach ($this->tmplTypes as $type)
		{
			$i[] = JHtml::_('select.option', key($this->tmplTypes), $type);
			next($this->tmplTypes);
		}

		return JHtml::_('select.genericlist', $i, 'type', null, 'value', 'text', $DefaultSelected);
	}

	/**
	 * Builds list of fields to be used in the SELECT statement, so only the fields required
	 * by the template are selected
	 *
	 * @param   array  $fields  The fields to include in the SELECT
	 *
	 * @return String
	 *
	 * @since 1.5
	 */
	public function buildSqlSELECT($fields)
	{
		$SELECT = null;

		foreach ($fields as $field)
		{
			if (is_array($field))
			{
				$SELECT[] = implode(', ', $field);
			}
			else
			{
				$SELECT[] = $field;
			}
		}

		return implode(', ', $SELECT);
	}

	/**
	 * Study Date string.
	 *
	 * @return string
	 *
	 * @since 1.5
	 */
	public function studyDate()
	{
		return 'Some date';
	}
}
