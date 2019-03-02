<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for TemplateCode
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyViewTemplatecode extends JViewLegacy
{
	/**
	 * Default Code for the Edit if content is null
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $defaultcode;

	/**
	 * Type
	 *
	 * @var string
	 * @since    7.0.0
	 */
	public $type;

	/**
	 * Can Do
	 *
	 * @var object
	 * @since    7.0.0
	 */
	public $canDo;

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
	 * @var object
	 * @since    7.0.0
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
		JToolbarHelper::title(JText::_('JBS_CMN_TEMPLATECODE') . ': <small><small>[' . $title . ']</small></small>', 'file file');

		if ($isNew && $this->canDo->get('core.create', 'com_biblestudy'))
		{
			JToolbarHelper::apply('templatecode.apply');
			JToolbarHelper::save('templatecode.save');
			JToolbarHelper::save2new('templatecode.save2new');
			JToolbarHelper::cancel('templatecode.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_biblestudy'))
			{
				JToolbarHelper::apply('templatecode.apply');
				JToolbarHelper::save('templatecode.save');
				JToolbarHelper::save2copy('templatecode.save2copy');
			}

			JToolbarHelper::cancel('templatecode.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('templatecodehelp', true);
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
