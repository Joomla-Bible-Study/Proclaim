<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Controller;

use CWM\Component\Proclaim\Administrator\Helper\CwmanalyticsHelper;
use CWM\Component\Proclaim\Site\Helper\Cwmdownload;
use CWM\Component\Proclaim\Site\Helper\Cwmlisting;
use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller class for Sermons
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmsermonsController extends BaseController
{
    /**
     * Download?
     *
     * @return void
     *
     * @throws \Exception
     * @since 7.0
     */
    public function download(): void
    {
        $input = Factory::getApplication()->getInput();
        $task  = $input->get('task');
        $mid   = $input->getInt('id');

        if ($task === 'download') {
            CwmanalyticsHelper::logEvent('download', 0, $mid);
            $downloader = new Cwmdownload();
            $downloader->download($mid);
        }
    }

    /**
     * Add hits to the play count. Used in direct link redirects
     *
     * @return  void
     *
     * @throws \Exception
     * @since 7.0
     */
    public function playHit(): void
    {
        $app      = Factory::getApplication();
        $id       = $app->getInput()->getInt('id', 0);
        $getMedia = new Cwmmedia();
        $getMedia->hitPlay($id);
        CwmanalyticsHelper::logEvent('play', 0, $id);

        // Now the hit has been updated will redirect to the url.
        $return = $app->getInput()->get('return');
        $return = base64_decode($return);
        $app->redirect($return);
    }

    /**
     * AJAX endpoint to record a play hit without redirect.
     * Used by Fancybox, inline players, and other JS-based media playback.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.1.0
     */
    public function playHitAjax(): void
    {
        $app = Factory::getApplication();
        $id  = $app->getInput()->getInt('id', 0);

        if ($id > 0) {
            $getMedia = new Cwmmedia();
            $getMedia->hitPlay($id);
            CwmanalyticsHelper::logEvent('play', 0, $id);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $id > 0]);
        $app->close();
    }

    /**
     * AJAX endpoint for sermon list filtering.
     *
     * Returns rendered HTML fragment and pagination data so the page can
     * update without a full reload.  Falls back gracefully — the form
     * still works via normal POST when JavaScript is disabled.
     *
     * URL: index.php?option=com_proclaim&task=cwmsermons.filterAjax&format=raw&{token}=1
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function filterAjax(): void
    {
        $app = Factory::getApplication();
        header('Content-Type: application/json; charset=utf-8');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => 'Invalid token'], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            // Flatten bracket-format params (filter[teacher] → filter_teacher) so
            // the model's getUserStateFromRequest() finds them by their flat keys.
            $input  = $app->getInput();
            $filter = $input->get('filter', [], 'array');

            foreach ($filter as $key => $value) {
                $input->set('filter_' . $key, $value);
            }

            $list = $input->get('list', [], 'array');

            foreach ($list as $key => $value) {
                $input->set('list_' . $key, $value);
            }

            /** @var \CWM\Component\Proclaim\Site\Model\CwmsermonsModel $model */
            $model    = $this->getModel('Cwmsermons', 'Site');
            $state    = $model->getState();
            $template = $state->get('template');
            $params   = $state->get('params');

            $items      = $model->getItems();
            $pagination = $model->getPagination();

            // Render the listing HTML using the same helper the template uses.
            $listing = new Cwmlisting();
            $html    = '';

            if ($items) {
                $html = $listing->getFluidListing($items, $params, $template, 'sermons');
            }

            // Build cross-filtered dropdown options so the client can
            // narrow the remaining filter dropdowns to match the active
            // selection.  Each field's getOptions() already applies
            // CwmfilterHelper::applyCrossFilters() internally.
            $filterForm    = $model->getFilterForm();
            $filterOptions = [];
            $filterFields  = ['book', 'teacher', 'series', 'messagetype', 'year', 'topic', 'location'];

            foreach ($filterFields as $fieldName) {
                $field = $filterForm->getField($fieldName, 'filter');

                if (!$field) {
                    continue;
                }

                $options = [];

                foreach ($field->options as $opt) {
                    // Skip placeholder options (empty value)
                    if ((string) $opt->value === '') {
                        continue;
                    }

                    $options[] = ['value' => $opt->value, 'text' => $opt->text];
                }

                $filterOptions[$fieldName] = $options;
            }

            echo json_encode([
                'success'       => true,
                'html'          => $html,
                'pagination'    => $pagination->getPagesLinks(),
                'pagesCounter'  => $pagination->getPagesCounter(),
                'total'         => $pagination->total,
                'pagesTotal'    => $pagination->pagesTotal,
                'filterOptions' => $filterOptions,
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to load results',
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }
}
