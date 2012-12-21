<?php

/**
 * Sermon JViewLegacy
 *
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;


require_once (JPATH_COMPONENT . '/lib/biblestudy.admin.class.php');
require_once (JPATH_COMPONENT . '/lib/biblestudy.pagebuilder.class.php');
require_once (JPATH_COMPONENT . '/helpers/podcastsubscribe.php');
require_once (JPATH_COMPONENT . '/helpers/related.php');
require_once (JPATH_COMPONENT . '/helpers/biblegateway.php');


/**
 * View class for Sermon
 *
 * @property mixed document
 * @package BibleStudy.Site
 * @since   7.0.0
 */
class BiblestudyViewSermon extends JViewLegacy
{

	protected $item;

	protected $params;

	protected $print;

	protected $state;

	protected $user;

	public $passage;
	public $related;
	public $subscribe;
	public $menuid;
	public $detailslink;
	public $page;
	public $template;
	public $article;

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
		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$userId = $user->get('id');
		if (BIBLESTUDY_CHECKREL) {
			$dispatcher = JEventDispatcher::getInstance();
		} else {
			$dispatcher = JDispatcher::getInstance();
		}

		$this->item  = $this->get('Item');
		$this->print = $app->input->getBool('print');
		$this->state = $this->get('State');
		$this->user  = $user;


		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		// Create a shortcut for $item.
		$item = & $this->item;

		if ($this->getLayout() == 'pagebreak') {
			$this->_displayPagebreak($tpl);

			return null;
		}
		$input = new JInput;
		$print = $input->get('print', '', 'bool');

		$Biblepassage  = new showScripture();
		$this->passage = $Biblepassage->buildPassage($this->item, $this->item->params);
		// Add router helpers.
		$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

		$item->readmore_link = JRoute::_(JBSMHelperRoute::getArticleRoute($item->slug, ''));

		// Merge article params. If this is single-article view, menu params override article params
		// Otherwise, article params override menu item params
		$this->params = $this->state->get('params');
		$active       = $app->getMenu()->getActive();
		$temp         = clone ($this->params);

		// Check to see which parameters should take priority
		if ($active) {
			$currentLink = $active->link;
			// If the current view is the active item and an article view for this article, then the menu item params take priority
			if (strpos($currentLink, 'view=sermon') && (strpos($currentLink, '&id=' . (string) $item->id))) {
				// $item->params are the article params, $temp are the menu item params
				// Merge so that the menu item params take priority
				$item->params->merge($temp);
				// Load layout from active query (in case it is an alternative menu item)
				if (isset($active->query['layout'])) {
					$this->setLayout($active->query['layout']);
				}
			} else {
				// Current view is not a single article, so the article params take priority here
				// Merge the menu item params with the article params so that the article params take priority
				$temp->merge($item->params);
				$item->params = $temp;

				// Check for alternative layouts (since we are not in a single-article menu item)
				// Single-article menu item layout takes priority over alt layout for an article
				if ($layout = $item->params->get('sermon_layout')) {
					$this->setLayout($layout);
				}
			}
		} else {
			// Merge so that article params take priority
			$temp->merge($item->params);
			$item->params = $temp;
			// Check for alternative layouts (since we are not in a single-article menu item)
			// Single-article menu item layout takes priority over alt layout for an article
			if ($layout = $item->params->get('article_layout')) {
				$this->setLayout($layout);
			}
		}

		$offset = $this->state->get('list.offset');

