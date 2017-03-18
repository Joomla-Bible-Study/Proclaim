<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
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
		$this->state      = $this->get('State');
		$this->template   = $this->state->get('template');
		$items            = $this->get('Items');

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

		$filter_topic       = $this->state->get('filter.topic');
		$filter_book        = $this->state->get('filter.book');
		$filter_teacher     = $this->state->get('filter.teacher');
		$filter_series      = $this->state->get('filter.series');
		$filter_messagetype = $this->state->get('filter.messageType');
		$filter_year        = $this->state->get('filter.year');
		$filter_location    = $this->state->get('filter.location');
		$filter_orders      = $this->state->get('filter.orders');
		$filter_languages   = $this->state->get('filter.languages');

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

		if ($params->get('show_popular') > 0)
		{
			$dropdowns[] = array('order' => $params->get('ddpopular'), 'item' => $this->page->popular);
		}

		// Get whether "Go" Button is used then turn off onchange if it is
		if ($params->get('use_go_button', 0) == 0)
		{
			$go = 'onchange="this.form.submit()"';
		}
		else
		{
			$go = null;
		}
		// Build go button
		$this->page->gobutton = '<input class="btn btn-primary pull-right" type="submit" value="' . JText::_('JBS_STY_GO_BUTTON') . '">';

		if ($params->get('use_go_button') > 0)
		{
			$dropdowns[] = array('order' => $params->get('ddgobutton'), 'item' => $this->page->gobutton);
		}

		// Build language drop down
		$used = JLanguageHelper::getLanguages();
		$lang = array();

		foreach ($used as $use)
		{
			$langtemp = array(
				'text'  => $use->title_native,
				'value' => $use->lang_code
			);
			$lang[]   = $langtemp;
		}

		$langdropdown[]        = JHtml::_('select.option', '0', JText::_('JOPTION_SELECT_LANGUAGE'));
		$langdropdown          = array_merge($langdropdown, $lang);
		$this->page->languages = JHtml::_('select.genericlist', $langdropdown, 'filter_languages', 'class="inputbox"  '
			. $go, 'value', 'text', "$filter_languages"
		);

		if ($params->get('listlanguage') == 1)
		{
			$dropdowns[] = array('order' => $params->get('ddlanguage'), 'item' => $this->page->languages);
		}

		// Build the teacher dropdown
		$types[]              = JHtml::_('select.option', '0', JText::_('JBS_CMN_SELECT_TEACHER'));
		$types                = array_merge($types, $this->teachers);
		$this->page->teachers = JHtml::_('select.genericlist', $types, 'filter_teacher', 'class="inputbox"  '
			. $go, 'value', 'text', "$filter_teacher"
		);

		if (($params->get('show_teacher_search') > 0 && ($teacher_menu == -1)) || $params->get('show_teacher_search') > 1)
		{
			$dropdowns[] = array('order' => $params->get('ddteachers'), 'item' => $this->page->teachers);
		}

		// Build Series List for drop down menu
		$types3[]           = JHtml::_('select.option', '0', JText::_('JBS_CMN_SELECT_SERIES'));
		$types3             = array_merge($types3, $this->series);
		$this->page->series = JHtml::_('select.genericlist', $types3, 'filter_series', 'class="inputbox"  '
			. $go, 'value', 'text', "$filter_series"
		);

		if (($params->get('show_series_search') > 0 && ($series_menu == -1)) || $params->get('show_series_search') > 1)
		{
			$dropdowns[] = array('order' => $params->get('ddseries'), 'item' => $this->page->series);
		}

		// Build message types
		$types4[]                 = JHtml::_('select.option', '0', JText::_('JBS_CMN_SELECT_MESSAGETYPE'));
		$types4                   = array_merge($types4, $this->messageTypes);
		$this->page->messagetypes = JHtml::_('select.genericlist', $types4, 'filter_messagetype', 'class="inputbox"  '
			. $go, 'value', 'text', "$filter_messagetype"
		);

		if (($params->get('show_type_search') > 0 && ($messagetype_menu == -1)) || $params->get('show_type_search') > 1)
		{
			$dropdowns[] = array('order' => $params->get('ddmessagetype'), 'item' => $this->page->messagetypes);
		}
		// Build study years
		$years[]           = JHtml::_('select.option', '0', JText::_('JBS_CMN_SELECT_YEAR'));
		$years             = array_merge($years, $this->years);
		$this->page->years = JHtml::_('select.genericlist', $years, 'filter_year', 'class="inputbox"  ' . $go, 'value', 'text', "$filter_year");

		if ($params->get('show_year_search') > 0)
		{
			$dropdowns[] = array('order' => $params->get('ddyears'), 'item' => $this->page->years);
		}
		// Build locations
		$loc[]                 = JHtml::_('select.option', '0', JText::_('JBS_CMN_SELECT_LOCATION'));
		$loc                   = array_merge($loc, $this->locations);
		$this->page->locations = JHtml::_(
			'select.genericlist', $loc, 'filter_location', 'class="inputbox" size="1" '
			. $go, 'value', 'text', "$filter_location"
		);

		if (($params->get('show_locations_search') > 0 && ($location_menu == -1)) || $params->get('show_locations_search') > 1)
		{
			$dropdowns[] = array('order' => $params->get('ddlocations'), 'item' => $this->page->locations);
		}
		// Build Topics
		$top[] = JHtml::_('select.option', '0', JText::_('JBS_CMN_SELECT_TOPIC'));

		if ($top && $this->topics)
		{
			$top = array_merge($top, $this->topics);
		}

		$this->page->topics = JHtml::_('select.genericlist', $top, 'filter_topic', 'class="inputbox" ' . $go, 'value', 'text', "$filter_topic");

		if (($params->get('show_topic_search') > 0 && ($topic_menu == -1)) || $params->get('show_topic_search') > 1)
		{
			$dropdowns[] = array('order' => $params->get('ddtopics'), 'item' => $this->page->topics);
		}
		// Build Books
		$boo[]             = JHtml::_('select.option', '0', JText::_('JBS_CMN_SELECT_BOOK'));
		$boo               = array_merge($boo, $this->books);
		$this->page->books = JHtml::_('select.genericlist', $boo, 'filter_book', 'class="inputbox"  ' . $go, 'value', 'text', "$filter_book");

		if (($params->get('show_book_search') > 0 && $book_menu == -1) || $params->get('show_book_search') > 1)
		{
			$dropdowns[] = array('order' => $params->get('ddbooks'), 'item' => $this->page->books);
		}

		// Build order
		$ordervalues       = array(
			array(
				'value' => "DESC",
				'text'  => JText::_("JBS_CMN_DESCENDING")
			),
			array(
				'value' => "ASC",
				'text'  => JText::_("JBS_CMN_ASCENDING")
			)
		);
		$ord[]             = JHtml::_('select.option', '0', JText::_('JBS_CMN_SELECT_ORDER'));
		$ord               = array_merge($ord, $ordervalues);
		$this->page->order = JHtml::_('select.genericlist', $ord, 'filter_orders', 'class="inputbox" size="1" ' . $go, 'value', 'text', "$filter_orders");

		if ($params->get('show_order_search') > 0)
		{
			$dropdowns[] = array('order' => $params->get('ddorder'), 'item' => $this->page->order);
		}

		if ($params->get('show_pagination') == 1)
		{
			$this->page->limits = '<span class="display-limit">' . JText::_('JGLOBAL_DISPLAY_NUM') . $this->pagination->getLimitBox() . '</span>';
			$dropdowns[]        = array('order' => '0', 'item' => $this->page->limits);
		}

		JBSMBibleStudyHelper::array_sort_by_column($dropdowns, 'order');

		foreach ($dropdowns as $dmenus)
		{
			$this->page->dropdowns .= $dmenus['item'];
		}

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
