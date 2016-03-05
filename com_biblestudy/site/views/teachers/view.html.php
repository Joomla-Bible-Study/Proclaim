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

/**
 * View class for Teachers
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewTeachers extends JViewLegacy
{

	/**
	 * Document
	 *
	 * @var JDocument
	 */
	public $document;

	/**
	 * Template Table
	 *
	 * @var TableTemplate
	 */
	public $template;

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
	 * @var Joomla\Registry\Registry
	 */
	protected $state = null;

	/**
	 * Params
	 *
	 * @var Joomla\Registry\Registry
	 */
	protected $params = null;

	/**
	 * Admin
	 *
	 * @var object
	 */
	protected $admin;

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
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{

		$state  = $this->get('State');
		$items  = $this->get('Items');

		/** @var $params Joomla\Registry\Registry */
		$params	= $state->template->params;

		$this->template        = $state->get('template');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JFactory::getApplication()->enqueueMessage(implode("\n", $errors), 'worning');

			return;
		}

		// Load the Admin settings and params from the template
		$this->admin = $state->get('admin');
		$uri                = new JUri;

		JHtml::_('biblestudy.framework');

		$images = new JBSMImages;
		if ($params->get('useexpert_teacherdetail') > 0 || is_string($params->get('teacherstemplate')))
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
					$items[$i]->teacherlink = JRoute::_('index.php?option=com_biblestudy&view=teacher&id=' . $item->slug . '&t=' . $this->template->id);

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

		/** @var Joomla\Registry\Registry $itemparams */
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
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
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
			$this->document->setMetadata('keywords', $this->admin->params->get('metakey'));
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
			$this->document->setDescription($this->admin->params->get('metadesc'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}

}
