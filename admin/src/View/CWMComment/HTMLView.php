<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMComment;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') or die;

/**
 * View class for Comment
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HTMLView extends BaseHtmlView
{
	/**
	 * Can Do
	 *
	 * @var object
	 *
	 * @since 9.0.0
	 */
	public $canDo;

	/**
	 * Form Data
	 *
	 * @var object
	 *
	 * @since 9.0.0
	 */
	protected $form;

	/**
	 * Item
	 *
	 * @var object
	 *
	 * @since 9.0.0
	 */
	protected $item;

	/**
	 * State
	 *
	 * @var object
	 *
	 * @since 9.0.0
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  false|void  A string if successful, otherwise a Error object.
	 *
	 * @throws \Exception
	 * @since  9.0.0
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get("Form");
		$this->item  = $this->get("Item");
		$this->state = $this->get("State");
		$this->canDo = CWMProclaimHelper::getActions($this->item->id, 'comment');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

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
	 * @throws \Exception
	 * @since  7.0
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		$title = $isNew ? Text::_('JBS_CMN_NEW') : Text::_('JBS_CMN_EDIT');
		ToolbarHelper::title(Text::_('JBS_CMN_COMMENTS') . ': <small><small>[ ' . $title . ' ]</small></small>', 'comment comment');

		if ($isNew && $this->canDo->get('core.create', 'com_proclaim'))
		{
			ToolbarHelper::apply('cwmcomment.apply');
			ToolbarHelper::save('cwmcomment.save');
			ToolbarHelper::save2new('cwmcomment.save2new');
			ToolbarHelper::cancel('cwmcomment.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_proclaim'))
			{
				ToolbarHelper::apply('cwmcomment.apply');
				ToolbarHelper::save('cwmcomment.save');
			}

			ToolbarHelper::cancel('cwmcomment.cancel', 'JTOOLBAR_CLOSE');
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
		$document = Factory::getApplication()->getDocument();
		$document->setTitle($isNew ? Text::_('JBS_TITLE_COMMENT_CREATING') : Text::sprintf('JBS_TITLE_COMMENT_EDITING', $this->item->id));
	}
}
