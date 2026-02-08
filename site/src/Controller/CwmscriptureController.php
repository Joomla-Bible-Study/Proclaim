<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Controller;

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Site\Bible\BibleProviderFactory;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Scripture AJAX controller.
 *
 * Provides endpoint for frontend Bible version switching.
 *
 * @since  10.1.0
 */
class CwmscriptureController extends BaseController
{
    /**
     * Fetch a passage via AJAX. Returns JSON with passage text.
     *
     * URL: index.php?option=com_proclaim&task=cwmscripture.getPassageXHR&reference=...&version=...&token=1
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public function getPassageXHR(): void
    {
        $app      = Factory::getApplication();
        $document = $app->getDocument();
        $input    = $app->getInput();

        $document->setMimeEncoding('application/json');

        if (!Session::checkToken('get')) {
            echo json_encode(['success' => false, 'message' => 'Invalid token'], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        $reference = $input->getString('reference', '');
        $version   = $input->getString('version', 'kjv');

        if (empty($reference)) {
            echo json_encode(['success' => false, 'message' => 'No reference provided'], JSON_THROW_ON_ERROR);
            $app->close();

            return;
        }

        try {
            $admin       = Cwmparams::getAdmin();
            $adminParams = $admin->params ?? new Registry();
        } catch (\Exception $e) {
            $adminParams = new Registry();
        }

        try {
            $provider  = BibleProviderFactory::getProviderForTranslation($version, $adminParams);
            $cacheDays = (int) $adminParams->get('scripture_cache_days', 30);

            if ($cacheDays > 0 && method_exists($provider, 'setCacheTtl')) {
                $provider->setCacheTtl($cacheDays * 86400);
            }

            $result = $provider->getPassage($reference, $version);

            echo json_encode([
                'success'     => true,
                'text'        => $result->text ?? '',
                'copyright'   => $result->copyright ?? '',
                'translation' => $version,
                'isIframe'    => $result->isIframe,
                'iframeUrl'   => $result->iframeUrl ?? '',
            ], JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch passage',
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }
}
