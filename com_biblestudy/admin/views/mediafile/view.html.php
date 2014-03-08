<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for MediaFile
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewMediafile extends JViewLegacy
{

	/**
	 * Form
	 *
	 * @var object
	 */
	protected $form;

    /**
     * Media form
     *
     * @var
     */
    protected $media_form;


	/**
	 * Item
	 *
	 * @var object
	 */
	protected $item;

	/**
	 * State
	 *
	 * @var object
	 */
	protected $state;

	/**
	 * Admin
	 *
	 * @var object
	 */
	protected $admin;

	/**
	 * Can Do
	 *
	 * @var object
	 */
	public $canDo;

	/**
	 * Admin Params
	 *
	 * @var JRegistry
	 */
	public $admin_params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get("Form");
        $this->media_form = $this->get("MediaForm");
		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = JBSMBibleStudyHelper::getActions($this->item->id, 'mediafile');

		// Load the Admin settings
		$this->admin = JBSMParams::getAdmin();
		$registry    = new JRegistry;
		$registry->loadString($this->admin->params);
		$this->admin_params = $registry;

		// Needed to load the article field type for the article selector
		JFormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_content/models/fields/modal');

		$db = JFactory::getDBO();

		// Get server for upload dropdown
		$query = $db->getQuery(true);
		$query->select('id as value, server_name as text')->from('#__bsms_servers')->where('published = ' . 1)->order('server_name asc');
		$db->setQuery($query);
		$server     = array(
			array('value' => '', 'text' => JText::_('JBS_MED_SELECT_SERVER')),
		);
		$results    = $db->loadObjectList();
		$serverlist = array_merge($server, $results);
		$idsel      = "'SWFUpload_0'";

		// @todo need to fix this not sure what to do to fix it now error, TOM (not seeing the error)
        //@todo So what do we need to fix here? I can't see where this is even referenced? BCC
		$ref1           = JHTML::_('select.genericList', $serverlist, 'upload_server',
			'class="inputbox" onchange="showupload(' . $idsel . ')"' . '', 'value', 'text', '');
		$ref1com        = 'upload_server';
		$this->$ref1com = $ref1;

		// Get folders for upload dropdown
		$query = $db->getQuery(true);
		$query->select('id as value, foldername as text')->from('#__bsms_folders')->where('published = ' . 1)->order('foldername asc');
		$db->setQuery($query);
		$folder     = array(
			array('value' => '', 'text' => JText::_('JBS_MED_SELECT_FOLDER')),
		);
		$folderlist = array_merge($folder, $db->loadObjectList());

		$ref2           = JHTML::_('select.genericList', $folderlist, 'upload_folder',
			'class="inputbox" onchange="showupload(' . $idsel . ')"' . '', 'value', 'text', '');
		$ref2com        = 'upload_folder';
		$this->$ref2com = $ref2;
		$this->setLayout('edit');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JFactory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');

			return false;
		}

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
	 * @return void
	 *
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		$input = new JInput;
		$input->set('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_MEDIA_FILES') . ': <small><small>[' . $title . ']</small></small>', 'mp3.png');

		if ($isNew && $this->canDo->get('core.create', 'com_biblestudy'))
		{
			JToolBarHelper::apply('mediafile.apply');
			JToolBarHelper::save('mediafile.save');
			JToolBarHelper::save2new('mediafile.save2new');
			JToolBarHelper::cancel('mediafile.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_biblestudy'))
			{
				JToolBarHelper::apply('mediafile.apply');
				JToolBarHelper::save('mediafile.save');

				if ($this->canDo->get('core.create', 'com_biblestudy'))
				{
					JToolBarHelper::save2new('mediafile.save2new');
				}
			}
			// If checked out, we can still save
			if ($this->canDo->get('core.create', 'com_biblestudy'))
			{
				JToolBarHelper::save2copy('mediafile.save2copy');
			}
			JToolBarHelper::cancel('mediafile.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('biblestudy', true);
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 *
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$isNew    = ($this->item->id < 1);
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('JBS_TITLE_MEDIA_FILES_CREATING') : JText::sprintf('JBS_TITLE_MEDIA_FILES_EDITING', $this->item->filename));
	
		// Add PLUpload Resources
		$document->addScript(JURI::root().'media/com_biblestudy/jui/js/plupload.full.js');
		$document->addScript(JURI::root().'media/com_biblestudy/jui/js/plugins/jquery.plupload.queue.min.js');
		$document->addStylesheet(JURI::root().'media/com_biblestudy/jui/css/jquery.plupload.queue.css');
	}

}
