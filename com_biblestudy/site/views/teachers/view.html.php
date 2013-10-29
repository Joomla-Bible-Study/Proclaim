<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package        BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for Teachers
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewTeachers extends JViewLegacy
{

	/**
	 * Items
	 *
	 * @var object
	 */
	protected $items = null;

	/**
	 * Pagination
	 *
	 * @var object
	 */
	protected $pagination;

	/**
	 * State
	 *
	 * @var JRegistry
	 */
	protected $state = null;

	/**
	 * Params
	 *
	 * @var object
	 */
	protected $params = null;

	/**
	 * Item
	 *
	 * @var object
	 */
	protected $item = null;

	/**
	 * Admin
	 *
	 * @var JObject
	 */
	protected $admin;

	/**
	 * Admin Params
	 *
	 * @var JRegistry
	 */
	protected $admin_params;

	/**
	 * Page
	 *
	 * @var object
	 */
	protected $page;

	/**
	 * Request Url
	 *
	 * @var string
	 */
	protected $request_url;

	/**
	 * Document
	 *
	 * @var object
	 */
	public $document;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{

		$state = $this->get('State');
		$items = $this->get('Items');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JFactory::getApplication()->enqueueMessage(implode("\n", $errors), 'worning');

			return;
		}

		// Load the Admin settings and params from the template
		$this->admin = JBSMParams::getAdmin(true);
		$template = JBSMParams::getTemplateparams();

		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadString($template->params);
		$params = $registry;
		$state->params->merge($params);
		$t = $params->get('teachertemplateid');

		if (!$t)
		{
			$input = new JInput;
			$t     = $input->get('t', 1, 'int');
		}
		$this->template = $t;

		// Convert parameter fields to objects.
		$registry = new JRegistry;
		$registry->loadString($this->admin->params);
		$this->admin_params = $registry;
		$document           = JFactory::getDocument();
		$uri                = new JUri;
		$document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/noconflict.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/tooltip.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');

		// Import Stylesheets
		$document->addStylesheet(JURI::base() . 'media/com_biblestudy/css/general.css');
		$document->addStylesheet(JURI::base() . 'media/com_biblestudy/css/studieslist.css');
		$css = $params->get('css');

		if ($css <= "-1")
		{
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
		}
		else
		{
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
		}
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap-responsive.css');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap-extended.css');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap-responsive-min.css');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap.css');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap-min.css');

		$images = new JBSMImages;
		if ($params->get('useexpert_teacherdetail') > 0 && !$params->get('teacherstemplate'))
		{
			$pagebuilder = new JBSMPagebuilder;

			foreach ($items as $i => $item)
			{
				if (isset($item->teacher_thumbnail))
				{
					$image                  = $images->getTeacherThumbnail($item->teacher_thumbnail, $item->thumb);
					$items[$i]->image       = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width
						. '" alt="' . $item->teachername . '" />';
					$items[$i]->slug        = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':'
						. str_replace(' ', '-', htmlspecialchars_decode($item->teachername, ENT_QUOTES));
					$items[$i]->teacherlink = JRoute::_('index.php?option=com_biblestudy&view=teacher&id=' . $item->slug . '&t=' . $t);

					if (isset($items[$i]->information))
					{
						$items[$i]->text        = $items[$i]->information;
						$information            = $pagebuilder->runContentPlugins($items[$i], $params);
						$items[$i]->information = $information->text;
					}
					if (isset($items[$i]->short))
					{
						$items[$i]->text  = $items[$i]->short;
						$short            = $pagebuilder->runContentPlugins($items[$i], $params);
						$items[$i]->short = $short->text;
					}
				}

			}
		}
		$pagination            = $this->get('Pagination');
		$this->page            = new stdClass;
		$this->page->pagelinks = $pagination->getPagesLinks();
		$this->page->counter   = $pagination->getPagesCounter();
		$this->pagination      = $pagination;
		$stringuri             = $uri->toString();
		$this->request_url     = $stringuri;
		$this->params          = $params;
		$this->items           = $items;

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document;
	 *
	 * @return void
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication('site');
		$menus = $app->getMenu();

		/** @var $itemparams JRegistry */
		$itemparams = $app->getParams();
		$title      = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}
		$title = $this->params->get('page_title', '');
		$title .= ' : ' . JText::_('JBS_CMN_TEACHERS');

		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		// Prepare meta information (under development)
		if ($itemparams->get('metakey'))
		{
			$this->document->setMetadata('keywords', $itemparams->get('metakey'));
		}
		elseif ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		else
		{
			$this->document->setMetadata('keywords', $this->admin_params->get('metakey'));
		}

		if ($itemparams->get('metadesc'))
		{
			$this->document->setDescription($itemparams->get('metadesc'));
		}
		elseif ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}
		else
		{
			$this->document->setDescription($this->admin_params->get('metadesc'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}

}
