<?php
/**
 * @version     $Id: view.html.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * @since		7.1.0
 * */

// no direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * Database Manager Manage View
 *
 * @package		BibleStudy.Administrator
 * @since		7.1.0
 */

class BiblestudyViewDatabase extends JView
{
	/**
	 * @since	7.1.0
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
                $this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	7.1.0
	 */
	protected function addToolbar()
	{

		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::custom('database.fix', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_DATABASE_FIX', false, false);
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_DATABASE');
	}
}
