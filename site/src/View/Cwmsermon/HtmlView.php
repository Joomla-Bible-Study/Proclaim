<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmsermon;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmanalyticsHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmschemaorgHelper;
use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Site\Helper\Cwmpagebuilder;
use CWM\Component\Proclaim\Site\Helper\Cwmpodcastsubscribe;
use CWM\Component\Proclaim\Site\Helper\Cwmrelatedstudies;
use CWM\Component\Proclaim\Site\Helper\Cwmshowscripture;
use CWM\Component\Proclaim\Site\Model\CwmsermonModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * View class for Sermon
 *
 * @property mixed document
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /** @var object|null Item
     *
     * @since 7.0
     */
    protected ?object $item = null;

    /** @var Registry|null Params
     *
     * @since 7.0
     */
    protected ?Registry $params = null;

    /** @var  string|null Print
     *
     * @since 7.0
     */
    protected ?string $print = null;

    /** @var Registry|null State
     *
     * @since 7.0
     */
    protected ?Registry $state = null;

    /** @var  \Joomla\CMS\User\User|null User
     *
     * @since 7.0
     */
    protected ?\Joomla\CMS\User\User $user = null;

    /** @var  string|null Passage
     *
     * @since 7.0
     */
    protected ?string $passage = null;

    /** @var  string Print-friendly passage (always visible, no interactive elements)
     *
     * @since 10.1.0
     */
    protected string $printPassage = '';

    /** @var  string|null Related
     *
     * @since 7.0
     */
    protected ?string $related = null;

    /** @var  string|null Subscribe
     *
     * @since 7.0
     */
    protected ?string $subscribe = null;

    /** @var  int|null Menu ID
     *
     * @since 7.0
     */
    protected ?int $menuid = null;

    /** @var  string|null Details Link
     *
     * @since 7.0
     */
    protected ?string $detailslink = null;

    /** @var  \stdClass|null Page
     *
     * @since 7.0
     */
    protected ?\stdClass $page = null;

    /** @var  \stdClass|null Article
     *
     * @since 7.0
     */
    protected ?\stdClass $article = null;

    /** @var  array|null Comments
     *
     * @since 7.0
     */
    protected ?array $comments = null;

    /**
     * Simple Mode object
     *
     * @var object|null
     * @since 9.2.3
     */
    protected ?object $simple = null;

    /**
     * Form for comments
     *
     * @var \Joomla\CMS\Form\Form|null
     * @since 9.2.3
     */
    public ?\Joomla\CMS\Form\Form $form = null;

    /**
     * Captcha enabled flag
     *
     * @var bool
     * @since 9.2.3
     */
    public bool $captchaEnabled = false;

    /**
     * Template object
     *
     * @var object|null
     * @since 9.2.3
     */
    protected ?object $template = null;

    /**
     * Listing helper instance
     *
     * @var Cwmlisting|null
     * @since 10.0.0
     */
    protected ?Cwmlisting $listing = null;

    /**
     * Pre-calculated fluid listing HTML for the sermon
     *
     * @var string
     * @since 10.0.0
     */
    public string $fluidListing = '';

    /**
     * Recent messages for 404 not-found page
     *
     * @var array
     * @since 10.2.0
     */
    public array $recentItems = [];

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws \Exception
     * @todo  Need to clean up the display function as there is stuff needed to change up.
     *
     * @since 7.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $app            = Factory::getApplication();
        $this->form     = $this->get('Form');
        $user           = $app->getIdentity();
        $CWMListing     = new Cwmlisting();
        $this->item     = $this->get('Item');
        $this->state    = $this->get('State');
        $this->user     = $user;
        $this->comments = $this->get('comments');
        $this->simple   = Cwmhelper::getSimpleView();

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            $app->enqueueMessage(implode("\n", $errors), 'error');

            return;
        }

        // Create a shortcut for $item.
        $item = $this->item;

        if (!$item) {
            // Render within the site template so the user sees navigation,
            // footer, and suggested messages instead of a bare error page.
            // Set 404 for SEO after rendering starts so Gantry/Joomla can't intercept.
            http_response_code(404);
            $this->document->setTitle(Text::_('JBS_CMN_STUDY_NOT_FOUND'));

            // Load recent messages from cache (15 min TTL) to reduce server load
            $cacheKey   = 'proclaim_notfound_recent_' . implode(',', $user->getAuthorisedViewLevels());
            $cacheGroup = 'com_proclaim';
            $cache      = Factory::getContainer()->get(\Joomla\CMS\Cache\CacheControllerFactoryInterface::class)
                ->createCacheController('callback', ['defaultgroup' => $cacheGroup, 'lifetime' => 15]);

            $this->recentItems = $cache->get(
                static function () use ($user) {
                    $db    = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
                    $query = $db->getQuery(true)
                        ->select($db->quoteName(['s.id', 's.studytitle', 's.studydate', 's.alias', 's.studyintro', 's.thumbnailm', 's.image']))
                        ->select($db->quoteName('t.teachername'))
                        ->select($db->quoteName('t.teacher_thumbnail'))
                        ->select($db->quoteName('se.series_text'))
                        ->join('LEFT', $db->quoteName('#__bsms_series', 'se')
                            . ' ON ' . $db->quoteName('se.id') . ' = ' . $db->quoteName('s.series_id'))
                        ->from($db->quoteName('#__bsms_studies', 's'))
                        ->join('LEFT', $db->quoteName('#__bsms_study_teachers', 'stj')
                            . ' ON ' . $db->quoteName('stj.study_id') . ' = ' . $db->quoteName('s.id')
                            . ' AND ' . $db->quoteName('stj.ordering') . ' = 0')
                        ->join('LEFT', $db->quoteName('#__bsms_teachers', 't')
                            . ' ON ' . $db->quoteName('t.id') . ' = COALESCE(' . $db->quoteName('stj.teacher_id') . ', ' . $db->quoteName('s.teacher_id') . ')')
                        ->where($db->quoteName('s.published') . ' = 1')
                        ->whereIn($db->quoteName('s.access'), $user->getAuthorisedViewLevels())
                        ->order($db->quoteName('s.studydate') . ' DESC')
                        ->setLimit(5);
                    $db->setQuery($query);

                    return $db->loadObjectList() ?: [];
                },
                [],
                $cacheKey
            );

            $this->setLayout('notfound');
            parent::display($tpl);

            return;
        }

        $BiblePassage  = new Cwmshowscripture();
        $this->passage = $BiblePassage->buildAllPassages($this->item, $this->item->params);

        // Add router helpers.
        $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

        // Merge article params. If this is single-article view, menu params override article params
        // Otherwise, article params override menu item params
        $this->params = $this->state->params;
        $active       = $app->getMenu()->getActive();
        $temp         = clone $this->params;

        // Check to see which parameters should take priority
        if ($active) {
            $currentLink = $active->link;

            // If the current view is the active item and an article view for this article, then the menu item params take priority
            if (str_contains($currentLink, 'view=cwmsermon') && str_contains($currentLink, '&id=' . (string)$item->id)) {
                // $item->params are the article params, $temp are the menu item params
                // Merge so that the menu item params take priority
                $item->params->merge($temp);

                // Load layout from an active query (in case it is an alternative menu item)
                if (isset($active->query['layout'])) {
                    $this->setLayout($active->query['layout']);
                }
            } else {
                // The Current view is not a single article, so the article params takes priority here
                // Merge the menu item params with the article params so that the article params takes priority
                $temp->merge($item->params);
                $item->params = $temp;

                // Check for alternative layouts (since we are not in a single-article menu item)
                // Single-article menu item layout takes priority over alt layout for an article
                $layout = $item->params->get('sermon_layout');

                if ($layout) {
                    $this->setLayout($layout);
                }
            }
        } else {
            // Merge so that article params take priority
            $temp->merge($item->params);
            $item->params = $temp;

            // Check for alternative layouts (since we are not in a single-article menu item)
            // Single-article menu item layout takes priority over alt layout for an article
            $layout = $item->params->get('sermon_layout');

            if ($layout) {
                $this->setLayout($layout);
            }
        }

        $captchaSet = $temp->get('captcha', $app->get('captcha', '0'));

        foreach (PluginHelper::getPlugin('captcha') as $plugin) {
            if ($captchaSet === $plugin->name) {
                $this->captchaEnabled = true;
                break;
            }
        }

        $offset = (int)$this->state->get('list.offset');

        // Check the view access to the article (the model has already computed the values).
        if (
            $item->params->get('access-view') !== true && (($item->params->get('show_noauth') !== true && $user->guest))
        ) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
        }

        // Check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $groups = $user->getAuthorisedViewLevels();

        if (($this->item->access > 1) && !\in_array($this->item->access, $groups, true)) {
            $app->enqueueMessage(Text::_('JBS_CMN_ACCESS_FORBIDDEN'), 'error');
        }

        // Detect print mode early so we can disable tooltip wrapping on scripture refs
        $this->print = $app->getInput()->getString('print', '');

        $scriptureParams = $this->params;

        // Get Scripture references from listing class in case we don't use the page-builder class
        $this->item->scripture1    = $CWMListing->getScripture($scriptureParams, $item, 0, 1);
        $this->item->scripture2    = $CWMListing->getScripture($scriptureParams, $item, 0, 2);
        $this->item->allScriptures = $CWMListing->getAllScriptures($scriptureParams, $item);

        // @todo check to see if this works
        $this->item->topics = $this->item->topic_text;

        if ($item->params->get('showrelated') > 0) {
            $relatedstudies = new Cwmrelatedstudies();
            $this->related  = $relatedstudies->getRelated($this->item, $item->params);
        }

        // Only load page builder if the default template is NOT being used
        if (
            $item->params->get('useexpert_list') > 0
            || ($item->params->get('simple_mode') === '1')
            || (\is_string($item->params->get('sermontemplate')) === true && $item->params->get(
                'sermontemplate'
            ) !== '0')
        ) {
            $pagebuilder = new Cwmpagebuilder();
            $pagebuilder->enrichStudy($this->item, $this->item->params, $this->state->get('template'));
        }

        PluginHelper::importPlugin('content');
        $article       = new \stdClass();
        $article->text = $this->item->scripture1;
        $app->triggerEvent(
            'onContentPrepare',
            ['com_proclaim.sermons', &$article, &$this->item->params, null]
        );
        $this->item->scripture1 = $article->text;
        $article->text          = $this->item->scripture2;
        $app->triggerEvent(
            'onContentPrepare',
            ['com_proclaim.sermons', &$article, &$this->item->params, null]
        );
        $this->item->scripture2 = $article->text;
        $article->text          = $this->item->studyintro;
        $app->triggerEvent(
            'onContentPrepare',
            ['com_proclaim.sermons', &$article, &$this->item->params, null]
        );
        $this->item->studyintro = $article->text;
        $article->text          = $this->item->secondary_reference;
        $app->triggerEvent(
            'onContentPrepare',
            ['com_proclaim.sermons', &$article, &$this->item->params, null]
        );
        $this->item->secondary_reference = $article->text;

        // Get the podcast subscription
        $this->getDocument()->getWebAssetManager()->useStyle('com_proclaim.podcast');
        $podcast         = new Cwmpodcastsubscribe();
        $this->subscribe = $podcast->buildSubscribeTable($this->item->params->get('subscribeintro', 'Our Podcasts'));

        // Find the messages list menu item via Joomla's cached menu API
        $menuid = null;
        $menu   = $app->getMenu();

        foreach ($menu->getItems('component', 'com_proclaim') as $menuItem) {
            if (isset($menuItem->query['view']) && $menuItem->query['view'] === 'cwmsermons') {
                $menuid = $menuItem->id;
                break;
            }
        }

        $this->menuid = $menuid;

        if ($this->getLayout() === 'pagebreak') {
            $this->displayPagebrake($tpl);

            return;
        }

        // Always run content prepare on the study text so that inline
        // {scripture} tags (e.g. from AI-generated content) are processed
        // regardless of the template's show_scripture_link setting.
        $article->text = $this->item->studytext;
        $linkit        = (int) $this->item->params->get('show_scripture_link', 0);

        if ($linkit === 1) {
            PluginHelper::importPlugin('content');
        } else {
            // Always load at least the scripture links plugin for inline tags
            PluginHelper::importPlugin('content', 'scripturelinks');
        }

        $limitstart = (int) $app->getInput()->get('limitstart', 0, 'int');
        $app->triggerEvent(
            'onContentPrepare',
            ['com_proclaim.sermon', &$article, &$this->item->params, $limitstart]
        );
        $article->studytext    = $article->text;
        $this->item->studytext = $article->text;

        // Prepares a link string for use in social networking
        $u                 = Uri::getInstance();
        $detailslink       = htmlspecialchars($u->toString());
        $detailslink       = Route::_($detailslink);
        $this->detailslink = $detailslink;

        $this->page         = new \stdClass();
        $this->page->social = $CWMListing->getShare($detailslink, $this->item, $this->item->params);

        // End process prepares content plugins
        $this->template = $this->state->get('template');
        $this->article  = $article;

        // Store listing helper for template use and pre-calculate fluid listing
        $this->listing = $CWMListing;
        try {
            $this->fluidListing = $CWMListing->getFluidListing(
                $this->item,
                $this->item->params,
                $this->template,
                'sermon'
            );
        } catch (\Exception $e) {
            $this->fluidListing = '';
        }

        // Increment the hit counter of the Sermon.
        if ($offset === 0 && !$this->params->get('intro_only')) {
            /** @var CwmsermonModel $model */
            $model = $this->getModel();
            $model->hit();

            // Log analytics page_view event
            CwmanalyticsHelper::logEvent('page_view', (int) $item->id);
        }

        // Build print-friendly passage: always visible, no version switcher
        if (!empty($this->print)) {
            $printParams = clone $this->item->params;
            $printParams->set('show_passage_view', 2);
            $printParams->set('allow_version_switch', 0);
            $this->printPassage = $BiblePassage->buildAllPassages($this->item, $printParams);
        }

        // Load print stylesheet
        $wa = $this->getDocument()->getWebAssetManager();
        $wa->useStyle('com_proclaim.print');

        // Load player chapters script for chapter lists and timestamp seeking
        if (empty($this->print)) {
            $wa->useScript('com_proclaim.cwm-player-chapters');
            $wa->useStyle('com_proclaim.cwm-chapters');
            $wa->useScript('com_proclaim.cwm-transcript');
            $wa->useStyle('com_proclaim.cwm-transcript-css');
        }

        // Load scripture tooltip assets (per-element controlled; JS is a no-op
        // if no elements have show_tooltip enabled). Skip in print mode.
        if (empty($this->print)) {
            $wa->useScript('lib_cwmscripture.scripture-tooltip');
            $wa->useStyle('lib_cwmscripture.scripture-tooltip');

            $app->getDocument()->addScriptOptions('com_proclaim.scripture', [
                'ajaxUrl' => Route::_(
                    'index.php?option=com_proclaim&task=cwmscripture.getPassageXHR&format=raw',
                    false
                ),
            ]);

            // Register language strings used by scripture-switcher JS
            Text::script('JBS_CMN_SCRIPTURE_UNAVAILABLE');
            Text::script('JBS_CMN_SCRIPTURE_RETRY');
            Text::script('JBS_CMN_SCRIPTURE_FALLBACK');
            Text::script('JBS_CMN_SCRIPTURE_SERVICE_BUSY');
        }

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Display PageBrake
     *
     * @param   string  $tpl  ?
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0
     */
    protected function displayPageBrake(string $tpl): void
    {
        $this->document->setTitle(Text::_('JBS_CMN_READ_MORE'));
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
        $app     = Factory::getApplication();
        $menu    = $app->getMenu()->getActive();
        $pathway = $app->getPathway();

        $this->item->metadesc = mb_substr(trim(strip_tags($this->item->studyintro ?? '')), 0, 160);
        $this->item->metakey  = $this->item->topics;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('JBS_CMN_MESSAGE_TITLE'));
        }

        $title = $this->params->get('page_title', '');
        $id    = 0;

        if (isset($menu->query['id'])) {
            $id = (int) ($menu->query['id'] ?? 0);
        }

        // If the menu item does not concern this Study
        if ($menu && ($menu->query['option'] !== 'com_proclaim' || $menu->query['view'] !== 'sermon' || $id !== $this->item->id)) {
            // If this is not a single article menu item, set the page title to the article title
            if ($this->item->studytitle) {
                $title = $this->item->studytitle;
            }

            $pathway->addItem($this->item->studytitle, '');
        }

        // Check for empty title and add site name if param is set
        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) === 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) === 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        if (empty($title)) {
            $title = $this->item->studytitle;
        }

        $this->document->setTitle($title);

        if ($this->item->params->get('metadesc')) {
            $this->document->setDescription($this->item->params->get('metadesc'));
        } else {
            $this->document->setDescription(mb_substr(trim(strip_tags($this->item->studyintro ?? '')), 0, 160));
        }

        if ($this->item->params->get('metakey')) {
            $this->document->setMetaData('keywords', $this->item->params->get('metakey'));
        } elseif ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        } else {
            $this->document->setMetaData('keywords', $this->item->topic_text . ',' . $this->item->studytitle);
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }

        if ($app->get('MetaAuthor') === '1') {
            $this->document->setMetaData('author', $this->item->teachername);
        }

        // If there is a page-break heading or title, add it to the page title
        if (!empty($this->item->page_title)) {
            $this->item->title .= ' - ' . $this->item->page_title;
            $this->document->setTitle(
                $this->item->page_title . ' - '
                . Text::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $this->state->get('list.offset') + 1)
            );
        }

        if ($this->print) {
            $this->document->setMetaData('robots', 'noindex, nofollow');
        }

        // Schema.org structured data (skip in print mode)
        if (empty($this->print)) {
            CwmschemaorgHelper::inject(
                CwmschemaorgHelper::buildSermonDetail(
                    $this->item,
                    Uri::getInstance()->toString(),
                    $app->get('sitename')
                ),
                (int) ($this->item->id ?? 0),
                'com_proclaim.cwmmessage'
            );
        }
    }
}
