<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMMessage;

// No Direct Access
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use JHtml;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use JText;
use JToolbarHelper;

/**
 * View class for Message
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HTMLView extends BaseHtmlView
{
	/**
	 * Form
	 *
	 * @var mixed
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
	 * Admin
	 *
	 * @var array
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
	 * Media Files
	 *
	 * @var string
	 * @since    7.0.0
	 */
	protected $mediafiles;

	/**
	 * Admin Params
	 *
	 * @var Registry
	 * @since    7.0.0
	 */
	protected $admin_params;

	/**
	 * Simple mode object
	 *
	 * @var   object
	 * @since 9.2.3
	 */
	protected $simple;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 * @since   11.1
	 * @see     fetch()
	 */
	public function display($tpl = null)
	{
		$this->form       = $this->get("Form");
		$this->item       = $this->get("Item");
		$this->canDo      = CWMProclaimHelper::getActions($this->item->id, 'message');
		$input            = new Input;
		$option           = $input->get('option', '', 'cmd');
		$this->mediafiles = $this->get('MediaFiles');
		$this->state      = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors), 500);
		}

		// Set some variables for use by the modal mediafile entry form from a study
		$app = Factory::getApplication();
		$app->setUserState($option . 'sid', $this->item->id);
		$app->setUserState($option . 'sdate', $this->item->studydate);
		$this->admin = CWMParams::getAdmin();
		$registry    = new Registry;
		$registry->loadString($this->admin->params);
		$this->admin_params = $registry;
		$document           = Factory::getDocument();

		$this->simple = CWMHelper::getSimpleView();

		JHtml::stylesheet('media/com_proclaim/css/token-input-jbs.css');

		// JHtml::_('proclaim.framework');
		$script = "
            jQuery(document).ready(function() {
                jQuery('#topics').tokenInput(" . $this->get('alltopics') . ",
                {
                    theme: 'jbs',
                    hintText: '" . Text::_('JBS_CMN_TOPIC_TAG') . "',
                    noResultsText: '" . Text::_('JBS_CMN_NOT_FOUND') . "',
                    searchingText: '" . Text::_('JBS_CMN_SEARCHING') . "',
                    animateDropdown: false,
                    preventDuplicates: true,
                    allowFreeTagging: true,
                    prePopulate: " . $this->get('topics') . "
                });
            });
             ";

		$document->addScriptDeclaration($script);

		JHtml::script('media/com_proclaim/js/plugins/jquery.tokeninput.js');

		// Set the toolbar
		$this->addToolbar();

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
		$isNew = ($this->item->id == 0);
		$title = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolbarHelper::title(JText::_('JBS_CMN_STUDIES') . ': <small><small>[ ' . $title . ' ]</small></small>', 'book book');

		if ($isNew && $this->canDo->get('core.create', 'com_proclaim'))
		{
			JToolbarHelper::apply('cwmmessage.apply');
			JToolbarHelper::save('cwmmessage.save');
			JToolbarHelper::save2new('cwmmessage.save2new');
			JToolbarHelper::cancel('cwmmessage.cancel');
		}
		else
		{
			if ($this->canDo->get('core.edit', 'com_proclaim'))
			{
				JToolbarHelper::apply('cwmmessage.apply');
				JToolbarHelper::save('cwmmessage.save');

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($this->canDo->get('core.create', 'com_proclaim'))
				{
					JToolbarHelper::save2new('cwmmessage.save2new');
				}
			}

			// If checked out, we can still save
			if ($this->canDo->get('core.create', 'com_proclaim'))
			{
				JToolbarHelper::save2copy('cwmmessage.save2copy');
			}

			JToolbarHelper::cancel('cwmmessage.cancel', 'JTOOLBAR_CLOSE');

			if ($this->canDo->get('core.edit', 'com_proclaim'))
			{
				JToolbarHelper::divider();
				JToolbarHelper::custom('resetHits', 'reset.png', 'Reset Hits', 'JBS_STY_RESET_HITS', false);
			}
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('biblestudy', true);
	}
}
