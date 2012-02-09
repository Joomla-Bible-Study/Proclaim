<?php

/**
 * @author Tom Fuller
 * @copyright 2010
 * Displays a docman list for the mediafiles menu item
 */

//No Direct Access
defined('_JEXEC') or die;

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Virtuemart Category List Form Field class for the Joomla Bible Study component
 */
class JFormFieldDocman extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 */
	protected $type = 'Docman';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return      array           An array of JHtml options.
	 */
	protected function getOptions()
	{
		
        //Check to see if Docman is installed
		jimport('joomla.filesystem.folder');
		if(!JFolder::exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_docman')){
			return JText::_('JBS_CMN_DOCMAN_NOT_INSTALLED');
		}
        
        $db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('dm.id, dm.dmname');  	
		$query->from('#__docman AS dm');
        $query->order('dm.id DESC');
		$db->setQuery((string)$query);
		$docs = $db->loadObjectList();
		$options = array();
		if ($docs)
		{
			$options[] = JHtml::_('select.option', '-1', JTEXT::_('JBS_MED_DOCMAN_SELECT'));
            foreach($docs as $doc)
			{
				$options[] = JHtml::_('select.option', $doc->id, $doc->dmname);
			}
		}
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}