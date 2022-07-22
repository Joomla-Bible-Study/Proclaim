<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\View\CWMMediaFileForm;
// No Direct Access
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Form\FormHelper;

/**
 * View class for MediaFile
 *
 * @property mixed document
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
	/** @var  string Upload Folder
	 *
	 * @since 7.0 */
	public $upload_folder;

	/** @var  string Upload Folder
	 *
	 * @since 7.0 */
	public $upload_server;

	/**
	 * @var  string  Page Class
	 *
	 * @since 7.0
	 */
	public $pageclass_sfx;

	/** @var \Joomla\CMS\Form\Form Form
	 *
	 * @since 7.0 */
	protected $form;

	/** @var object Item
	 *
	 * @since 7.0 */
	protected $item;

	/** @var string Return Page
	 *
	 * @since 7.0 */
	protected $return_page;

	/** @var array State
	 *
	 * @since 7.0 */
	protected $state;

	/** @var  Registry Params
	 *
	 * @since 7.0 */
	protected $params;

	/** @var  object Media Form
	 *
	 * @since 7.0 */
	protected $media_form;

	/** @var  string Can Do
	 *
	 * @since 7.0 */
	protected $canDo;

	/** @var object
	 *
	 * @since 7.0 */
	protected $options;

	/** @var object
	 * @since    9.1.3 */
	public $addon;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 *
	 * @since 7.0
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$app              = Factory::getApplication();
		$this->form       = $this->get("Form");
		$this->media_form = $this->get("MediaForm");
		$this->item       = $this->get("Item");
		$this->state      = $this->get("State");
		$this->canDo      = CWMProclaimHelper::getActions($this->item->id, 'mediafile');
		$this->params     = $this->state->get('administrator');

		// Load the addon @todo This does not seem to be used and the class doesn't exist
		//$this->addon = JBSMAddon::getInstance($this->media_form->type);

		$language = Factory::getLanguage();
		$language->load('', JPATH_ADMINISTRATOR, null, true);

		if (!$this->canDo->get('core.edit'))
		{
			Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
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
		FormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_content/models/fields/modal');

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
		$this->prepareDocument();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @since 9.0.0
	 * @throws Exception
	 */
	protected function prepareDocument()
	{
		$app   = Factory::getApplication();
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
			$this->params->def('page_heading', Text::_('JBS_FORM_EDIT_ARTICLE'));
		}

		if (JBSPAGETITLE)
		{
			$title = $this->params->def('page_title', '');
		}
		else
		{
			$title = Text::_('JBS_CMN_JOOMLA_BIBLE_STUDY');
		}

		$isNew = ($this->item->id == 0);
		$state = $isNew ? Text::_('JBS_CMN_NEW') : Text::sprintf('JBS_CMN_EDIT', $this->form->getValue('studytitle'));
		$title .= ' : ' . $state;

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		$pathway = $app->getPathway();
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
