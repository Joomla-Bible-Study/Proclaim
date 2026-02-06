<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmterms;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Registry\Registry;

// This is the popup window for the teachings.  We could put anything in this window.

/**
 * View class for Terms
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Media
     *
     * @var object|null
     *
     * @since 7.0
     */
    public ?object $media = null;

    /**
     * Document
     *
     * @var Document|null
     *
     * @since 7.0
     */
    public $document;

    /**
     * Params
     *
     * @var Registry|null
     *
     * @since 7.0
     */
    protected ?Registry $params = null;

    /**
     * Compatibility mode flag
     *
     * @var int
     * @since 7.0
     */
    public int $compat_mode = 0;

    /**
     * Terms text content
     *
     * @var string|null
     * @since 7.0
     */
    public ?string $termstext = null;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    #[\Override]
    public function display($tpl = null): void
    {
        $input             = Factory::getApplication()->getInput();
        $mid               = $input->get('mid', '', 'int');
        $this->compat_mode = $input->get('compat_mode', '0', 'int');

        $template           = Cwmparams::getTemplateparams();
        $this->params       = $template->params;
        $this->termstext    = $this->params->get('terms');
        $db                 = Factory::getContainer()->get('DatabaseDriver');
        $query              = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__bsms_mediafiles'));
        $query->where($db->quoteName('id') . ' = ' . (int) $mid);
        $db->setQuery($query);
        $this->media = $db->loadObject();

        // Params are the individual params for the media file record
        if ($this->media) {
            $registory = new Registry();
            $registory->loadString($this->media->params);
            $this->media->params = $registory;
        }

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document;
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0.0
     */
    protected function prepareDocument(): void
    {
        $app   = Factory::getApplication();
        $menus = $app->getMenu();

        $itemparams = ComponentHelper::getParams('com_proclaim');
        $title      = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('JGLOBAL_ARTICLES'));
        }

        $title = $this->params->get('page_title', '');
        if ($this->media && $this->media->params instanceof Registry) {
            $title .= ' : ' . $this->media->params->get('filename');
        }

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ((int)$app->get('sitename_pagetitles', 0) === 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ((int)$app->get('sitename_pagetitles', 0) === 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        // Prepare meta information (under development)
        if ($itemparams->get('metakey')) {
            $this->document->setMetaData('keywords', $itemparams->get('metakey'));
        } elseif ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($itemparams->get('metadesc')) {
            $this->document->setDescription($itemparams->get('metadesc'));
        } elseif ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
