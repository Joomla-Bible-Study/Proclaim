<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMServer;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Input\Input;

defined('_JEXEC') or die;

/**
 * View class for Server
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HTMLView extends BaseHtmlView
{
	/**
	 * Form
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected mixed $form;

	/**
	 * Server form
	 *
	 * @var string
	 * @since    7.0.0
	 */
	protected $server_form;

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
	 * Admin
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected $admin;

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
		$this->form        = $this->get("form");
		$this->server_form = $this->get('AddonServerForm');

		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = CWMProclaimHelper::getActions($this->item->id, 'server');

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
		$input = new Input;
		$input->set('hidemainmenu', true);
		$isNew = ($this->item->id < 1);
		$canDo = ContentHelper::getActions('com_proclaim');
		$title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
		ToolbarHelper::title(Text::_('JBS_CMN_SERVERS') . ': <small><small>[' . $title . ']</small></small>', 'database database');

		if ($isNew && $canDo->get('core.create', 'com_proclaim'))
		{
			ToolbarHelper::apply('server.apply');
			ToolbarHelper::save('server.save');
			ToolbarHelper::save2new('server.save2new');
			ToolbarHelper::cancel('server.cancel');
		}
		else
		{
			if ($canDo->get('core.edit', 'com_proclaim'))
			{
				ToolbarHelper::apply('server.apply');
				ToolbarHelper::save('server.save');

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create', 'com_proclaim'))
				{
					ToolbarHelper::save2new('server.save2new');
				}
			}

			// If checked out, we can still save
			if ($canDo->get('core.create', 'com_proclaim'))
			{
				ToolbarHelper::save2copy('server.save2copy');
			}

			ToolbarHelper::cancel('server.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('biblestudy', true);
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
		$document = Factory::getDocument();
		$document->setTitle($isNew ? Text::_('JBS_TITLE_SERVERS_CREATING') : Text::sprintf('JBS_TITLE_SERVERS_EDITING', $this->item->server_name));
	}
}
