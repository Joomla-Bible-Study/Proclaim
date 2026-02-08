<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\View\Cwmseriespodcastdisplay;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\Cwmimages;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
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
    /**
     * State object
     *
     * @var Registry|null
     * @since 7.0
     */
    protected ?Registry $state = null;

    /**
     * Item object (podcast)
     *
     * @var object|null
     * @since 7.0
     */
    protected ?object $item = null;

    /**
     * Template object
     *
     * @var object|null
     * @since 7.0
     */
    protected ?object $template = null;

    /**
     * Media files array
     *
     * @var array
     * @since 7.0
     */
    protected array $media = [];

    /**
     * Parameters
     *
     * @var Registry|null
     * @since 7.0
     */
    protected ?Registry $params = null;

    /**
     * Request URL string
     *
     * @var string
     * @since 7.0
     */
    protected string $request_url = '';

    /**
     * Pagination object
     *
     * @var Pagination|null
     * @since 7.0
     */
    protected ?Pagination $pagination = null;

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
        $input = Factory::getApplication()->getInput();

        // Get the menu item object
        // Load the Admin settings and params from the template
        $item        = $this->get('Item');
        $this->state = $this->get('State');

        /** @var Registry $params */
        $params         = clone $this->state->template->params;
        $this->template = $this->state->get('template');

        if (!$item) {
            return;
        }

        // Get studies associated with this series
        Factory::getApplication()->setUserState('sid', $item->id);
        $studies = $this->get('Studies');

        // Pagination
        $total            = $this->get('Total');
        $limit            = $params->get('series_detail_limit', 20);
        $limitstart       = $this->state->get('list.offset');
        $this->pagination = new Pagination($total, $limitstart, $limit);

        // Get the series image
        $image              = Cwmimages::getSeriesThumbnail($item->series_thumbnail);
        $item->image        = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="" />';
        $teacherImage       = Cwmimages::getTeacherThumbnail($item->thumb, $image2 = null);
        $item->teacherimage = '<img src="' . $teacherImage->path . '" height="' . $teacherImage->height . '" width="'
            . $teacherImage->width . '" alt="" />';

        $media = [];

        if ($studies) {
            foreach ($studies as $s => $study) {
                $medias   = !empty($study->mids) ? explode(',', $study->mids) : [];
                $jbsMedia = new Cwmmedia();

                foreach ($medias as $i => $extraMedia) {
                    $rowMedia = $jbsMedia->getMediaRows2((int)$extraMedia);

                    if ($rowMedia) {
                        $reg = new Registry();
                        $reg->loadString($rowMedia->params);
                        $rowParams = $reg;

                        if (str_ends_with($rowParams->get('filename', ''), '.mp3')) {
                            $media[] = $rowMedia;
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

        // Prepare meta-information (under development)
        if ($params->get('metakey')) {
            $this->getDocument()->setMetaData('keywords', $params->get('metakey'));
        }

        if ($params->get('metadesc')) {
            $this->getDocument()->setDescription($params->get('metadesc'));
        }

        // Check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user   = Factory::getApplication()->getIdentity();
        $groups = $user->getAuthorisedViewLevels();

        if ($item->access && !\in_array($item->access, $groups, true)) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

            return;
        }

        $input->set('returnid', $item->id);

        // End process prepare content plugins
        $this->params      = $params;
        $this->item        = $item;
        $this->request_url = (new Uri())->toString();

        parent::display($tpl);
    }
}
