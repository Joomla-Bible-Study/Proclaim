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
     * @var Registry
     *
     * @since 7.0
     */
    public Registry $params;

    /**
     * Params
     *
     * @var mixed  State of the page
     *
     * @since 7.0
     */
    public mixed $state;

    public object $main;

    /**
     * Landing page helper instance
     *
     * @var Cwmlanding
     * @since 10.0.0
     */
    public Cwmlanding $landing;

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
        } elseif (!$itemparams->get('metakey')) {
            $document->setMetaData('keywords', $this->params->get('metakey'));
        }

        if ($itemparams->get('metadesc')) {
            $document->setDescription($itemparams->get('metadesc'));
        } elseif (!$itemparams->get('metadesc')) {
            $document->setDescription($this->params->get('metadesc'));
        }

        Cwmimages::getShowHide();

        // Get the main study list image
        $this->main = Cwmimages::mainStudyImage();

        $uri               = new Uri();
        $Uri_toString      = $uri->toString();
        $this->request_url = $Uri_toString;

        // Pre-create landing helper for template use
        $this->landing = new Cwmlanding();

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
        // End Switch
        if ($this->params->get('landing' . $showIt . 'limit')) {
            $showHide_tmp = Cwmimages::getShowHide();

            $showHideAll = "<div id='showhide" . $i . "'>";

            $buttonLink = "\n\t" . '<a class="showhideheadingbutton" href="javascript:ReverseDisplay2(' . "'showhide"
                . $showIt . "'" . ')">';
            $labelLink  = "\n\t" . '<a class="showhideheadinglabel" href="javascript:ReverseDisplay2(' . "'showhide"
                . $showIt . "'" . ')">';

            switch ($this->params->get('landing_hide', 0)) {
                case 0: // Image only
                    $showHideAll .= $buttonLink;

                    $showHideAll .= "\n\t\t" . '<img src="' . Uri::base() . $showHide_tmp->path . '" alt="' . $showIt_phrase . '" title="' . $showIt_phrase . '" border="0" width="';
                    $showHideAll .= $showHide_tmp->width . '" height="' . $showHide_tmp->height . '" />';
                    $showHideAll .= '<i class="fas fa-arrow-down" title="x"></i>';

                    // Spacer
                    $showHideAll .= ' ';
                    $showHideAll .= "\n\t" . '</a>';
                    break;

                case 1: // Image and label
                    $showHideAll .= $buttonLink;
                    $showHideAll .= '<i class="fas fa-arrow-down" title="x"></i>';

                    // Spacer
                    $showHideAll .= ' ';
                    $showHideAll .= "\n\t" . '</a>';
                    $showHideAll .= $labelLink;
                    $showHideAll .= "\n\t\t" . '<span id="landing_label">' . $this->params->get(
                        'landing_hidelabel'
                    ) . '</span>';
                    $showHideAll .= "\n\t" . '</a>';
                    break;

                case 2: // Label only
                    $showHideAll .= $labelLink;
                    $showHideAll .= "\n\t\t" . '<span id="landing_label">' . $this->params->get(
                        'landing_hidelabel'
                    ) . '</span>';
                    $showHideAll .= "\n\t" . '</a>';
                    break;
            }

            $showHideAll .= "\n" . '</div> <!-- end div id="showhide" for ' . $i . ' -->' . "\n";

            return $showHideAll;
        }

        return '';
    }
}
