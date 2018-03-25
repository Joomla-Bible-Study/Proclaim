<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
/**
 * View for Sermons class
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewSermons extends JViewLegacy
{
	/** @var object
	 *
	 * @since 7.0 */
	public $document;

	/** @var object
	 *
	 * @since 7.0 */
	protected $items;

	/** @var object
	 *
	 * @since 7.0 */
	protected $pagination;

	/** @var Registry
	 *
	 * @since 7.0 */
	protected $state;

	/** @var string
	 *
	 * @since 7.0 */
	protected $pagelinks;

	/** @var string
	 *
	 * @since 7.0 */
	protected $limitbox;

	/** @var JObject
	 *
	 * @since 7.0 */
	protected $admin;

	/** @var Registry
	 *
	 * @since 7.0 */
	protected $params;

	/** @var object
	 *
	 * @since 7.0 */
	protected $study;

	/** @var string
	 *
	 * @since 7.0 */
	protected $subscribe;

	/** @var string
	 *
	 * @since 7.0 */
	protected $series;

	/** @var string
	 *
	 * @since 7.0 */
	protected $teachers;

	/** @var string
	 *
	 * @since 7.0 */
	protected $messageTypes;

	/** @var string
	 *
	 * @since 7.0 */
	protected $years;

	/** @var string
	 *
	 * @since 7.0 */
	protected $locations;

	/** @var string
	 *
	 * @since 7.0 */
	protected $topics;

	/** @var string
	 *
	 * @since 7.0 */
	protected $orders;

	/** @var string
	 *
	 * @since 7.0 */
	protected $books;

	/** @var object
	 *
	 * @since 7.0 */
	protected $template;

	/** @var string
	 *
	 * @since 7.0 */
	protected $order;

	/** @var array
	 *
	 * @since 7.0 */
	protected $topic;

	/** @var object
	 *
	 * @since 7.0 */
	protected $main;

	/** @var object
	 *
	 * @since 7.0 */
	protected $page;

	/** @var string
	 *
	 * @since 7.0 */
	protected $request_url;

	/** @var int
	 *
	 * @since 7.0 */
	protected $limitstart;

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
		$input      = new JInput;
		$limitstart = $input->get('limitstart', '', 'int');
		$input->set('start', $limitstart);
		$this->state         = $this->get('State');
		$this->template      = $this->state->get('template');
		$items               = $this->get('Items');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$this->limitstart = $input->get('start', '', 'int');
		$pagination       = $this->get('Pagination');
		$pagelinks        = $pagination->getPagesLinks();

		if ($pagelinks !== '')
		{
			$this->pagelinks = $pagelinks;
		}

		$this->limitbox   = '<span class="display-limit">' . JText::_('JGLOBAL_DISPLAY_NUM') . $pagination->getLimitBox() . '</span>';
		$this->pagination = $pagination;
		$this->admin      = $this->state->get('admin');

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		/** @var  $params Registry */
		$params = $this->state->params;

		$images     = new JBSMImages;
		$this->main = $images->mainStudyImage();

		// Only load PageBuilder if the default template is NOT being used
		if ($params->get('useexpert_list') > 0 || (is_string($params->get('sermonstemplate')) == true && $params->get('sermonstemplate') != '0'))
		{
			$page_builder = new JBSMPageBuilder;

			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				$item = &$items[$i];

				if ($item->access > 1 && !in_array($item->access, $groups))
				{
					unset($item);
				}
				else
				{
					$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

					$pelements        = $page_builder->buildPage($item, $params, $this->template);
					$item->scripture1 = $pelements->scripture1;
					$item->scripture2 = $pelements->scripture2;
					$item->media      = $pelements->media;
					$item->duration   = $pelements->duration;
					$item->studydate  = $pelements->studydate;
					$item->topics     = $pelements->topics;

					if (isset($pelements->study_thumbnail))
					{
						$item->study_thumbnail = $pelements->study_thumbnail;
					}
					else
					{
						$item->study_thumbnail = null;
					}

					if (isset($pelements->series_thumbnail))
					{
						$item->series_thumbnail = $pelements->series_thumbnail;
					}
					else
					{
						$item->series_thumbnail = null;
					}

					$item->detailslink = $pelements->detailslink;

					if (!isset($item->studyintro))
					{
						$item->studyintro = '';
					}

					if (isset($pelements->secondary_reference))
					{
						$item->secondary_reference = $pelements->secondary_reference;
					}
					else
					{
						$item->secondary_reference = '';
					}

					if (isset($pelements->sdescription))
					{
						$item->sdescription = $pelements->sdescription;
					}
					else
					{
						$item->sdescription = '';
					}
				}
			}
		}
		// Get the podcast subscription
		JHtml::stylesheet('media/css/podcast.css');
		$podcast         = new JBSMPodcastSubscribe;
		$this->subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));

		$uri = new JUri;

		$this->teachers     = $this->get('Teachers');
		$this->series       = $this->get('Series');
		$this->messageTypes = $this->get('MessageTypes');
		$this->years        = $this->get('Years');
		$this->locations    = $this->get('Locations');
		$this->topics       = $this->get('Topics');
		$this->orders       = $this->get('Orders');
		$this->books        = $this->get('Books');

		// End scripture helper
		// Get the data for the drop down boxes

		$this->pagination = $pagination;
		$this->order      = $this->orders;
		$this->topic      = $this->topics;

		// Get the template options for showing the dropdowns
		$teacher_menu1     = $params->get('mteacher_id');
		$teacher_menu      = $teacher_menu1[0];
		$topic_menu1       = $params->get('mtopic_id');
		$topic_menu        = $topic_menu1[0];
		$book_menu1        = $params->get('mbooknumber');
		$book_menu         = $book_menu1[0];
		$location_menu1    = $params->get('mlocations');
		$location_menu     = $location_menu1[0];
		$series_menu1      = $params->get('mseries_id');
		$series_menu       = $series_menu1[0];
		$messagetype_menu1 = $params->get('mmessagetype');
		$messagetype_menu  = $messagetype_menu1[0];

		// Initialize the page
		$this->page            = new stdClass;
		$this->page->dropdowns = '';

		// Build drop down menus for search filters
		$dropdowns = array();

		// Get the Popular stats
		$stats               = new JBSMStats;
		$this->page->popular = $stats->top_score_site();

		$this->items       = $items;
		$stringuri         = $uri->toString();
		$this->request_url = $stringuri;
		$this->params      = $params;

		$this->_prepareDocument();

		// Get the drop down menus

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @since 7.0
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
			$this->params->def('page_heading', JText::_('JBS_CMN_MESSAGES_LIST'));
		}

		$title = $this->params->def('page_title', '');

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

		$pathway = $app->getPathway();
		$pathway->addItem($title, '');

		// Prepare meta information (under development)
		if ($this->params->get('metakey'))
		{
			$this->document->setMetadata('keywords', $this->params->get('metakey'));
		}

		if ($this->params->get('metadesc'))
		{
			$this->document->setDescription($this->params->get('metadesc'));
		}

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

		JHtml::_('biblestudy.framework');
		JHtml::_('biblestudy.loadCss', $this->params, null, 'font-awesome');
	}
}
