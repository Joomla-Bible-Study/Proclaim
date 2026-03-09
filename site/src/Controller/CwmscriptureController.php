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
use CWM\Component\Proclaim\Site\Bible\AbstractBibleProvider;
use CWM\Component\Proclaim\Site\Bible\BibleProviderFactory;
use Joomla\CMS\Application\CMSApplicationInterface;
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
 * Provides an endpoint for frontend Bible version switching.
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
        $app   = Factory::getApplication();
        $input = $app->getInput();

        // Clean any prior output, then start a fresh buffer to capture stray
        // PHP warnings/notices that may occur during provider execution.
        while (ob_get_level()) {
            ob_end_clean();
        }

        ob_start();

        if (!Session::checkToken('get')) {
            ob_end_clean();
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');
            try {
                echo json_encode(['success' => false, 'message' => 'Invalid token'], JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                echo '{"success":false,"message":"JSON encoding error"}';
            }
            $app->close();

            return;
        }

        $reference = $input->getString('reference', '');
        $version   = $input->getString('version', 'kjv');

        if (empty($reference)) {
            $this->sendJson($app, ['success' => false, 'message' => 'No reference provided']);

            return;
        }

        try {
            $admin       = Cwmparams::getAdmin();
            $adminParams = $admin->params ?? new Registry();
        } catch (\Exception $e) {
            $adminParams = new Registry();
        }

        try {
            AbstractBibleProvider::registerLogger();
            $provider  = BibleProviderFactory::getProviderForTranslation($version, $adminParams);
            $cacheDays = (int) $adminParams->get('scripture_cache_days', 30);

            if ($cacheDays > 0 && method_exists($provider, 'setCacheTtl')) {
                $provider->setCacheTtl($cacheDays * 86400);
            }

            $result      = $provider->getPassage($reference, $version);
            $transient   = ($provider instanceof AbstractBibleProvider) && $provider->isLastErrorTransient();
            $usedVersion = $version;

            // Fallback 1: try same version via Local provider
            if (!$result->hasText() && $provider->getName() !== 'local') {
                try {
                    $localProvider = BibleProviderFactory::getProvider('local');
                    $localResult   = $localProvider->getPassage($reference, $version);

                    if ($localResult->hasText()) {
                        $result    = $localResult;
                        $transient = false;
                    }
                } catch (\Throwable $e) {
                    // Continue to next fallback
                }
            }

            // Fallback 2: try admin default version locally
            if (!$result->hasText()) {
                $defaultVersion = (string) $adminParams->get('default_bible_version', 'kjv');

                if ($defaultVersion === '') {
                    $defaultVersion = 'kjv';
                }

                if ($defaultVersion !== $version) {
                    try {
                        $localProvider = BibleProviderFactory::getProvider('local');
                        $defaultResult = $localProvider->getPassage($reference, $defaultVersion);

                        if ($defaultResult->hasText()) {
                            $result      = $defaultResult;
                            $usedVersion = $defaultVersion;
                            $transient   = false;
                        }
                    } catch (\Throwable $e) {
                        // Continue to hard fallback
                    }
                }
            }

            // Fallback 3: hard fallback to KJV (bundled, always auto-downloaded)
            if ($usedVersion !== 'kjv' && !$result->hasText()) {
                try {
                    $localProvider = BibleProviderFactory::getProvider('local');
                    $kjvResult     = $localProvider->getPassage($reference, 'kjv');

                    if ($kjvResult->hasText()) {
                        $result      = $kjvResult;
                        $usedVersion = 'kjv';
                        $transient   = false;
                    }
                } catch (\Throwable $e) {
                    // Even KJV failed
                }
            }

            $providerName = ($provider instanceof AbstractBibleProvider) ? $provider->getName() : 'unknown';

            if ($result->hasText()) {
                $this->sendJson($app, [
                    'success'     => true,
                    'text'        => $result->text,
                    'copyright'   => $result->copyright,
                    'translation' => $usedVersion,
                    'fallback'    => $usedVersion !== $version,
                    'provider'    => $providerName,
                    'requested'   => $version,
                ]);
            } else {
                $this->sendJson($app, [
                    'success'   => false,
                    'retryable' => $transient,
                    'message'   => 'No passage text returned',
                    'provider'  => $providerName,
                    'requested' => $version,
                ]);
            }
        } catch (\Throwable $e) {
            $this->sendJson($app, [
                'success'   => false,
                'retryable' => false,
                'message'   => $e->getMessage(),
                'requested' => $version,
            ]);
        }
    }

    /**
     * Discard any buffered PHP output and send a clean JSON response.
     *
     * @param   CMSApplicationInterface  $app   Application instance
     * @param   array                    $data  Response payload
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function sendJson(CMSApplicationInterface $app, array $data): void
    {
        // Discard any stray PHP warnings/notices captured during execution
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        try {
            echo json_encode($data, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            echo '{"success":false,"message":"JSON encoding error"}';
        }
        $app->close();
    }
}
