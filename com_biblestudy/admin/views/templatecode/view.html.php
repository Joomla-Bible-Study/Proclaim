<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for TemplateCode
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewTemplatecode extends JViewLegacy
{

	/**
	 * Default Code for the Edit if content is null
	 *
	 * @var string
	 */
	public $defaultcode;

	/**
	 * Type
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Can Do
	 *
	 * @var object
	 */
	public $canDo;

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
		$this->form = $this->get("Form");
		$item       = $this->get("Item");

		if ($item->id == 0)
		{
			jimport('joomla.client.helper');
			jimport('joomla.filesystem.file');
			JClientHelper::setCredentialsFromRequest('ftp');
			$ftp               = JClientHelper::getCredentials('ftp');
			$file              = JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/defaulttemplatecode.php';
			$this->defaultcode = file_get_contents($file);
		}
		$this->type = null;

		if ($item->id !== 0)
		{
			$this->type = $this->get('Type');
		}
		$this->item  = $item;
		$this->state = $this->get("State");
		$this->canDo = JBSMBibleStudyHelper::getActions($this->item->id, 'templatecode');

		$this->setLayout("edit");
		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add Toolbar
	 *
	 * @return object
	 *
	 * @since 7.0.0
	 */
	protected function addToolbar()
	{
		$input = new JInput;
		$input->set('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_TEMPLATECODE') . ': <small><small>[' . $title . ']</small></small>', 'file file');

		if ($isNew && $this->canDo->get('core.create', 'com_biblestudy'))
		{
			JToolBarHelper::apply('templatecode.apply');
			JToolBarHelper::save('templatecode.save');
			JToolbarHelper::save2new('templatecode.save2new');
			JToolBarHelper::cancel('templatecode.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_biblestudy'))
			{
				JToolBarHelper::apply('templatecode.apply');
				JToolBarHelper::save('templatecode.save');
				JToolBarHelper::save2copy('templatecode.save2copy');
			}
			JToolBarHelper::cancel('templatecode.cancel', 'JTOOLBAR_CLOSE');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('templatecodehelp', true);
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
		$document->setTitle(
			$isNew ? JText::_('JBS_TITLE_TEMPLATECODES_CREATING')
				: JText::sprintf('JBS_TITLE_TEMPLATECODES_EDITING', $this->item->topic_text)
		);
	}

}
