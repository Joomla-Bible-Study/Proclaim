<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmsermons;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use CWM\Component\Proclaim\Site\Helper\Cwmpagebuilder;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcastsubscribe;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * View for Sermons class
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
	/** @var object
	 *
	 * @since 7.0
	 */
	public $document;

	/** @var object
	 *
	 * @since 7.0
	 */
	protected $items = null;

	/** @var object
	 *
	 * @since 7.0
	 */
	protected $pagination = null;

	/** @var Registry
	 *
	 * @since 7.0
	 */
	protected $state = null;

	/**
	 * @var object
	 *
	 * @since 7.0
	 */
	protected $admin;

	/** @var Registry
	 *
	 * @since 7.0
	 */
	protected $params;

	/** @var object
	 *
	 * @since 7.0
	 */
	protected $study;

	/** @var string
	 *
	 * @since 7.0
	 */
	protected $subscribe;

	/** @var string
	 *
	 * @since 7.0
	 */
	protected $series;

	/** @var string
	 *
	 * @since 7.0
	 */
	protected $teachers;

	/** @var string
	 *
	 * @since 7.0
	 */
	protected $messageTypes;

	/** @var string
	 *
	 * @since 7.0
	 */
	protected $years;

	/** @var string
	 *
	 * @since 7.0
	 */
	protected $locations;

	/** @var string
	 *
	 * @since 7.0
	 */
	protected $topics;

	/** @var string
	 *
	 * @since 7.0
	 */
	protected $orders;

	/** @var string
	 *
	 * @since 7.0
	 */
	protected $books;

	/** @var object
	 *
	 * @since 7.0
	 */
	protected $template;

	/** @var array
	 *
	 * @since 7.0
	 */
	protected $topic;

	/** @var object
	 *
	 * @since 7.0
	 */
	protected $main;

	/** @var object
	 *
	 * @since 7.0
	 */
	protected $page;

	/** @var string
	 *
	 * @since 7.0
	 */
	protected $request_url;

	/**
	 * Form object for search filters
	 *
	 * @var  Form
	 * @since 9.1.4
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var  array
	 * @since 9.1.4
	 */
	public $activeFilters;

	/**
	 * The sidebar markup
	 *
	 * @var  string
	 * @since 9.1.4
	 */
	protected $sidebar;

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
		$this->state           = $this->get('State');

		$this->template        = $this->state->get('template');

		$items                 = $this->get('Items');
		$pagination            = $this->get('Pagination');
		$this->page            = new \stdClass;
		$this->page->pagelinks = $pagination->getPagesLinks();
		$this->page->counter   = $pagination->getPagesCounter();
		$this->activeFilters   = $this->get('ActiveFilters');

		// Get filter form.
		$this->filterForm = $this->get('FilterForm');
		$mainframe        = Factory::getApplication();
		$this->admin      = $this->state->get('administrator');

		$params              = $this->state->params;

		/**
		 * @todo not ready for live and could be slowing down site.
		// Searched all code and not seeing it used in core.
		// $this->page->popular = (new Cwmstats)->top_score_site();
		 */

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user            = $mainframe->getIdentity();
		$groups          = $user->getAuthorisedViewLevels();
		$this->main      = Cwmimages::mainStudyImage($params);
		$this->mainimage = '<img src="' . $this->main->path . '" width="' . $this->main->width . '" height="' . $this->main->height . '">';

		// Build go button
		$this->page->gobutton = '<input class="btn btn-primary" type="submit" value="' . Text::_('JBS_STY_GO_BUTTON') . '">';

		// Only load PageBuilder if the default template is NOT being used
		if ($params->get('useexpert_list') > 0
			|| ($params->get('simple_mode') === '1')
			|| (is_string($params->get('sermonstemplate')) === true && $params->get('sermonstemplate') !== '0'))
		{
			$page_builder = new Cwmpagebuilder;

			foreach ($items as $iValue)
			{
				$item = &$iValue;

				if ($item->access > 1 && !in_array($item->access, $groups, true))
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
		$wa = $mainframe->getDocument()->getWebAssetManager();
		$wa->useStyle('com_proclaim.podcast');
		$podcast         = new Cwmpodcastsubscribe;
		$this->subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));

		$uri = new Uri;

		$this->pagination  = &$pagination;
		$this->limitbox    = $this->pagination->getLimitBox();
		$this->items       = &$items;
		$stringuri         = $uri->toString();
		$this->request_url = $stringuri;
		$this->params      = &$params;

		$this->updateFilters();

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	protected function _prepareDocument(): void
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('JBS_CMN_MESSAGES_LIST'));
		}

		$title = $this->params->def('page_title', '');

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

	}

	/**
	 * Update Filters per landing page call and Hide filters per the template settings.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since 9.1.6
	 */
	private function updateFilters(): void
	{
		$input   = Factory::getApplication()->input;
		$filters = ['search', 'book', 'teacher', 'series', 'messagetype', 'year', 'topic', 'location', 'language'];
		$lists   = ['fullordering', 'limit'];

		// Fix language filter
		$lang = $this->params->get('listlanguage', 'NO');

		if ($lang !== 'NO')
		{
			$this->params->set('show_language_search', (int) $lang);
		}

		foreach ($filters as $filter)
		{
			$set  = $input->getInt('filter_' . $filter);
			$from = $this->filterForm->getValue($filter, 'filter');

			// Update value from landing page call.
			if ($set !== 0 && $set !== null)
			{
				$this->filterForm->setValue($filter, 'filter', $set);
			}

			// Catch active filters and update them.
			if ($from !== null || $set !== null)
			{
				$this->activeFilters[] = $filter;
			}

			// Remove from view if set to hid in template.
			if ((int) $this->params->get('show_' . $filter . '_search', 1) === 0 && $filter !== 'language')
			{
				$this->filterForm->removeField($filter, 'filter');
			}
		}

		foreach ($lists as $list)
		{
			// Remove from view if set to hid in template.
			if ((int) $this->params->get('show_' . $list . '_search', 1) === 0)
			{
				$this->filterForm->removeField($list, 'list');
			}
		}
	}
}
