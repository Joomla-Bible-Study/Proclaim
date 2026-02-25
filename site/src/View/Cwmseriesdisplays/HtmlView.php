<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmseriesdisplays;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Site\Helper\Cwmpagebuilder;
use CWM\Component\Proclaim\Site\Helper\Cwmserieslist;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * View class for SeriesDisplays
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /** @var object|null Admin Info
     *
     * @since 7.0
     */
    protected ?object $admin = null;

    /** @var  array|null Items
     *
     * @since 7.0
     */
    protected ?array $items = null;

    /** @var  object|null Template
     *
     * @since 7.0
     */
    protected ?object $template = null;

    /** @var Pagination|null  Pagination
     *
     * @since 7.0
     */
    protected ?Pagination $pagination = null;

    /** @var  string Request Url
     *
     * @since 7.0
     */
    protected string $request_url = '';

    /** @var  Registry|null Params
     *
     * @since 7.0
     */
    protected ?Registry $params = null;

    /** @var  \stdClass|null Page
     *
     * @since 7.0
     */
    protected ?\stdClass $page = null;

    /** @var Registry|null State
     *
     * @since 7.0
     */
    protected ?Registry $state = null;

    /** @var string State
     *
     * @since 7.0
     */
    protected string $go = '';

    /**
     * Filter form
     *
     * @var Form|null
     * @since 9.1.4
     */
    public Form|null $filterForm;

    /**
     * Active filters array
     *
     * @var array|null
     * @since 10.0.0
     */
    public ?array $activeFilters = null;

    /**
     * Listing helper instance for template use
     *
     * @var Cwmlisting|null
     * @since 10.0.0
     */
    public ?Cwmlisting $listing = null;

    /**
     * Series element CSS class
     *
     * @var string
     * @since 10.0.0
     */
    public string $classelement = '';

    /**
     * Series list helper for menu
     *
     * @var Cwmserieslist|null
     * @since 10.0.0
     */
    public ?Cwmserieslist $serieslist = null;

    /**
     * Series menu ID
     *
     * @var int
     * @since 10.0.0
     */
    public int $seriesMenu = 1;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void  A string if successful, otherwise a JError object.
     *
     * @throws \Exception
     * @since   11.1
     * @see     fetch()
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $this->state = $this->get('state');

        /** @var  $params Registry */
        $params         = $this->state->template->params;
        $this->params   = $params;
        $this->template = $this->state->get('template');

        $uri                   = new Uri();
        $pagebuilder           = new Cwmpagebuilder();
        $items                 = $this->get('Items');
        $this->activeFilters   = $this->get('ActiveFilters');

        // Get a filter form.
        $this->filterForm = $this->get('FilterForm');
        // Adjust the slug if there is no alias in the row
        foreach ($items as $item) {
            $item->slug  = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':'
                . str_replace(' ', '-', htmlspecialchars_decode($item->series_text, ENT_QUOTES));
            $seriesimage = Cwmimages::getSeriesThumbnail($item->series_thumbnail);

            if ($seriesimage->path) {
                $item->image = Cwmimages::renderPicture($seriesimage, $item->series_text ?? '');
            }

            $item->serieslink = Route::_(
                'index.php?option=com_proclaim&view=cwmseriesdisplay&id=' . $item->slug . '&t=' . $this->template->id
            );
            $teacherimage     = Cwmimages::getTeacherImage($item->thumb ?? '');

            if ($teacherimage->path) {
                $item->teacherimage = Cwmimages::renderPicture($teacherimage, $item->teachername ?? '');
            }

            if (isset($item->description)) {
                $item->text        = $item->description;
                $description       = $pagebuilder->runContentPlugins($item, $params);
                $item->description = $description->text;
            }
        }

        $this->items           = $items;
        $pagination            = $this->get('Pagination');
        $this->page            = new \stdClass();
        $this->page->pagelinks = $pagination->getPagesLinks();
        $this->page->counter   = $pagination->getPagesCounter();

        // End scripture helper
        $this->pagination = $pagination;

        // Get the main study list image
        $mainimage = Cwmimages::mainStudyImage();

        if ($mainimage->path) {
            $this->page->main = '<img src="' . $mainimage->path . '" height="' . $mainimage->height . '" width="' . $mainimage->width . '" alt="" />';
        }

        if ($params->get('series_list_show_pagination') === '1') {
            $this->page->limits = '<span class="display-limit">' . Text::_(
                'JGLOBAL_DISPLAY_NUM'
            ) . $this->pagination->getLimitBox() . '</span>';
        }

        $uri_tostring = $uri->toString();

        // $this->lists = $lists;
        $this->request_url = $uri_tostring;

        // Pre-create helpers for template use
        $this->listing      = new Cwmlisting();
        $this->classelement = $this->listing->createelement($params->get('series_element'));
        $this->serieslist   = new Cwmserieslist();
        $this->seriesMenu   = (int) $params->get('series_id', 1);

        // Infinite scroll / Load More for series listing
        $seriesPaginationStyle = $params->get('series_pagination_style', 'pagination');

        if ($seriesPaginationStyle !== 'pagination') {
            $app = Factory::getApplication();
            $wa  = $app->getDocument()->getWebAssetManager();

            $t      = $this->template->id ?? $app->getInput()->getInt('t', 1);
            $itemId = (int) $app->getInput()->get('Itemid', 0);

            $ajaxUrl = Uri::base() . 'index.php?option=com_proclaim&task=cwmseriesdisplays.paginateAjax&format=raw'
                . '&t=' . (int) $t
                . '&Itemid=' . (int) $itemId;

            $app->getDocument()->addScriptOptions('com_proclaim.seriesScroll', [
                'ajaxUrl'         => $ajaxUrl,
                'enabled'         => true,
                'csrfToken'       => Session::getFormToken(),
                'paginationStyle' => $seriesPaginationStyle,
                'limit'           => (int) $this->pagination->limit,
                'totalItems'      => (int) $pagination->total,
                'scrollThreshold' => (int) $params->get('series_infinite_scroll_threshold', 3),
            ]);

            $wa->useScript('com_proclaim.series-scroll');
            $wa->useStyle('com_proclaim.sermon-filters-css');

            // Register language strings for JS
            Text::script('JBS_CMN_LOAD_MORE');
            Text::script('JBS_CMN_LOADING');
            Text::script('JBS_CMN_SHOWING_X_OF_Y');
            Text::script('JBS_CMN_ALL_ITEMS_LOADED');
        }

        $this->updateFilters();

        parent::display($tpl);
    }

    /**
     * Update Filters per landing page call and hide filters per the template settings.
     *
     * @return  void
     *
     * @throws \Exception
     * @since 9.1.6
     */
    private function updateFilters(): void
    {
        $input   = Factory::getApplication()->getInput();
        $filters = ['search', 'book', 'teacher', 'series', 'messagetype', 'year', 'topic', 'location', 'language'];
        $lists   = ['fullordering', 'limit'];

        // Fix language filter
        $lang = $this->params->get('listlanguage', 'NO');

        if ($lang !== 'NO') {
            $this->params->set('show_language_search', (int)$lang);
        }

        foreach ($filters as $filter) {
            $set  = $input->getInt('filter_' . $filter);
            $from = $this->filterForm->getValue($filter, 'filter');

            // Update value from landing page call.
            if ($set !== 0 && $set !== null) {
                $this->filterForm->setValue($filter, 'filter', $set);
            }

            // Catch active filters and update them.
            if ($from !== null || $set !== null) {
                $this->activeFilters = $filter;
            }

            // Remove from view if set to 'hide' in the' template.
            if ((int)$this->params->get('show_' . $filter . '_search', 1) === 0 && $filter !== 'language') {
                $this->filterForm->removeField($filter, 'filter');
            }
        }

        foreach ($lists as $list) {
            // Remove from view if set to 'hide' in the' template.
            if ((int)$this->params->get('show_' . $list . '_search', 1) === 0) {
                $this->filterForm->removeField($list, 'list');
            }
        }
    }
}
