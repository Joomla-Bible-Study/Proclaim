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
 * View class for Topic
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewTest extends JViewLegacy
{

	/**
	 * Form
	 *
	 * @var object
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
	 * Defaults
	 *
	 * @var object
	 */
	protected $defaults;

	/**
	 * @var object
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
		$this->canDo = JBSMBibleStudyHelper::getActions(1, 'test');

		// Set the toolbar
		$this->addToolbar();

		// Set the document
		$this->setDocument();

		// Display the template
		return parent::display($tpl);
	}

	/**
	 * Adds ToolBar
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	protected function addToolbar()
	{
		$input = new JInput;
		$input->set('hidemainmenu', true);
		$isNew = 0;
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_TOPICS') . ': <small><small>[' . $title . ']</small></small>', 'topics.png');

		if ($isNew && $this->canDo->get('core.create', 'com_biblestudy'))
		{
			JToolBarHelper::apply('topic.apply');
			JToolBarHelper::save('topic.save');
			JToolBarHelper::cancel('topic.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_biblestudy'))
			{
				JToolBarHelper::apply('topic.apply');
				JToolBarHelper::save('topic.save');
			}
			JToolBarHelper::cancel('topic.cancel', 'JTOOLBAR_CLOSE');
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
		$isNew    = 0;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('JBS_TITLE_TOPICS_CREATING') : JText::sprintf('JBS_TITLE_TOPICS_EDITING', 'test'));
	}

}
