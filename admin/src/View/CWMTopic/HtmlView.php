<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMTopic;

// No Direct Access
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for Topic
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Form
	 *
	 * @var object
	 * @since    7.0.0
	 */
	protected mixed $form;

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
	 * @throws  \Exception
	 * @since   11.1
	 * @see     fetch()
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get("Form");
		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = ContentHelper::getActions('com_proclaim', 'topic', (int) $this->item->id);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \RuntimeException(implode("\n", $errors), 500);
		}

		$this->setLayout("edit");

		// Set the toolbar
		$this->addToolbar();

		// Set the document
		$this->setDocument();

		// Display the template
		parent::display($tpl);
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
		Factory::getApplication()->input->set('hidemainmenu', true);
		$isNew = ((int) $this->item->id === 0);
		$title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
		ToolbarHelper::title(Text::_('JBS_CMN_TOPICS') . ': <small><small>[' . $title . ']</small></small>', 'tag tag');

		if ($isNew && $this->canDo->get('core.create', 'com_proclaim'))
		{
			ToolbarHelper::apply('cwmtopic.apply');
			ToolbarHelper::save('cwmtopic.save');
			ToolbarHelper::cancel('cwmtopic.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_proclaim'))
			{
				ToolbarHelper::apply('cwmtopic.apply');
				ToolbarHelper::save('cwmtopic.save');
			}

			ToolbarHelper::cancel('cwmtopic.cancel', 'JTOOLBAR_CLOSE');
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
		$document->setTitle($isNew ? Text::_('JBS_TITLE_TOPICS_CREATING') : Text::sprintf('JBS_TITLE_TOPICS_EDITING', $this->item->topic_text));
	}
}
