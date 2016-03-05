<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View class for MediaFile
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyViewMediafile extends JViewLegacy
{

	/** @var object */
	public $canDo;

	/** @var Registry */
	public $admin_params;

	/** @var object */
	protected $form;

	/** @var object */
	protected $media_form;

	/** @var object */
	protected $item;

	/** @var Registry */
	protected $state;

	/** @var object */
	protected $admin;

	/** @var object */
	protected $options;

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
		$app                = JFactory::getApplication();
		$this->form         = $this->get("Form");
		$this->media_form   = $this->get("MediaForm");
		$this->item         = $this->get("Item");
		$this->state        = $this->get("State");
		$this->canDo        = JBSMBibleStudyHelper::getActions($this->item->id, 'mediafile');
		$this->admin_params = $this->state->get('admin');

		$options       = $app->input->get('options');
		$this->options = new stdClass;

		$this->options->study_id   = null;
		$this->options->createdate = null;
		if ($options)
		{
			$options = explode('&', base64_decode($app->input->get('options')));
			foreach ($options as $option_st)
			{
				$option_st = explode('=', $option_st);
				if ($option_st[0] == 'study_id')
				{
					$this->options->study_id = $option_st[1];
				}
				if ($option_st[0] == 'createdate')
				{
					$this->options->createdate = $option_st[1];
				}
			}
		}

		// Needed to load the article field type for the article selector
		JFormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_content/models/fields/modal');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode("\n", $errors), 'error');

			return false;
		}

		// Set the toolbar
		$this->addToolbar();

		// Set the document
		$this->setDocument();

		// Display the template
		return parent::display($tpl);
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
		$user       = JFactory::getUser();
		$userId     = $user->get('id');
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$title      = $isNew ? JText::_('JBS_CMN_NEW') : JText::_('JBS_CMN_EDIT');
		JToolBarHelper::title(JText::_('JBS_CMN_MEDIA_FILES') . ': <small><small>[' . $title . ']</small></small>', 'video video');

		if ($isNew && $this->canDo->get('core.create', 'com_biblestudy'))
		{
			JToolBarHelper::apply('mediafile.apply');
			JToolBarHelper::save('mediafile.save');
			JToolBarHelper::save2new('mediafile.save2new');
			JToolBarHelper::cancel('mediafile.cancel');
			JToolbarHelper::checkin('mediafile.checkin');
		}
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				if ($this->canDo->get('core.edit', 'com_biblestudy'))
				{
					JToolBarHelper::apply('mediafile.apply');
					JToolBarHelper::save('mediafile.save');

					if ($this->canDo->get('core.create', 'com_biblestudy'))
					{
						JToolBarHelper::save2new('mediafile.save2new');
					}
				}
			}

			// If checked out, we can still save
			if ($this->canDo->get('core.create', 'com_biblestudy'))
			{
				JToolBarHelper::save2copy('mediafile.save2copy');
			}
			JToolBarHelper::cancel('mediafile.cancel', 'JTOOLBAR_CLOSE');
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
		$isNew    = ($this->item->id < 1);
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('JBS_TITLE_MEDIA_FILES_CREATING') : JText::_('JBS_TITLE_MEDIA_FILES_EDITING'));

	}

}
