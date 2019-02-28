<?php
/**
 * JView html
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Podcast
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyViewPodcast extends JViewLegacy
{
	/**
	 * Form
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $form;

	/**
	 * Item
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $item;

	/**
	 * State
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $state;

	/**
	 * Defaults
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected $defaults;

	/**
	 * Can Do
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $canDo;

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
		$this->state = $this->get("State");
		$this->canDo = JBSMBibleStudyHelper::getActions($this->item->id, 'podcast');
		$this->setLayout("edit");
		$this->addToolbar();

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
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
		JToolbarHelper::title(JText::_('JBS_CMN_PODCASTS') . ': <small><small>[' . $title . ']</small></small>', 'feed feed');

		if ($isNew && $this->canDo->get('core.create', 'com_biblestudy'))
		{
			JToolbarHelper::apply('podcast.apply');
			JToolbarHelper::save('podcast.save');
			JToolbarHelper::save2new('podcast.save2new');
			JToolbarHelper::cancel('podcast.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_biblestudy'))
			{
				JToolbarHelper::apply('podcast.apply');
				JToolbarHelper::save('podcast.save');

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($this->canDo->get('core.create', 'com_biblestudy'))
				{
					JToolbarHelper::save2new('podcast.save2new');
				}
			}
			// If checked out, we can still save
			if ($this->canDo->get('core.create', 'com_biblestudy'))
			{
				JToolbarHelper::save2copy('podcast.save2copy');
			}

			JToolbarHelper::cancel('podcast.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('biblestudy', true);
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
		$document->setTitle($isNew ? JText::_('JBS_TITLE_PODCAST_CREATING') : JText::sprintf('JBS_TITLE_PODCAST_EDITING', $this->item->title));
	}
}
