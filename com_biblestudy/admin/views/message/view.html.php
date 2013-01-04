<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;


/**
 * View class for Message
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewMessage extends JViewLegacy
{

	/**
	 * Form
	 *
	 * @var array
	 */
	protected $form;

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
	 * @var array
	 */
	protected $admin;

	/**
	 * @var object
	 */
	public $canDo;

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
		$this->form  = $this->get("Form");
		$this->item  = $this->get("Item");
		$this->canDo = JBSMBibleStudyHelper::getActions($this->item->id, 'mediafile');
		$input       = new JInput;
		$option      = $input->get('option', '', 'cmd');
		$input->set('sid', $this->item->id);
		$input->set('sdate', $this->item->studydate);

		//JApplication::setUserState($option . 'sid', $this->item->id);
		//JApplication::setUserState($option . 'sdate', $this->item->studydate);
		$this->mediafiles = $this->get('MediaFiles');

		$this->loadHelper('params');
		$this->admin = JBSMParams::getAdmin();
		$registry    = new JRegistry();
		$registry->loadString($this->admin->params);
		$this->admin_params = $registry;
		$this->canDo        = JBSMBibleStudyHelper::getActions($type = 'message', $Itemid = $this->item->id);
		$host               = JURI::base();
		$document           = JFactory::getDocument();

		JHtml::stylesheet('media/com_biblestudy/css/token-input-jbs.css');
		//$document->addScript(JURI::base() . 'media/com_biblestudy/js/plugins/jquery.tokeninput.js');
		//$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/token-input-jbs.css');
		if (BIBLESTUDY_CHECKREL)
		{
			JHtml::_('jquery.framework');
		}

		//$document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
		JHtml::script('media/com_biblestudy/js/biblestudy.js');
		$script = "
            jQuery(document).ready(function() {
                jQuery('#topics').tokenInput(" . $this->get('alltopics') . ",
                {
                    theme: 'jbs',
                    hintText: '" . JText::_('JBS_CMN_TOPIC_TAG') . "',
                    noResultsText: '" . JText::_('JBS_CMN_NOT_FOUND') . "',
                    searchingText: '" . JText::_('JBS_CMN_SEARCHING') . "',
                    animateDropdown: false,
                    preventDuplicates: true,
                    prePopulate: " . $this->get('topics') . "
                });
            });
             ";
		//JHtml::script($script);
		$document->addScriptDeclaration($script);

		JHtml::script('media/com_biblestudy/js/plugins/jquery.tokeninput.js');
		JHtml::stylesheet('media/com_biblestudy/js/ui/theme/ui.all.css');
		//$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/js/ui/theme/ui.all.css');

		$this->setLayout("edit");

		// Set the toolbar
		$this->addToolbar();

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Add Toolbar
	 *
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		$input = new JInput;
		$input->set('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_STUDIES') . ': <small><small>[ ' . $title . ' ]</small></small>', 'studies.png');

		if ($isNew && $this->canDo->get('core.create', 'com_biblestudy'))
		{
			JToolBarHelper::apply('message.apply');
			JToolBarHelper::save('message.save');
			JToolBarHelper::save2new('message.save2new');
			JToolBarHelper::cancel('message.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_biblestudy'))
			{
				JToolBarHelper::apply('message.apply');
				JToolBarHelper::save('message.save');

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($this->canDo->get('core.create', 'com_biblestudy'))
				{
					JToolBarHelper::save2new('message.save2new');
				}
			}
			// If checked out, we can still save
			if ($this->canDo->get('core.create', 'com_biblestudy'))
			{
				JToolBarHelper::save2copy('message.save2copy');
			}
			JToolBarHelper::cancel('message.cancel', 'JTOOLBAR_CLOSE');

			if ($this->canDo->get('core.edit', 'com_biblestudy'))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('resetHits', 'reset.png', 'Reset Hits', 'JBS_STY_RESET_HITS', false, false);
			}
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
		$document->setTitle($isNew ? JText::_('JBS_TITLE_STUDIES_CREATING') : JText::sprintf('JBS_TITLE_STUDIES_EDITING', $this->item->studytitle));
	}

}
