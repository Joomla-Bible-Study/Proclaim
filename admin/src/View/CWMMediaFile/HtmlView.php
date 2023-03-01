<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\View\CWMMediaFile;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;
use JText;

/**
 * View class for MediaFile
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
	/** @var object
	 * @since    7.0.0
	 */
	public $canDo;

	/** @var Registry
	 * @since    7.0.0
	 */
	public $admin_params;

	/** @var object
	 * @since    7.0.0
	 */
	//public mixed $form;

	/** @var object
	 * @since    7.0.0
	 */
	public $media_form;

	/** @var object
	 * @since    7.0.0
	 */
	public $item;

	/** @var Registry
	 * @since    7.0.0
	 */
	protected $state;

	/** @var object
	 * @since    7.0.0
	 */
	protected $admin;

	/** @var object
	 * @since    7.0.0
	 */
	protected $options;

	/** @var object
	 * @since    9.1.3
	 */
	public $addon;

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
	public function display($tpl = null): void
	{
		$app                = Factory::getApplication();
		$this->form         = $this->get("Form");
		$this->media_form   = $this->get("MediaForm");
		$this->item         = $this->get("Item");
		$this->state        = $this->get("State");
		$this->canDo        = ContentHelper::getActions('com_proclaim', 'mediafile', (int) $this->item->id);
		$this->admin_params = $this->state->get('administrator');

		// Load the addon
		$this->addon = CWMAddon::getInstance($this->media_form->type);

		$options       = $app->input->get('options');
		$this->options = new \stdClass;

		$this->options->study_id   = null;
		$this->options->createdate = null;

		if ($options)
		{
			$options = explode('&', base64_decode($app->input->get('options')));

			foreach ($options as $option_st)
			{
				$option_st = explode('=', $option_st);

				if ($option_st[0] === 'study_id')
				{
					$this->options->study_id = $option_st[1];
				}

				if ($option_st[0] === 'createdate')
				{
					$this->options->createdate = $option_st[1];
				}
			}
		}

		// Needed to load the article field type for the article selector
		FormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_content/models/fields/modal');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

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
	 * @throws \Exception
	 * @since 7.0.0
	 */
	protected function addToolbar(): void
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$user       = $user = Factory::getApplication()->getSession()->get('user');
		$userId     = $user->get('id');
		$isNew      = (empty($this->item->id));
		$checkedOut = !($this->item->checked_out === '0' || $this->item->checked_out == $userId);
		$title      = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		ToolbarHelper::title(JText::_('JBS_CMN_MEDIA_FILES') . ': <small><small>[' . $title . ']</small></small>', 'video video');

		if ($isNew && $this->canDo->get('core.create', 'com_proclaim'))
		{
			ToolbarHelper::apply('cwmmediafile.apply');
			ToolbarHelper::save('cwmmediafile.save');
			ToolbarHelper::cancel('cwmmediafile.cancel');
			ToolbarHelper::checkin('cwmmediafile.checkin');
		}
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut && $this->canDo->get('core.edit', 'com_proclaim'))
			{
				ToolbarHelper::apply('cwmmediafile.apply');
				ToolbarHelper::save('cwmmediafile.save');

				if ($this->canDo->get('core.create', 'com_proclaim'))
				{
					ToolbarHelper::save2new('cwmmediafile.save2new');
				}
			}

			// If checked out, we can still save
			if ($this->canDo->get('core.create', 'com_proclaim'))
			{
				ToolbarHelper::save2copy('cwmmediafile.save2copy');
			}

			ToolbarHelper::cancel('cwmmediafile.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('biblestudy', true);
	}
}
