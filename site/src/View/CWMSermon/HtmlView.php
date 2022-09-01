<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\View\CWMSermon;
defined('_JEXEC') or die;
// No Direct Access
use JForm;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use CWM\Component\Proclaim\Site\Helper\CWMListing;
use CWM\Component\Proclaim\Site\Helper\CWMShowScripture;
use CWM\Component\Proclaim\Site\Helper\CWMHelperRoute;
use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use CWM\Component\Proclaim\Site\Helper\CWMRelatedstudies;
use CWM\Component\Proclaim\Site\Helper\CWMPagebuilder;
use CWM\Component\Proclaim\Site\Helper\CWMPodcastsubscribe;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

/**
 * View class for Sermon
 *
 * @property mixed document
 * @package  BibleStudy.Site
 * @since    7.0.0
 *
 * @todo     Still need to fix all the problems.
 */
class HtmlView extends BaseHtmlView
{
	/** @var object Item
	 *
	 * @since 7.0
	 */
	protected $item;

	/** @var Registry Params
	 *
	 * @since 7.0
	 */
	protected $params;

	/** @var  string Print
	 *
	 * @since 7.0
	 */
	protected $print;

	/** @var Registry State
	 *
	 * @since 7.0
	 */
	protected $state;

	/** @var  string User
	 *
	 * @since 7.0
	 */
	protected $user;

	/** @var  string Passage
	 *
	 * @since 7.0
	 */
	protected $passage;

	/** @var  string Related
	 *
	 * @since 7.0
	 */
	protected $related;

	/** @var  string Subscribe
	 *
	 * @since 7.0
	 */
	protected $subscribe;

	/** @var  integer Menu ID
	 *
	 * @since 7.0
	 */
	protected $menuid;

	/** @var  string Details Link
	 *
	 * @since 7.0
	 */
	protected $detailslink;

	/** @var  string Page
	 *
	 * @since 7.0
	 */
	protected $page;

	/** @var  string Template
	 *
	 * @since 7.0
	 */
	protected $template;

	/** @var  string Article
	 *
	 * @since 7.0
	 */
	protected $article;

	/** @var  array Article
	 *
	 * @since 7.0
	 */
	protected $comments;

	/**
	 * Simple Mode object
	 *
	 * @var object
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
	 * @throws \Exception
	 * @since 7.0
	 */
	public function display($tpl = null)
	{
        $container = Factory::getContainer();
        $app = $container->get(SiteApplication::class);
        Factory::$application = $app;
        HTMLHelper::script('media/com_proclaim/js/cwmcore.js');
        $this->form = $this->get('Form');
        $user           = Factory::getApplication()->getSession()->get('user');
		$CWMListing = new CWMListing;
		$this->item     = $this->get('Item');
		//$this->print    = $app->input->getBool('print'); removing this as also remomved in Joomla 4. Browser will do this.
		$this->state    = $this->get('State');
		$this->user     = $user;
		$this->comments = $this->get('comments');
        $this->simple = CWMHelper::getSimpleView();
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode("\n", $errors), 'error');

