<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmsermons;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmschemaorgHelper;
use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Site\Helper\Cwmpagebuilder;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcastsubscribe;
use CWM\Component\Proclaim\Site\Helper\Cwmteacher;
use CWM\Component\Proclaim\Site\Helper\UpdateFiltersTrait;
use CWM\Component\Proclaim\Site\Model\CwmsermonsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
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
    use UpdateFiltersTrait;

    /**
     * Form object for search filters
     *
     * @var  Form|null
     * @since 9.1.4
     */
    public ?Form $filterForm = null;

    /**
     * The active search filters
     *
     * @var  array|null
     * @since 9.1.4
     */
    public ?array $activeFilters = null;

    /** @var array|null
     *
     * @since 7.0
     */
    protected ?array $items = null;

    /** @var object|null
     *
     * @since 7.0
     */
    protected ?object $pagination = null;

    /** @var Registry|null
     *
     * @since 7.0
     */
    protected ?Registry $state = null;

    /**
     * @var object|null
     *
     * @since 7.0
     */
    protected ?object $admin = null;

    /** @var Registry|null
     *
     * @since 7.0
     */
    protected ?Registry $params = null;

    /** @var string|null
     *
     * @since 7.0
     */
    protected ?string $subscribe = null;

    /** @var object|null
     *
     * @since 7.0
     */
    protected ?object $template = null;

    /** @var object|null
     *
     * @since 7.0
     */
    protected ?object $main = null;

    /** @var object|null
     *
     * @since 7.0
     */
    protected ?object $page = null;

    /**
     * Main image HTML string
     *
     * @var string
     * @since 10.0.0
     */
    public string $mainimage = '';

    /**
     * Listing helper instance for template use
     *
     * @var Cwmlisting|null
     * @since 10.0.0
     */
    public ?Cwmlisting $listing = null;

    /**
     * Studies element CSS class
     *
     * @var string
     * @since 10.0.0
     */
    public string $classelement = '';

    /**
     * Pre-calculated teacher data for fluid display
     *
     * @var array
     * @since 10.0.0
     */
    public array $teachersFluid = [];

    /**
     * Current menu item ID
     *
     * @var int
     * @since 10.0.0
     */
    public int $itemid = 0;

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
    #[\Override]
    public function display($tpl = null): void
    {
        /** @var CwmsermonsModel $model */
        $model       = $this->getModel();
        $this->state = $model->getState();

        $this->template = $this->state->get('template');

        $items                 = $model->getItems();
        $pagination            = $model->getPagination();
        $this->page            = new \stdClass();
        $this->page->pagelinks = $pagination->getPagesLinks();
        $this->page->counter   = $pagination->getPagesCounter();
        $this->activeFilters   = $model->getActiveFilters();

        $this->filterForm = $model->getFilterForm();
        $mainframe        = Factory::getApplication();
        $this->admin      = $this->state->get('administrator');

        $params = $this->state->params;

        $user            = $mainframe->getIdentity();
        $groups          = $user->getAuthorisedViewLevels();
        $this->main      = Cwmimages::mainStudyImage($params);
        $this->mainimage = Cwmimages::renderPicture(
            $this->main,
            Text::_('JBS_CMN_MESSAGES_LIST'),
            'proclaim-page-header-img',
            false
        );

        // Only load PageBuilder if the default template is NOT being used
        if (
            $params->get('useexpert_list') > 0
            || ($params->get('simple_mode') === '1')
            || (\is_string($params->get('sermonstemplate')) === true && $params->get('sermonstemplate') !== '0')
        ) {
            $page_builder = new Cwmpagebuilder();

            foreach ($items as $item) {
                if ($item->access > 1 && !\in_array($item->access, $groups, true)) {
                    continue;
                }

                $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
            }

            $page_builder->enrichStudies($items, $params, $this->template);
        }

        // Get the podcast subscription
        $wa = $mainframe->getDocument()->getWebAssetManager();
        $wa->useStyle('com_proclaim.podcast');
        $podcast         = new Cwmpodcastsubscribe();
        $this->subscribe = $podcast->buildSubscribeTable($params->get('subscribeintro', 'Our Podcasts'));

        $this->pagination = $pagination;
        $this->items      = $items;
        $this->params     = $params;

        // Pre-calculate values for templates to avoid helper instantiation in templates
        $this->listing      = new Cwmlisting();
        $this->classelement = $this->listing->createelement($params->get('studies_element'));
        $this->itemid       = (int) $mainframe->input->get('Itemid', 0);

        // Pre-calculate teacher data for default_main template
        $cwmTeacher          = new Cwmteacher();
        $this->teachersFluid = $cwmTeacher->getTeachersFluid($params);

        // AJAX filtering — only for the default_main template (not custom/simple)
        $isDefaultTemplate = !(
            $params->get('useexpert_list') > 0
            || $params->get('simple_mode') === '1'
            || (\is_string($params->get('sermonstemplate')) && $params->get('sermonstemplate') !== '0')
        );

        if ($isDefaultTemplate) {
            $t      = $this->template->id ?? $mainframe->input->getInt('t', 1);
            $itemId = $this->itemid;

            $ajaxUrl = Uri::base() . 'index.php?option=com_proclaim&task=cwmsermons.filterAjax&format=raw'
                . '&t=' . (int) $t
                . '&Itemid=' . (int) $itemId;

            $mainframe->getDocument()->addScriptOptions('com_proclaim.sermonFilters', [
                'ajaxUrl'         => $ajaxUrl,
                'enabled'         => true,
                'csrfToken'       => Session::getFormToken(),
                'paginationStyle' => $params->get('pagination_style', 'pagination'),
                'limit'           => (int) $this->state->get('list.limit', 20),
                'totalItems'      => (int) $pagination->total,
                'scrollThreshold' => (int) $params->get('infinite_scroll_threshold', 3),
            ]);

            $wa->useScript('com_proclaim.sermon-filters');
            $wa->useStyle('com_proclaim.sermon-filters-css');
        }

        // Load scripture tooltip assets (per-element controlled; JS is a no-op
        // if no elements have show_tooltip enabled)
        $wa->useScript('com_proclaim.scripture-tooltip');
        $wa->useStyle('com_proclaim.scripture-tooltip-css');

        $mainframe->getDocument()->addScriptOptions('com_proclaim.scripture', [
            'ajaxUrl' => Route::_(
                'index.php?option=com_proclaim&task=cwmscripture.getPassageXHR&format=raw',
                false
            ),
        ]);

        // Register language strings used by infinite scroll / load more JS
        Text::script('JBS_CMN_LOAD_MORE');
        Text::script('JBS_CMN_LOADING');
        Text::script('JBS_CMN_SHOWING_X_OF_Y');
        Text::script('JBS_CMN_ALL_ITEMS_LOADED');

        // Register language strings used by scripture-switcher JS
        Text::script('JBS_CMN_SCRIPTURE_UNAVAILABLE');
        Text::script('JBS_CMN_SCRIPTURE_RETRY');
        Text::script('JBS_CMN_SCRIPTURE_FALLBACK');
        Text::script('JBS_CMN_SCRIPTURE_SERVICE_BUSY');

        $this->updateFilters();

        $this->prepareDocument();

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
    protected function prepareDocument(): void
    {
        $app   = Factory::getApplication();
        $menus = $app->getMenu();

        // Because the application sets a default page title,
        // We need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('JBS_CMN_MESSAGES_LIST'));
        }

        $title = $this->params->def('page_title', '');

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) === 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) === 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        $pathway = $app->getPathway();
        $pathway->addItem($title, '');

        // Prepare meta information (under development)
        if ($this->params->get('metakey')) {
            $this->document->setMetaData('keywords', $this->params->get('metakey'));
        }

        if ($this->params->get('metadesc')) {
            $this->document->setDescription($this->params->get('metadesc'));
        }

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }

        // Schema.org structured data
        CwmschemaorgHelper::inject(
            CwmschemaorgHelper::buildSermonList(
                $this->items ?? [],
                Uri::getInstance()->toString(),
                $app->get('sitename')
            )
        );
    }
}
