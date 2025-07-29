<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\CWMPodcastDisplay;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * View class for Messages
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
    protected $state;

    protected $item;

    protected $template;

    protected $media;

    protected $params;

    private $studies;

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
    public function display($tpl = null): void
    {
        $mainframe = Factory::getApplication();
        $input     = $mainframe->input;
        $document  = $mainframe->getDocument();

        // Get the menu item object
        // Load the Admin settings and params from the template
        $item        = $this->get('Item');
        $this->state = $this->get('State');

        /** @var Registry $params */
        $params         = $this->state->template->params;
        $this->template = $this->state->get('template');

        if (!$item) {
            return;
        }

        // Get studies associated with this series
        $mainframe->setUserState('sid', $item->id);
        $this->studies = $this->get('Studies');

        // Get the series image
        $image              = Cwmimages::getSeriesThumbnail($item->series_thumbnail);
        $item->image        = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="" />';
        $teacherimage       = Cwmimages::getTeacherThumbnail($item->thumb, $image2 = null);
        $item->teacherimage = '<img src="' . $teacherimage->path . '" height="' . $teacherimage->height . '" width="'
            . $teacherimage->width . '" alt="" />';

        $media = [];

        if ($this->studies) {
            foreach ($this->studies as $s => $stude) {
                $exmedias = explode(',', $stude->mids);
                $jbsmedia = new Cwmmedia();

                foreach ($exmedias as $i => $exmedia) {
                    $rmedia = $jbsmedia->getMediaRows2($exmedia);

                    if ($rmedia) {
                        $reg = new Registry();
                        $reg->loadString($rmedia->params);
                        $rparams = $reg;

                        if ($this->endsWith($rparams->get('filename'), '.mp3') === true) {
                            $media[] = $rmedia;
                        }
                    }
                }
            }
        }

        $this->media = $media;

        // Set Player build info
        $params->set('pcplaylist', 1);
        $params->set('show_filesize', 0);
        $params->set('mp3', true);

        // Prepare meta information (under development)
        if ($params->get('metakey')) {
            $document->setMetaData('keywords', $params->get('metakey'));
        }

        if ($params->get('metadesc')) {
            $document->setDescription($params->get('metadesc'));
        }

        // Check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user   = Factory::getApplication()->getIdentity();
        $groups = $user->getAuthorisedViewLevels();

        if (!in_array($item->access, $groups) && $item->access) {
            $mainframe->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

            return;
        }

        $input->set('returnid', $item->id);

        // End process prepare content plugins
        $this->params      = &$params;
        $this->item        = $item;
        $uri               = new Uri();
        $stringuri         = $uri->toString();
        $this->request_url = $stringuri;

        parent::display($tpl);
    }

    /**
     * Find Ends With
     *
     * @param   string  $haystack  Search string
     * @param   string  $needle    What to search for.
     *
     * @return bool
     *
     * @since version
     */
    private function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);

        if ($length === 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
