<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
//include_once dirname(__FILE__).'/../default/view.php';

/**
 * Extension Manager Manage View
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.6
 */

class BiblestudyViewDatabase extends JView
{
	/**
	 * @since	1.6
	 */
	function display($tpl=null)
	{
		$language = JFactory::getLanguage();
                $language->load('com_installer');
                // Get data from the model
		$this->state = $this->get('State');
		$this->changeSet = $this->get('Items');
		$this->errors = $this->changeSet->check();
		$this->results = $this->changeSet->getStatus();
		$this->schemaVersion = $this->get('SchemaVersion');
		$this->updateVersion = $this->get('UpdateVersion');
		$this->filterParams =$this->get('DefaultTextFilters');
		$this->schemaVersion = ($this->schemaVersion) ?  $this->schemaVersion : JText::_('JNONE');
		$this->updateVersion = ($this->updateVersion) ?  $this->updateVersion : JText::_('JNONE');
		$this->pagination = $this->get('Pagination');
		$this->errorCount = count($this->errors);
                
                $jbsversion = JApplicationHelper::parseXMLInstallFile(JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_biblestudy'.DIRECTORY_SEPARATOR.'biblestudy.xml');
                $this->version = $jbsversion['version'];
                
		$errors = count($this->errors);
		if (!(strncmp($this->schemaVersion, $this->version, 5) === 0))
		{
			$this->errorCount++;
		}
		if (!$this->filterParams)
		{
			$this->errorCount++;
		}
		if (($this->updateVersion != $this->version))
		{
			$this->errorCount++;
		}
               // $this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::custom('database.fix', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_DATABASE_FIX', false, false);
		JToolBarHelper::divider();
		parent::addToolbar();
		JToolBarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_DATABASE');
	}
}
