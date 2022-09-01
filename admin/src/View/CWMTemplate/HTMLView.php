<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMTemplate;
defined('_JEXEC') or die;
// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Input\Input;

defined('_JEXEC') or die;

/**
 * View class for Template
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HTMLView extends BaseHtmlView
{
	/**
	 * Pagination
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var array
	 * @since    7.0.0
	 */
	protected $state;

	/**
	 * Item
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $item;

	/**
	 * Types
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $types;

	/**
	 * Form
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $form;

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
	 * @return  void  A string if successful, otherwise a JError object.
	 *
	 * @throws \Exception
	 * @since   11.1
	 * @see     fetch()
	 */
	public function display($tpl = null)
	{
		$this->item       = $this->get('Item');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');
		$this->types      = $this->get('Types');
		$this->form       = $this->get("Form");
		$this->canDo      = CWMProclaimHelper::getActions($this->item->id, 'template');

		$this->setLayout("edit");

		// Set the toolbar
		$this->addToolbar();

		// Set the document
		$this->setDocument();

		// Display the template
		parent::display($tpl);
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
		Factory::getApplication()->input->set('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		$title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
		ToolbarHelper::title(Text::_('JBS_CMN_TEMPLATE') . ': <small><small>[' . $title . ']</small></small>', 'square square');

		if ($isNew && $this->canDo->get('core.create', 'com_proclaim'))
		{
			ToolbarHelper::apply('cwmtemplate.apply');
			ToolbarHelper::save('cwmtemplate.save');
			ToolbarHelper::save2new('cwmtemplate.save2new');
			ToolbarHelper::cancel('cwmtemplate.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_proclaim'))
			{
				ToolbarHelper::apply('cwmtemplate.apply');
				ToolbarHelper::save('cwmtemplate.save');

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($this->canDo->get('core.create', 'com_proclaim'))
				{
					ToolbarHelper::save2new('cwmtemplate.save2new');
				}
			}

			// If checked out, we can still save
			if ($this->canDo->get('core.create', 'com_proclaim'))
			{
				ToolbarHelper::save2copy('cwmtemplate.save2copy');
			}

			ToolbarHelper::cancel('cwmtemplate.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('biblestudy', true);
	}

	/**
	 * Add the page title to browser.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since    7.1.0
	 */
	protected function setDocument()
	{
		$isNew    = ($this->item->id < 1);
		$document = Factory::getApplication()->getDocument();
		$document->setTitle($isNew ? Text::_('JBS_TITLE_TEMPLATES_CREATING') : Text::sprintf('JBS_TITLE_TEMPLATES_EDITING', $this->item->title));
	}
}
