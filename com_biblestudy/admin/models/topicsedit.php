<?php
/**
 * @version     $Id: topicsedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

	jimport('joomla.application.component.modeladmin');
	abstract class modelClass extends JModelAdmin{}

class biblestudyModeltopicsedit extends modelClass
{
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
		
	}


	
	function &getData()
	{
		// Load the data
		if (empty( $this->_data )) {
			$query = ' SELECT * FROM #__bsms_topics '.
					'  WHERE id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_data->id = 0;
			//TF added these
			$this->_data->published = 0;
			$this->_data->topic_text = null;
			$this->_data->params = null;
		}
		return $this->_data;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */

	public function getTable($type = 'topicsedit', $prefix = 'Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

    /**
     * Get the form data
     *
     * @param <Array> $data
     * @param <Boolean> $loadData
     * @return <type>
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        // build a fieldset of forms of all installed laguages
        // as is is very tricky to use both static and dynamic form file, build the complete form file dynamically
        // first get the installed languages
        $knownLanguages = JLanguage::getKnownLanguages();
        $topicseditXMLForm = '<?xml version="1.0" encoding="utf-8"?> <form>';
        $topicseditXMLForm .= '<field name="published" type="radio" label="JBS_CMN_PUBLISHED" description="JBS_CMN_PUBLISHED_DESC" class="inputbox" default="0" required="true"> <option value="0">JBS_CMN_NO</option> <option value="1">JBS_CMN_YES</option> </field> ';
        $topicseditXMLForm .= '<field name="topic_text" type="text" label="JBS_TPC_TOPIC_ALIAS" description="JBS_TPC_TOPIC_ALIAS_DESC" size="75" /> ';
        $topicseditXMLForm .= '<fields name="params"> <fieldset name="params"> ';
        foreach ($knownLanguages as $knownLanguage) {
            $topicseditXMLForm .= '<field name="' . $knownLanguage['tag'] . '" type="text" default="" label="' . JText::sprintf('JBS_TPC_LANGUAGE', $knownLanguage['name']) . '" description="JBS_TPC_LANGUAGE_DESC" size="75" required="false" translate_label= "false" /> ';
        }
        $topicseditXMLForm .= '</fieldset> </fields> ';
        $topicseditXMLForm .= '<field name="asset_id" type="hidden" filter="unset" /> ';
        $topicseditXMLForm .= '<field name="rules" type="rules" label="JFIELD_RULES_LABEL" translate_label="false" class="inputbox" filter="rules" component="com_biblestudy" section="topicsedit" validate="rules" /> ';
        $topicseditXMLForm .= '</form>';
        // build forms
        $form = $this->loadForm('com_biblestudy.topicsedit', $topicseditXMLForm, array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     *
     * @return <type>
     * @since   7.0
     */
    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.topicsedit.data', array());
        if (empty($data)) 
            $data = $this->getItem();

        return $data;
    }

}
?>