			return;
		}

		// Create a shortcut for $item.
		$item = &$this->item;

		if (!$item)
		{
			return;
		}

		if ($this->getLayout() === 'pagebreak')
		{
			$this->_displayPagebreak($tpl);

			return null;
		}

		$Biblepassage  = new CWMShowScripture;
		$this->passage = $Biblepassage->buildPassage($this->item, $this->item->params);

		// Add router helpers.
		$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

		//$item->readmore_link = Route::_(CWMHelperRoute::getArticleRoute($item->slug, ''));

		// Merge article params. If this is single-article view, menu params override article params
		// Otherwise, article params override menu item params
		$this->params = $this->state->template->params;
		$active       = $app->getMenu()->getActive();
		$temp         = clone $this->params;

		// Check to see which parameters should take priority
		if ($active)
		{
			$currentLink = $active->link;

			// If the current view is the active item and an article view for this article, then the menu item params take priority
			if (strpos($currentLink, 'view=CWMSermon') && (strpos($currentLink, '&id=' . (string) $item->id)))
			{
				// $item->params are the article params, $temp are the menu item params
				// Merge so that the menu item params take priority
				$item->params->merge($temp);

				// Load layout from active query (in case it is an alternative menu item)
				if (isset($active->query['layout']))
				{
					$this->setLayout($active->query['layout']);
				}
			}
			else
			{
				// Current view is not a single article, so the article params take priority here
				// Merge the menu item params with the article params so that the article params take priority
				$temp->merge($item->params);
				$item->params = $temp;

				// Check for alternative layouts (since we are not in a single-article menu item)
				// Single-article menu item layout takes priority over alt layout for an article
				$layout = $item->params->get('sermon_layout');

				if ($layout)
				{
					$this->setLayout($layout);
				}
			}
		}
		else
		{
			// Merge so that article params take priority
			$temp->merge($item->params);
			$item->params = $temp;

			// Check for alternative layouts (since we are not in a single-article menu item)
			// Single-article menu item layout takes priority over alt layout for an article
			$layout = $item->params->get('sermon_layout');

			if ($layout)
			{
				$this->setLayout($layout);
			}
		}

		// Technically guest could edit an article, but lets not check that to improve performance a little.
		if (!$user->get('guest'))
		{
			$userId = $user->get('id');
			$asset  = 'com_proclaim.CWMMessage.' . $item->id;

			// Check general edit permission first.
			if ($user->authorise('core.edit', $asset))
			{
				$item->params->set('access-edit', true);
			}

			// Now check if edit.own is available.
			elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
			{
				// Check for a valid user and that they are the owner.
				if ($userId === $item->created_by)
				{
					$item->params->set('access-edit', true);
				}
			}
		}
        $this->captchaEnabled = true;

		$this->simple = CWMHelper::getSimpleView();

		$offset = $this->state->get('list.offset');

		// Check the view access to the article (the model has already computed the values).
		if ($item->params->get('access-view') !== true && (($item->params->get('show_noauth') !== true && $user->get('guest'))))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
		}

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$groups = $user->getAuthorisedViewLevels();

		if ($this->item->access > 1)
		{
			if (!in_array($this->item->access, $groups))
			{
				$app->enqueueMessage(Text::_('JBS_CMN_ACCESS_FORBIDDEN'), 'error');
			}
		}

		// Get Scripture references from listing class in case we don't use the pagebuilder class
		$this->item->scripture1 = $CWMListing->getScripture($this->params, $item, $esv = 0, $scripturerow = 1);
		$this->item->scripture2 = $CWMListing->getScripture($this->params, $item, $esv = 0, $scripturerow = 2);

		// @todo check to see if this works
		$this->item->topics = $this->item->topic_text;
		$relatedstudies     = new CWMRelatedStudies;

		$template      = $this->get('template');
		$this->related = $relatedstudies->getRelated($this->item, $item->params);

		//HtmlHelper::_('proclaim.framework');
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wa->useStyle('com_proclaim.cwmcore');
		$wa->useStyle('com_proclaim.general');

		// Only load page builder if the default template is NOT being used
		if ($this->item->params->get('useexpert_details', '0') !== '0' || $this->params->get('sermontemplate', '0') !== '0')
		{
			$pagebuilder            = new CWMPageBuilder;
			$pelements              = $pagebuilder->buildPage($this->item, $this->item->params, $template);
			$this->item->scripture1 = $pelements->scripture1;
			$this->item->scripture2 = $pelements->scripture2;
			$this->item->media      = $pelements->media;
			$this->item->studydate  = $pelements->studydate;

			if (isset($pelements->secondary_reference))
			{
				$this->item->secondary_reference = $pelements->secondary_reference;
			}
			else
			{
				$this->item->secondary_reference = '';
			}

			if (isset($pelements->topics))
			{
				$this->item->topics = $pelements->topics;
			}
			else
			{
				$this->item->topics = '';
			}

			if (isset($pelements->study_thumbnail))
			{
				$this->item->study_thumbnail = $pelements->study_thumbnail;
			}
			else
			{
				$this->item->study_thumbnail = null;
			}

			if (isset($pelements->series_thumbnail))
			{
				$this->item->series_thumbnail = $pelements->series_thumbnail;
			}
			else
			{
				$this->item->series_thumbnail = null;
			}

			$this->item->detailslink = $pelements->detailslink;

			if (isset($pelements->teacherimage))
			{
				$this->item->teacherimage = $pelements->teacherimage;
			}
			else
			{
				$this->item->teacherimage = null;
			}
		}

        PluginHelper::importPlugin('content');
		$article       = new \stdClass;
		$article->text = $this->item->scripture1;
        Factory::getApplication()->triggerEvent('onContentPrepare', array('com_proclaim.sermons', & $article, & $this->item->params, $limitstart = null));
		$this->item->scripture1 = $article->text;
		$article->text          = $this->item->scripture2;
        Factory::getApplication()->triggerEvent('onContentPrepare', array('com_proclaim.sermons', & $article, & $this->item->params, $limitstart = null));
		$this->item->scripture2 = $article->text;
		$article->text          = $this->item->studyintro;
        Factory::getApplication()->triggerEvent('onContentPrepare', array('com_proclaim.sermons', & $article, & $this->item->params, $limitstart = null));
		$this->item->studyintro = $article->text;
		$article->text          = $this->item->secondary_reference;
        Factory::getApplication()->triggerEvent('onContentPrepare', array('com_proclaim.sermons', & $article, & $this->item->params, $limitstart = null));
		$this->item->secondary_reference = $article->text;
		$this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
		$this->loadHelper('params');

		// Get the podcast subscription
        HtmlHelper::_('stylesheet','media/css/podcast.css');
		$podcast         = new CWMPodcastSubscribe;
		$this->subscribe = $podcast->buildSubscribeTable($this->item->params->get('subscribeintro', 'Our Podcasts'));

		// Passage link to BibleGateway
		$plugin = PluginHelper::getPlugin('content', 'scripturelinks');

		if ($plugin)
		{
			$plugin = PluginHelper::getPlugin('content', 'scripturelinks');

			// Convert parameter fields to objects.
			$registry = new Registry;
			$registry->loadString($plugin->params);
			$st_params  = $registry;
			$version    = $st_params->get('bible_version');
			$windowopen = "window.open(this.href,this.target,'width=800,height=500,scrollbars=1');return false;";
		}

		// Added database queries from the default template - moved here instead
		$database = Factory::getContainer()->get('DatabaseDriver');
		$query    = $database->getQuery(true);
		$query->select('id')->from('#__menu')->where('link =' . $database->q('index.php?option=com_proclaim&view=CWMSermons'))->where('published = 1');
		$database->setQuery($query);
		$menuid       = $database->loadResult();
		$this->menuid = $menuid;

		if ($this->getLayout() === 'pagebreak')
		{
			$this->_displayPagebreak($tpl);

			return null;
		}

		// Process the prepare content plugins
		$article->text = $this->item->studytext;
		$linkit        = $this->item->params->get('show_scripture_link');

		if ($linkit)
		{
			switch ($linkit)
			{
				case 0:
					break;
				case 1:
					PluginHelper::importPlugin('content');
					break;
				case 2:
					PluginHelper::importPlugin('content', 'scripturelinks');
					break;
			}

			$limitstart = $app->input->get('limitstart', 'int');
            Factory::getApplication()->triggerEvent('onContentPrepare', array('com_proclaim.sermon', & $article, & $this->item->params, $limitstart));
			$article->studytext    = $article->text;
			$this->item->studytext = $article->text;
		}

		$Biblepassage  = new CWMShowScripture;
		$this->passage = $Biblepassage->buildPassage($this->item, $this->item->params);

		// Prepares a link string for use in social networking
		$u                 = Uri::getInstance();
		$detailslink       = htmlspecialchars($u->toString());
		$detailslink       = Route::_($detailslink);
		$this->detailslink = $detailslink;

		$this->page         = new \stdClass;
		$this->page->social = $CWMListing->getShare($detailslink, $this->item, $this->item->params);

		// End process prepare content plugins
		$this->template = $template;
		$this->article  = $article;

		// Increment the hit counter of the Sermon.
		if (!$this->params->get('intro_only') && $offset == 0)
		{
			$model = $this->getModel();
			$model->hit();
		}

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Display PageBrack
	 *
	 * @param   string  $tpl  ?
	 *
	 * @return void
	 *
	 * @since 7.0
	 */
	protected function _displayPagebreak($tpl)
	{
		$this->document->setTitle(Text::_('JBS_CMN_READ_MORE'));
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since 7.0
	 */
	protected function _prepareDocument()
	{
		$app     = Factory::getApplication();
		$menus   = $app->getMenu();
		$pathway = $app->getPathway();
		$title   = null;

		$this->item->metadesc = $this->item->studyintro;
		$this->item->metakey  = $this->item->topics;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('JBS_CMN_MESSAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		$id = (int) @$menu->query['id'];

		// If the menu item does not concern this Study
		if ($menu && ($menu->query['option'] !== 'com_proclaim' || $menu->query['view'] !== 'sermon' || $id !== $this->item->id))
		{
			// If this is not a single article menu item, set the page title to the article title
			if ($this->item->studytitle)
			{
				$title = $this->item->studytitle;
			}

			$path = array(array('studytitle' => $this->item->studytitle, 'link' => '')
			);

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$pathway->addItem($item['studytitle'], $item['link']);
			}
		}

		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) === 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) === 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		if (empty($title))
		{
			$title = $this->item->studytitle;
		}

		$this->document->setTitle($title);

		if ($this->item->params->get('metadesc'))
		{
			$this->document->setDescription($this->item->params->get('metadesc'));
		}
		elseif (!$this->item->params->get('metadesc'))
		{
			$this->document->setDescription($this->item->studyintro);
		}

		if ($this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}
		elseif (!$this->item->metakey && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		// Prepare meta information (under development)
		if ($this->item->params->get('metakey'))
		{
			$this->document->setMetadata('keywords', $this->item->params->get('metakey'));
		}
		elseif (!$this->item->params->get('metakey'))
		{
			$this->document->setMetadata('keywords', $this->item->topic_text . ',' . $this->item->studytitle);
		}

		if ($app->get('MetaAuthor') === '1')
		{
			$this->document->setMetaData('author', $this->item->teachername);
		}

		// If there is a pagebreak heading or title, add it to the page title
		if (!empty($this->item->page_title))
		{
			$this->item->title .= ' - ' . $this->item->page_title;
			$this->document->setTitle(
				$this->item->page_title . ' - '
				. Text::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $this->state->get('list.offset') + 1)
			);
		}

		if ($this->print)
		{
			$this->document->setMetaData('robots', 'noindex, nofollow');
		}
	}
}
