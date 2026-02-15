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
            AbstractBibleProvider::registerLogger();
            $provider  = BibleProviderFactory::getProviderForTranslation($version, $adminParams);
            $cacheDays = (int) $adminParams->get('scripture_cache_days', 30);

            if ($cacheDays > 0 && method_exists($provider, 'setCacheTtl')) {
                $provider->setCacheTtl($cacheDays * 86400);
            }

            $result      = $provider->getPassage($reference, $version);
            $transient   = ($provider instanceof AbstractBibleProvider) && $provider->lastErrorTransient;
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
                } catch (\Exception $e) {
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
                    } catch (\Exception $e) {
                        // Continue to hard fallback
                    }
                }
            }

            // Fallback 3: hard fallback to KJV (bundled, always auto-downloaded)
            if (!$result->hasText() && $usedVersion !== 'kjv') {
                try {
                    $localProvider = BibleProviderFactory::getProvider('local');
                    $kjvResult     = $localProvider->getPassage($reference, 'kjv');

                    if ($kjvResult->hasText()) {
                        $result      = $kjvResult;
                        $usedVersion = 'kjv';
                        $transient   = false;
                    }
                } catch (\Exception $e) {
                    // Even KJV failed
                }
            }

            if ($result->hasText()) {
                echo json_encode([
                    'success'     => true,
                    'text'        => $result->text,
                    'copyright'   => $result->copyright,
                    'translation' => $usedVersion,
                    'fallback'    => $usedVersion !== $version,
                ], JSON_THROW_ON_ERROR);
            } else {
                echo json_encode([
                    'success'   => false,
                    'retryable' => $transient,
                    'message'   => 'No passage text returned',
                ], JSON_THROW_ON_ERROR);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success'   => false,
                'retryable' => true,
                'message'   => 'Failed to fetch passage',
            ], JSON_THROW_ON_ERROR);
        }

        $app->close();
    }
}
