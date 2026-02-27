<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmteachers;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Site\Helper\Cwmpagebuilder;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * View class for Teachers
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Template Table
     *
     * @var \stdClass
     *
     * @since 7.0
     */
    protected \stdClass $template;

    /**
     * Items
     *
     * @var array|null
     *
     * @since 7.0
     */
    protected ?array $items = [];

    /**
     * Pagination
     *
     * @var Pagination
     *
     * @since 7.0
     */
    protected Pagination $pagination;

    /**
     * State
     *
     * @var Registry|null
     *
     * @since 7.0
     */
    protected ?Registry $state = null;

    /**
     * Params
     *
     * @var Registry|null
     *
     * @since 7.0
     */
    protected ?Registry $params = null;

    /**
     * Admin
     *
     * @var object|null
     *
     * @since 7.0
     */
    protected ?object $admin;

    /**
     * Page
     *
     * @var object|null
     *
     * @since 7.0
     */
    protected ?object $page;

    /**
     * Listing helper instance for template use
     *
     * @var Cwmlisting|null
     * @since 10.0.0
     */
    public ?Cwmlisting $listing = null;

    /**
     * Studies the element CSS class
     *
     * @var string
     * @since 10.0.0
     */
    public string $classelement = '';

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $state = $this->get('State');
        $items = $this->get('Items');

        $params = $state->template->params;

        $this->template = $state->get('template');

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'warning');
        }

        // Load the Admin settings and params from the template
        $this->admin = $state->get('administrator');

        $useCustomTemplate = $params->get('useexpert_teacherdetail') > 0
            || \is_string($params->get('teacherstemplate'));
        $pagebuilder = $useCustomTemplate ? new Cwmpagebuilder() : null;

        foreach ($items as $item) {
            if (isset($item->teacher_thumbnail)) {
                $image             = Cwmimages::getTeacherThumbnail($item->teacher_thumbnail, $item->teacher_image ?? '');
                $item->image       = Cwmimages::renderPicture($image, $item->teachername ?? '');
                $item->slug        = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':'
                    . str_replace(' ', '-', htmlspecialchars_decode($item->teachername, ENT_QUOTES));
                $item->teacherlink = Route::_(
                    'index.php?option=com_proclaim&view=teacher&id=' . $item->slug . '&t=' . $this->template->id
                );

                if ($pagebuilder !== null) {
                    if (isset($item->information)) {
                        $item->text        = $item->information;
                        $information       = $pagebuilder->runContentPlugins($item, $params);
                        $item->information = $information->text;
                    }

                    if (isset($item->short)) {
                        $item->text  = $item->short;
                        $short       = $pagebuilder->runContentPlugins($item, $params);
                        $item->short = $short->text;
                    }
                }
            }
        }

        $pagination            = $this->get('Pagination');
        $this->page            = new \stdClass();
        $this->page->pagelinks = $pagination->getPagesLinks();
        $this->page->counter   = $pagination->getPagesCounter();
        $this->pagination      = $pagination;
        $this->params          = $params;
        $this->items           = $items;

        // Pre-create listing helper for template use
        $this->listing      = new Cwmlisting();
        $this->classelement = $this->listing->createelement($params->get('teachers_element'));

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document;
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

        /** @var Registry $itemparams */
        $itemparams = $app->getParams();

        // Because the application sets a default page title,
        // We need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('JGLOBAL_ARTICLES'));
        }

        $title = $this->params->get('page_title', '');

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) === 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) === 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        // Prepare meta information (under development)
        if ($itemparams->get('metakey')) {
            $this->document->setMetaData('keywords', $itemparams->get('metakey'));
        } elseif ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        } else {
            $this->document->setMetaData('keywords', $this->admin->params->get('metakey'));
        }

        if ($itemparams->get('metadesc')) {
            $this->document->setDescription($itemparams->get('metadesc'));
        } elseif ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        } else {
            $this->document->setDescription($this->admin->params->get('metadesc'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
