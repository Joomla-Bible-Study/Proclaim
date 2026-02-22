<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmteacher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmschemaorgHelper;
use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Site\Helper\Cwmpagebuilder;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Contact\Site\Model\ContactModel;
use Joomla\Registry\Registry;

/**
 * View class for Teacher
 *
 * @since  7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /** @var  object|null Item
     *
     * @since 7.0
     */
    protected ?object $item;

    /** @var  object|null Contact
     *
     * @since 7.0
     */
    protected ?object $contact = null;

    /** @var  Registry|null Admin
     *
     * @since 7.0
     */
    protected ?Registry $state = null;

    /** @var  Registry|null Params
     *
     * @since 7.0
     */
    protected ?Registry $params = null;

    /** @var  object|null Template Info
     *
     * @since 7.0
     */
    protected ?object $template = null;

    /** @var  array|null Template Studies
     *
     * @since 7.0
     */
    protected ?array $teacherstudies = null;

    /** @var  bool|string|null Print flag
     *
     * @since 7.0
     */
    protected bool|string|null $print = null;

    /** @var  array|null Studies
     *
     * @since 7.0
     */
    protected ?array $studies = null;

    /**
     * Listing helper instance for template use
     *
     * @var Cwmlisting|null
     * @since 10.0.0
     */
    public ?Cwmlisting $listing = null;

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
    #[\Override]
    public function display($tpl = null): void
    {
        $pagebuilder = new Cwmpagebuilder();

        $images      = new Cwmimages();
        $this->state = $this->get('state');

        /** @var Registry $params */
        $params = $this->state->template->params;

        $input = Factory::getApplication()->getInput();
        $item  = $this->get('Item');

        // Add the slug
        $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : str_replace(
            ' ',
            '-',
            htmlspecialchars_decode($item->teachername, ENT_QUOTES)
        ) . ':' . $item->id;
        $id         = $input->get('id', '0', 'get');
        $item->id   = $id;

        $image      = $images::getTeacherThumbnail($item->teacher_thumbnail, $item->teacher_image ?? '');
        $largeimage = $images::getTeacherImage($item->image, $item->teacher_image);

        if ($image) {
            $item->image = Cwmimages::renderPicture($image, $item->teachername ?? '');
        }

        if ($largeimage) {
            $item->largeimage = Cwmimages::renderPicture($largeimage, $item->teachername ?? '');
        }

        if (isset($item->information)) {
            $item->text        = $item->information;
            $information       = $pagebuilder->runContentPlugins($item, $params);
            $item->information = $information->text;
        } elseif (isset($item->short)) {
            $item->text  = $item->short;
            $short       = $pagebuilder->runContentPlugins($item, $params);
            $item->short = $short->text;
        }

        // Check to see if com_contact used instead
        if ($item->contact) {
            $language = Factory::getApplication()->getLanguage();
            $language->load('com_contact', JPATH_SITE);

            $contactmodel = new ContactModel();

            try {
                $this->contact = $contactmodel->getItem($pk = $item->contact);
            } catch (\Throwable $throwable) {
                $this->contact = null;
            }

            if ($this->contact !== null) {
                // Substitute contact info from com_contacts for duplicate fields
                $item->title       = $this->contact->con_position;
                $item->teachername = $this->contact->name;
                $item->email       = $this->contact->email_to;
                $largeimage        = $images::getImagePath((string) $this->contact->image);
                $item->largeimage  = Cwmimages::renderPicture($largeimage, $item->teachername ?? '');
                $item->information = $this->contact->misc;
                $item->phone       = $this->contact->telephone;
                $cregistry         = new Registry();
                $cregistry->loadString($this->contact->params);
                $contact_params     = $cregistry;
                $item->facebooklink = $contact_params->get('linka');
                $item->twitterlink  = $contact_params->get('linkb');
                $item->bloglink     = $contact_params->get('linkc');
                $item->link1        = $contact_params->get('linkd');
                $item->linklabel1   = $contact_params->get('linklabel1');
                $item->link2        = $contact_params->get('linke');
                $item->linklabel2   = $contact_params->get('linke_name');
                $item->website      = $this->contact->webpage;
                $item->address      = $this->contact->address;
            }
        }

        $this->item = $item;

        $whereitem  = (int)$item->id;
        $wherefield = 'teacher';
        $limit      = $params->get('studies', '20');
        $order      = 'DESC';
        $template   = $input->get('template');

        if ($params->get('show_teacher_studies') > 0) {
            $studies  = $pagebuilder->studyBuilder(
                $whereitem,
                $wherefield,
                $params,
                $limit,
                $order,
                $template
            );
            $template = $this->state->template;

            foreach ($studies as $i => $study) {
                $pelements               = $pagebuilder->buildPage($study, $params, $template);
                $studies[$i]->scripture1 = $pelements->scripture1;
                $studies[$i]->scripture2 = $pelements->scripture2;
                $studies[$i]->media      = $pelements->media;
                $studies[$i]->studydate  = $pelements->studydate;
                $studies[$i]->topics     = $pelements->topics;

                if (isset($pelements->study_thumbnail)) {
                    $studies[$i]->study_thumbnail = $pelements->study_thumbnail;
                } else {
                    $studies[$i]->study_thumbnail = null;
                }

                if (isset($pelements->series_thumbnail)) {
                    $studies[$i]->series_thumbnail = $pelements->series_thumbnail;
                } else {
                    $studies[$i]->series_thumbnail = null;
                }

                $studies[$i]->detailslink = $pelements->detailslink;

                if (!isset($pelements->studyintro)) {
                    $pelements->studyintro = '';
                }

                $studies[$i]->studyintro = $pelements->studyintro;

                if (isset($pelements->secondary_reference)) {
                    $studies[$i]->secondary_reference = $pelements->secondary_reference;
                } else {
                    $studies[$i]->secondary_reference = '';
                }

                if (isset($pelements->sdescription)) {
                    $studies[$i]->sdescription = $pelements->sdescription;
                } else {
                    $studies[$i]->sdescription = '';
                }
            }

            $this->teacherstudies = $studies;
            $this->studies        = $studies;
        }

        $this->item = $item;
        $print      = $input->get('print', '', 'bool');

        // Build the HTML select list for ordering
        $this->print    = $print;
        $this->params   = $params;
        $this->template = $this->state->template;
        $this->document = Factory::getApplication()->getDocument();

        // Pre-create listing helper for template use
        $this->listing = new Cwmlisting();

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
        $app   = Factory::getApplication('site');
        $menus = $app->getMenu();

        /** @var $itemparams Registry */
        $itemparams = $app->getParams();

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('JGLOBAL_ARTICLES'));
        }

        $title = $this->params->get('page_title', '');
        $title .= ' : ' . $this->item->teachername . ' - ' . $this->item->title;

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        // Prepare meta-information (under development)
        if ($itemparams->get('metakey')) {
            $this->document->setMetaData('keywords', $itemparams->get('metakey'));
        } elseif ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        } else {
            $this->document->setMetaData('keywords', $this->params->get('metakey'));
        }

        if ($itemparams->get('metadesc')) {
            $this->document->setDescription($itemparams->get('metadesc'));
        } elseif ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        } else {
            $this->document->setDescription($this->params->get('metadesc'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }

        // Schema.org structured data
        if (CwmschemaorgHelper::isEnabled()) {
            CwmschemaorgHelper::inject(
                CwmschemaorgHelper::buildTeacherDetail(
                    $this->item,
                    Uri::getInstance()->toString()
                )
            );
        }
    }
}