		// Check the view access to the article (the model has already computed the values).
		if ($item->params->get('access-view') != true && (($item->params->get('show_noauth') != true && $user->get('guest')))) {

			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'message');

		}
		//check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$groups = $user->getAuthorisedViewLevels();

		if ($this->item->access > 1) {
			if (!in_array($this->item->access, $groups)) {
				JFactory::getApplication()->enqueueMessage(JText::_('JBS_CMN_ACCESS_FORBIDDEN'), 'error');
			}
		}

		//$study = $this->item;
		$relatedstudies = new relatedStudies();

		$template      = $this->get('template');
		$this->related = $relatedstudies->getRelated($this->item, $this->item->params);

		$document = JFactory::getDocument();
		$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
		$document->addScript('http://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/jui/js/jquery.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/noconflict.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
		$css = $this->item->params->get('css');
		if ($css <= "-1"):
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css'); else:
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
		endif;

		$pagebuilder            = new JBSPagebuilder();
		$pelements              = $pagebuilder->buildPage($this->item, $this->item->params, $this->item->admin_params);
		$this->item->scripture1 = $pelements->scripture1;
		$this->item->scripture2 = $pelements->scripture2;
		$this->item->media      = $pelements->media;
		$this->item->duration   = $pelements->duration;
		$this->item->studydate  = $pelements->studydate;
		if (isset($pelements->secondary_reference)) {
			$this->item->secondary_reference = $pelements->secondary_reference;
		} else {
			$this->item->secondary_reference = '';
		}
		if (isset($pelements->topics)):
			$this->item->topics = $pelements->topics; else:
			$this->item->topics = '';
		endif;
		if (isset($pelements->study_thumbnail)):
			$this->item->study_thumbnail = $pelements->study_thumbnail; else:
			$this->item->study_thumbnail = null;
		endif;
		if (isset($pelements->series_thumbnail)):
			$this->item->series_thumbnail = $pelements->series_thumbnail; else:
			$this->item->series_thumbnail = null;
		endif;
		$this->item->detailslink = $pelements->detailslink;
		if (isset($pelements->teacherimage)):
			$this->item->teacherimage = $pelements->teacherimage; else:
			$this->item->teacherimage = null;
		endif;
		$article                         = new stdClass();
		$article->text                   = $this->item->scripture1;
		$results                         = $dispatcher->trigger('onContentPrepare', array(
			'com_biblestudy.sermons',
			& $article,
			& $this->item->params,
			$limitstart = null
		));
		$this->item->scripture1          = $article->text;
		$article->text                   = $this->item->scripture2;
		$results                         = $dispatcher->trigger('onContentPrepare', array(
			'com_biblestudy.sermons',
			& $article,
			& $this->item->params,
			$limitstart = null
		));
		$this->item->scripture2          = $article->text;
		$article->text                   = $this->item->studyintro;
		$results                         = $dispatcher->trigger('onContentPrepare', array(
			'com_biblestudy.sermons',
			& $article,
			& $this->item->params,
			$limitstart = null
		));
		$this->item->studyintro          = $article->text;
		$article->text                   = $this->item->secondary_reference;
		$results                         = $dispatcher->trigger('onContentPrepare', array(
			'com_biblestudy.sermons',
			& $article,
			& $this->item->params,
			$limitstart = null
		));
		$this->item->secondary_reference = $article->text;
		$this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
		$this->loadHelper('params');

		//get the podcast subscription
		$podcast         = new podcastSubscribe();
		$this->subscribe = $podcast->buildSubscribeTable($this->item->params->get('subscribeintro', 'Our Podcasts'));

		//Passage link to BibleGateway
		$plugin = JPluginHelper::getPlugin('content', 'scripturelinks');
		if ($plugin) {
			$plugin = JPluginHelper::getPlugin('content', 'scripturelinks');
			// Convert parameter fields to objects.
			$registry = new JRegistry;
			$registry->loadString($plugin->params);
			$st_params  = $registry;
			$version    = $st_params->get('bible_version');
			$windowopen = "window.open(this.href,this.target,'width=800,height=500,scrollbars=1');return false;";
		}

		//Added database queries from the default template - moved here instead
		$database = JFactory::getDBO();
		$query    = "SELECT id"
				. "\nFROM #__menu"
				. "\nWHERE link ='index.php?option=com_biblestudy&view=sermons' and published = 1";
		$database->setQuery($query);
		$menuid       = $database->loadResult();
		$this->menuid = $menuid;


		if ($this->getLayout() == 'pagebreak') {
			$this->_displayPagebreak($tpl);

			return null;
		}

		/*
         * Process the prepare content plugins
         */
		$article->text = $this->item->studytext;
		$linkit        = $this->item->params->get('show_scripture_link');
		if ($linkit) {
			switch ($linkit) {
				case 0:
					break;
				case 1:
					JPluginHelper::importPlugin('content');
					break;
				case 2:
					JPluginHelper::importPlugin('content', 'scripturelinks');
					break;
			}
			$limitstart            = $app->input->get('limitstart', 'int');
			$results               = $dispatcher->trigger('onContentPrepare', array(
				'com_biblestudy.sermon',
				& $article,
				& $this->item->params,
				$limitstart
			));
			$article->studytext    = $article->text;
			$this->item->studytext = $article->text;
		} //end if $linkit
		$Biblepassage  = new showScripture();
		$this->passage = $Biblepassage->buildPassage($this->item, $this->item->params);

		//Prepares a link string for use in social networking
		$u                 = JURI::getInstance();
		$detailslink       = htmlspecialchars($u->toString());
		$detailslink       = JRoute::_($detailslink);
		$this->detailslink = $detailslink;

		//End social networking
		JViewLegacy::loadHelper('share');

		$this->page         = new stdClass();
		$this->page->social = getShare($detailslink, $this->item, $this->item->params, $this->item->admin_params);
		JHTML::_('behavior.tooltip');

		// To add the icon class to JHTML
		JHTML::addIncludePath(JPATH_COMPONENT . '/helpers');
		$this->page->print = JHtml::_('icon.print_popup', $this->item, $this->item->params);

		// End process prepare content plugins
		$this->template = $template;
		$this->article  = $article;
		//$this->assignRef('passage_link', $passage_link);
		// Increment the hit counter of the article.
		if (!$this->params->get('intro_only') && $offset == 0) {
			$model = $this->getModel();
			$model->hit();
		}

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app                  = JFactory::getApplication();
		$menus                = $app->getMenu();
		$pathway              = $app->getPathway();
		$title                = null;
		$this->item->metadesc = $this->item->studyintro;
		$this->item->metakey  = $this->item->topics;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('JBS_CMN_MESSAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		$id = (int) @$menu->query['id'];
		// if the menu item does not concern this article
		if ($menu && ($menu->query['option'] != 'com_biblestudy' || $menu->query['view'] != 'sermon' || $id != $this->item->id)) {
			// If this is not a single article menu item, set the page title to the article title
			if ($this->item->studytitle) {
				$title = $this->item->studytitle;
			}
			$path = array(
				array(
					'studytitle' => $this->item->studytitle,
					'link'       => ''
				)
			);

			$path = array_reverse($path);
			foreach ($path as $item) {
				$pathway->addItem($item['studytitle'], $item['link']);
			}
		}

		// Check for empty title and add site name if param is set
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		} elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		} elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		if (empty($title)) {
			$title = $this->item->studytitle;
		}
		$this->document->setTitle($title);

		if ($this->item->params->get('metadesc')) {
			$this->document->setDescription($this->item->params->get('metadesc'));
		} elseif (!$this->item->params->get('metadesc')) {
			$this->document->setDescription($this->item->studyintro);
		}

		if ($this->item->metakey) {
			$this->document->setMetadata('keywords', $this->item->metakey);
		} elseif (!$this->item->metakey && $this->params->get('menu-meta_keywords')) {
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots')) {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}


		//Prepare meta information (under development)
		if ($this->item->params->get('metakey')) {
			$this->document->setMetadata('keywords', $this->item->params->get('metakey'));
		} elseif (!$this->item->params->get('metakey')) {
			$this->document->setMetadata('keywords', $this->item->topic_text . ',' . $this->item->studytitle);
		}
		if ($app->getCfg('MetaAuthor') == '1') {
			//$this->document->setMetaData('author', $this->item->author);
		}

		// If there is a pagebreak heading or title, add it to the page title
		if (!empty($this->item->page_title)) {
			$this->item->title = $this->item->title . ' - ' . $this->item->page_title;
			$this->document->setTitle($this->item->page_title . ' - ' . JText::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $this->state->get('list.offset') + 1));
		}

		if ($this->print) {
			$this->document->setMetaData('robots', 'noindex, nofollow');
		}
	}

	/**
	 * Display PageBrack
	 *
	 * @param string $tpl
	 */
	function _displayPagebreak($tpl)
	{
		$this->document->setTitle(JText::_('JBS_CMN_READ_MORE'));
		parent::display($tpl);
	}

}