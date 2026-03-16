<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmlandingpage;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use CWM\Component\Proclaim\Site\Helper\Cwmlanding;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * Landing page list view class
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    /** @var  string Request URL
     *
     * @since 7.0
     */
    public string $request_url;

    /**
     * Params
     *
     * @var Registry|null
     *
     * @since 7.0
     */
    public ?Registry $params = null;

    /**
     * Params
     *
     * @var mixed  State of the page
     *
     * @since 7.0
     */
    public mixed $state;

    /**
     * Main study image object
     *
     * @var object|null
     * @since 7.0
     */
    public ?object $main = null;

    /**
     * Landing page helper instance
     *
     * @var Cwmlanding|null
     * @since 10.0.0
     */
    public ?Cwmlanding $landing = null;

    /**
     * Pre-fetched landing page data
     *
     * @var array
     * @since 10.1.0
     */
    public array $landingData = [];

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
        $document = Factory::getApplication()->getDocument();

        $this->state  = $this->get('state');
        $this->params = $this->state->template->params;

        $itemparams = ComponentHelper::getParams('com_proclaim');

        // Prepare meta-information (under development)
        if ($itemparams->get('metakey')) {
            $document->setMetaData('keywords', $itemparams->get('metakey'));
        } else {
            $document->setMetaData('keywords', $this->params->get('metakey'));
        }

        if ($itemparams->get('metadesc')) {
            $document->setDescription($itemparams->get('metadesc'));
        } else {
            $document->setDescription($this->params->get('metadesc'));
        }

        // Get the main study list image
        $this->main = Cwmimages::mainStudyImage();

        $uri               = new Uri();
        $Uri_toString      = $uri->toString();
        $this->request_url = $Uri_toString;

        // Pre-create landing helper for template use
        $this->landing = new Cwmlanding();

        // Fetch all landing data in a single query
        $this->landingData = $this->landing->getLandingData($this->params);

        // Load landing page assets
        $wa = $document->getWebAssetManager();
        $wa->useStyle('com_proclaim.cwm-landing');
        $wa->useScript('com_proclaim.cwm-landing-toggle');

        parent::display($tpl);
    }

    /**
     * Parse through the Show hid buttons/links
     *
     * @param   string  $showIt         Name of Show
     * @param   string  $showIt_phrase  Name of the
     * @param   int     $i              Number of Show
     *
     * @return string
     *
     * @throws \Exception
     * @since 9.2.4
     */
    public function getShowHide($showIt, $showIt_phrase, $i): string
    {
        if (!$this->params->get('landing' . $showIt . 'limit')) {
            return '';
        }

        $data = [
            'showIt'        => $showIt,
            'showIt_phrase' => $showIt_phrase,
            'i'             => $i,
            'params'        => $this->params,
            'image'         => Cwmimages::getShowHide(),
        ];

        // Example: Referencing a layout located in the Administrator component folder
        return LayoutHelper::render(
            'landing.showhide',
            $data,
            JPATH_ADMINISTRATOR . '/components/com_proclaim/layouts'
        );
    }
}
