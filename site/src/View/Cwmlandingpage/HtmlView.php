<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmlandingpage;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
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
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws Exception
     * @since 7.0
     */
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
     * @throws Exception
     * @since 9.2.4
     */
    public function getShowHide($showIt, $showIt_phrase, $i)
    {
        // End Switch
        if ($this->params->get('landing' . $showIt . 'limit')) {
            $showhide_tmp = Cwmimages::getShowHide();

            $showhideall = "<div id='showhide" . $i . "'>";

            $buttonlink = "\n\t" . '<a class="showhideheadingbutton" href="javascript:ReverseDisplay2(' . "'showhide"
                . $showIt . "'" . ')">';
            $labellink  = "\n\t" . '<a class="showhideheadinglabel" href="javascript:ReverseDisplay2(' . "'showhide"
                . $showIt . "'" . ')">';

            switch ($this->params->get('landing_hide', 0)) {
                case 0: // Image only
                    $showhideall .= $buttonlink;

                    // $showhideall .= "\n\t\t" . '<img src="' . Uri::base() . $showhide_tmp->path . '" alt="'
                    // . Text::_('JBS_CMN_SHOW_HIDE_ALL');
                    // $showhideall .= ' ' . $showIt_phrase . '" title="' . Text::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' .
                    //  $showIt_phrase . '" border="0" width="';
                    // $showhideall .= $showhide_tmp->width . '" height="' . $showhide_tmp->height . '" />';
                    $showhideall .= '<i class="fas fa-arrow-down" title="x"></i>';

                    // Spacer
                    $showhideall .= ' ';
                    $showhideall .= "\n\t" . '</a>';
                    break;

                case 1: // Image and label
                    $showhideall .= $buttonlink;

                    // $showhideall .= "\n\t\t" . '<img src="' . Uri::base() . $showhide_tmp->path . '" alt="' .
                    //  Text::_('JBS_CMN_SHOW_HIDE_ALL');
                    // $showhideall .= ' ' . $showIt_phrase . '" title="' . Text::_('JBS_CMN_SHOW_HIDE_ALL') . ' ' .
                    //  $showIt_phrase . '" border="0" width="';
                    // $showhideall .= $showhide_tmp->width . '" height="' . $showhide_tmp->height . '" />';

                    $showhideall .= '<i class="fas fa-arrow-down" title="x"></i>';

                    // Spacer
                    $showhideall .= ' ';
                    $showhideall .= "\n\t" . '</a>';
                    $showhideall .= $labellink;
                    $showhideall .= "\n\t\t" . '<span id="landing_label">' . $this->params->get(
                        'landing_hidelabel'
                    ) . '</span>';
                    $showhideall .= "\n\t" . '</a>';
                    break;

                case 2: // Label only
                    $showhideall .= $labellink;
                    $showhideall .= "\n\t\t" . '<span id="landing_label">' . $this->params->get(
                        'landing_hidelabel'
                    ) . '</span>';
                    $showhideall .= "\n\t" . '</a>';
                    break;
            }

            $showhideall .= "\n" . '</div> <!-- end div id="showhide" for ' . $i . ' -->' . "\n";

            return $showhideall;
        }

        return '';
    }
}
