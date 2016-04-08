<?php
/**
 * View html
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View class for Archive
 *
 * @package  BibleStudy.Admin
 * @since    9.0.1
 */
class BiblestudyViewArchive extends JViewLegacy
{

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Can Do
	 *
	 * @var string
	 */
	public $canDo;

	/**
	 * Change Set
	 *
	 * @var string
	 */
	public $changeSet;

	/**
	 * Errors
	 *
	 * @var string
	 */
	public $errors;

	/**
	 * Results
	 *
	 * @var string
	 */
	public $results;

	/**
	 * Schema Version
	 *
	 * @var string
	 */
	public $schemaVersion;

	/**
	 * Update Version
	 *
	 * @var string
	 */
	public $updateVersion;

	/**
	 * Filter Params
	 *
	 * @var Registry
	 */
	public $filterParams;

	/**
	 * Pagination
	 *
	 * @var string
	 */
	public $pagination;

	/**
	 * Error Count
	 *
	 * @var string
	 */
	public $errorCount;

	/**
	 * Joomla BibleStudy Version
	 *
	 * @var string
	 */
	public $jversion;

	/**
	 * Temp Destination
	 *
	 * @var string
	 */
	public $tmp_dest;

	/**
	 * Player Stats
	 *
	 * @var string
	 */
	public $playerstats;

	/**
	 * Assets
	 *
	 * @var string
	 */
	public $assets;

	/**
	 * Popups
	 *
	 * @var string
	 */
	public $popups;

	/**
	 * SS
	 *
	 * @var string
	 */
	public $ss;

	/**
	 * Lists
	 *
	 * @var string
	 */
	public $lists;

	/**
	 * PI
	 *
	 * @var string
	 */
	public $pi;

	/**
	 * Form
	 *
	 * @var array
	 */
	protected $form;

	/**
	 * Item
	 *
	 * @var array
	 */
	protected $item;

	/**
	 * State
	 *
	 * @var array
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$this->setLayout('edit');

		// Set the toolbar
		$this->addToolbar();

		// Set the document
		$this->setDocument();

		// Display the template
		return parent::display($tpl);
	}

	/**
	 * Add Toolbar
	 *
	 * @return null
	 *
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('JBS_CMN_ARCHIVE'), 'archive');
		JToolbarHelper::preferences('com_biblestudy', '600', '800', 'JBS_ADM_PERMISSIONS');
		JToolbarHelper::divider();
		JToolbarHelper::help('biblestudy', true);
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return null
	 *
	 * @since    9.0.1
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('JBS_TITLE_ARCHIVE'));
	}

}
