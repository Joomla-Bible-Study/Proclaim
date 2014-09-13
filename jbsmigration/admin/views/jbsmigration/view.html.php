<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2014 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
jimport('joomla.application.component.helper');
jimport('joomla.i18n.help');

/**
 * View class for JBSMigration
 *
 * @package     BibleStudy
 * @subpackage  JBSMigration
 * @since       7.0.2
 */
class jbsmigrationViewjbsmigration extends JView
{

	/**
	 * Set Display for the view
	 *
	 * @param   string $tpl  ?
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{

		$config   = & JFactory::getConfig();
		$tmp_dest = $config->getValue('config.tmp_path');
		$this->assignRef('tmp_dest', $tmp_dest);
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the Toolbar to the view
	 *
	 * @return void
	 */
	public function addToolbar()
	{
		JToolBarHelper::title(JText::_('JBS_EI_TITLE'), 'folder');
		JToolBarHelper::help('jbsexportimport', true);
	}

}
