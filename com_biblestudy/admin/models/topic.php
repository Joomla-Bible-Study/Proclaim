<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 */
// No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Topic Model class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyModelTopic extends JModelAdmin
{

	/**
	 * Get Table
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 */
	public function getTable($name = 'topic', $prefix = 'Table', $options = array())
	{
		return JTable::getInstance($name, $prefix, $options);
	}

	/**
	 * Get the form data
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since  7.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		/* Get the form.
		* build a fieldset of forms of all installed languages
		* as is is very tricky to use both static and dynamic form file, build the complete form file dynamically
		* first get the installed languages */
		$knownLanguages    = JLanguage::getKnownLanguages();
		$topicseditXMLForm = '<?xml version="1.0" encoding="utf-8"?> <form>';
		$topicseditXMLForm .= '<field name="published" type="list" class="span12 small" id="published" label="JSTATUS" description="JFIELD_PUBLISHED_DESC" size="1" default="1" filter="integer">	<option value="1"> JPUBLISHED</option> <option value="0"> JUNPUBLISHED</option> <option value="-2"> JTRASHED</option> </field>';
		$topicseditXMLForm .= '<field name="topic_text" type="text" label="JBS_TPC_TOPIC_ALIAS" description="JBS_TPC_TOPIC_ALIAS_DESC" size="75" /> ';
		$topicseditXMLForm .= '<field name="id" type="text" label="JGLOBAL_FIELD_ID_LABEL" description="JGLOBAL_FIELD_ID_DESC" size="10" default="0" readonly="true" class="readonly" />';
		$topicseditXMLForm .= '<fields name="params"> <fieldset name="params"> ';

		foreach ($knownLanguages as $knownLanguage)
		{
			$topicseditXMLForm .= '<field name="' . $knownLanguage['tag'] . '" type="text" default="" label="' . JText::sprintf('JBS_TPC_LANGUAGE', $knownLanguage['name']) . '" description="JBS_TPC_LANGUAGE_DESC" size="75" required="false" translate_label= "false" /> ';
		}
		$topicseditXMLForm .= '</fieldset> </fields> ';
		$topicseditXMLForm .= '<field name="asset_id" type="hidden" filter="unset" /> ';
		$topicseditXMLForm .= '<field name="rules" type="rules" label="JFIELD_RULES_LABEL" translate_label="false" class="inputbox" filter="rules" component="com_biblestudy" section="topicsedit" validate="rules" /> ';
		$topicseditXMLForm .= '</form>';

		// Build forms
		$form = $this->loadForm('com_biblestudy.topic', $topicseditXMLForm, array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Load Form Data
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   7.0
	 */
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_biblestudy.edit.topic.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer  $pk  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   11.1
	 */
	public function checkout($pk = null)
	{
		return $pk;
	}

	/**
	 * Custom clean the cache of com_biblestudy and biblestudy modules
	 *
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_biblestudy');
		parent::cleanCache('mod_biblestudy');
	}

}
