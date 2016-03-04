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
 * @property mixed document
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewMediafileform extends JViewLegacy
{

	/** @var  string Upload Folder */
	public $upload_folder;

	/** @var  string Upload Folder */
	public $upload_server;

	/** @var JForm Form */
	protected $form;

	/** @var object Item */
	protected $item;

	/** @var string Return Page */
	protected $return_page;

	/** @var array State */
	protected $state;

	/** @var  Registry Params */
	protected $params;

	/** @var  object Media Form */
	protected $media_form;

	/** @var  string Can Do */
	protected $canDo;

	/** @var object */
	protected $options;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{

		$app              = JFactory::getApplication();
		$this->form       = $this->get("Form");
		$this->media_form = $this->get("MediaForm");
		$this->item       = $this->get("Item");
		$this->state      = $this->get("State");
		$this->canDo      = JBSMBibleStudyHelper::getActions($this->item->id, 'mediafile');
		$this->params     = $this->state->get('admin');

		$language = JFactory::getLanguage();
		$language->load('', JPATH_ADMINISTRATOR, null, true);

		if (!$this->params->def('page_title', ''))
		{
			define('JBSPAGETITLE', 0);
		}
		else
		{
			define('JBSPAGETITLE', 1);
		}

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

			return;
		}

		// Create a shortcut to the parameters.
		$params = &$this->state->params;

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->setLayout('edit');

		// Set the document
		$this->_prepareDocument();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('JBS_FORM_EDIT_ARTICLE'));
		}
		if (JBSPAGETITLE)
		{
			$title = $this->params->def('page_title', '');
		}
		else
		{
			$title = JText::_('JBS_CMN_JOOMLA_BIBLE_STUDY');
		}
		$isNew = ($this->item->id == 0);
		$state = $isNew ? JText::_('JBS_CMN_NEW') : JText::sprintf('JBS_CMN_EDIT', $this->form->getValue('studytitle'));
		$title .= ' : ' . $state;

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}
		$this->document->setTitle($title);

		$pathway = $app->getPathWay();
		$pathway->addItem($title, '');

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}

}
