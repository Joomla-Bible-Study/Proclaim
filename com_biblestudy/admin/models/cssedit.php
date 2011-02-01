<?php
/**
 * @version     $Id: cssedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */

//No Direct Access
defined('_JEXEC') or die();

	jimport('joomla.application.component.modeladmin');
	abstract class modelClass extends JModelAdmin{}

class biblestudyModelcssedit extends modelClass
{
function __construct()
	{
		parent::__construct();

		//$array = JRequest::getVar('cid',  0, '', 'array');
		//$this->setId((int)$array[0]);
	}

function &getData()
	{
		$filename = JPATH_ROOT.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
		$csscontents=fopen($filename,"rb");
		$this->_data->filecontent = fread($csscontents,filesize($filename));
		fclose($csscontents);
		//$this->assignRef('lists',		$lists);
		
		
		return $this->_data;
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
        $form = $this->loadForm('com_biblestudy.cssedit', 'cssedit', array('control' => 'jform', 'load_data' => $loadData));

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
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.cssedit.data', array());
        if (empty($data)) {
            $data = $this->getItem();
            $data->podcast_id = explode(',', $data->podcast_id);
        }


        return $data;
    }
}