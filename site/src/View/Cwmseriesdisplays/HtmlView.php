<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmseriesdisplays;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use CWM\Component\Proclaim\Site\Helper\Cwmpagebuilder;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
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
    /** @var object Admin Info
     *
     * @since 7.0
     */
    protected $admin;

    /** @var  \JObject Items
     *
     * @since 7.0
     */
    protected $items;

    /** @var  \JObject Template
     *
     * @since 7.0
     */
    protected $template;

    /** @var  \JObject Pagination
     *
     * @since 7.0
     */
    protected $pagination;

    /** @var  string Request Url
     *
     * @since 7.0
     */
    protected $request_url;

    /** @var  Registry Params
     *
     * @since 7.0
     */
    protected $params;

    /** @var  string Page
     *
     * @since 7.0
     */
    protected $page;

    /** @var Registry State
     *
     * @since 7.0
     */
    protected $state;

    /** @var string State
     *
     * @since 7.0
     */
    protected $go;

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
                $item->image = '<img src="' . $seriesimage->path . '" height="' . $seriesimage->height . '" width="'
                    . $seriesimage->width . '" alt="" />';
            }

            $item->serieslink = Route::_(
                'index.php?option=com_proclaim&view=cwmseriesdisplay&id=' . $item->slug . '&t=' . $this->template->id
            );
            $teacherimage     = Cwmimages::getTeacherImage($item->thumb);

            if ($teacherimage->path) {
                $item->teacherimage = '<img src="' . $teacherimage->path . '" height="' . $teacherimage->height .
                    '" width="' . $teacherimage->width . '" alt="" />';
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

        $this->updateFilters();

        parent::display($tpl);
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
                $this->activeFilters[] = $filter;
            }

            // Remove from view if set to hid in template.
            if ((int)$this->params->get('show_' . $filter . '_search', 1) === 0 && $filter !== 'language') {
                $this->filterForm->removeField($filter, 'filter');
            }
        }

        foreach ($lists as $list) {
            // Remove from view if set to hid in template.
            if ((int)$this->params->get('show_' . $list . '_search', 1) === 0) {
                $this->filterForm->removeField($list, 'list');
            }
        }
    }
}
