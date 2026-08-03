<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Library\Scripture\Bible\AbstractBibleProvider;
use CWM\Library\Scripture\Bible\BibleProviderFactory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Passage lookup with provider fallback for the scripture AJAX endpoint.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmscriptureLookupHelper
{
    /**
     * Fetch a passage, falling back through local providers when the
     * configured provider has no text for the requested translation.
     *
     * Fallback order: configured provider → local provider (same version)
     * → local provider (admin default version) → local provider (KJV,
     * bundled and always available).
     *
     * @param   string    $reference        Scripture reference (e.g. "John 3:16")
     * @param   string    $version          Requested translation code
     * @param   Registry  $scriptureParams  Scripture library params (cache_days, default_version)
     *
     * @return  array  Response payload, ready to serialize as the AJAX response
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function lookupPassage(string $reference, string $version, Registry $scriptureParams): array
    {
        try {
            AbstractBibleProvider::registerLogger();
            $provider  = BibleProviderFactory::getProviderForTranslation($version, $scriptureParams);
            $cacheDays = (int) $scriptureParams->get('cache_days', 30);

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
                $defaultVersion = (string) $scriptureParams->get('default_version', 'kjv');

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
                return [
                    'success'     => true,
                    'text'        => $result->text,
                    'copyright'   => $result->copyright,
                    'translation' => $usedVersion,
                    'fallback'    => $usedVersion !== $version,
                    'provider'    => $providerName,
                    'requested'   => $version,
                ];
            }

            return [
                'success'   => false,
                'retryable' => $transient,
                'message'   => Text::_('COM_PROCLAIM_SCRIPTURE_AJAX_NO_PASSAGE_TEXT'),
                'provider'  => $providerName,
                'requested' => $version,
            ];
        } catch (\Throwable $e) {
            return [
                'success'   => false,
                'retryable' => false,
                'message'   => Text::sprintf('COM_PROCLAIM_SCRIPTURE_AJAX_PROVIDER_ERROR', $e->getMessage()),
                'requested' => $version,
            ];
        }
    }
}
