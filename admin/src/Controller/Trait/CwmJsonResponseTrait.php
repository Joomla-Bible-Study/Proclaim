<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller\Trait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Log\Log;

/**
 * Send a uniform {success, message, data} JSON response and terminate.
 *
 * The single implementation shared by CwmassetsController,
 * CwmlocationwizardController, CwmsetupwizardController,
 * CwmanalyticsController and CwmbackupController, which each need the same
 * envelope. It captures and logs stray output -- PHP notices and warnings that
 * would otherwise corrupt the JSON body -- guards header calls with
 * headers_sent(), and exits immediately rather than routing through Joomla's
 * shutdown
 * processing.
 *
 * Only the plumbing was unified — every consuming controller already
 * produced this exact envelope shape, so no JS consumer is affected.
 *
 * @since  10.5.6
 */
trait CwmJsonResponseTrait
{
    /**
     * Send a response body exactly as the caller shaped it.
     *
     * The sibling sendJsonResponse() imposes a {success, message, data}
     * envelope. Most of this component's AJAX predates it and answers with flat,
     * endpoint-specific keys that the JavaScript reads directly — reshaping
     * those would mean changing every consumer in lockstep. This gives them the
     * parts that are not about shape: the throw-on-failure flag, the JSON
     * headers, stray-output capture, and a log line when encoding fails.
     *
     * ⚠️ Unlike sendJsonResponse() this does NOT terminate. Callers already
     * close the application themselves, and keeping that in their hands is what
     * lets the body be swapped in without touching any surrounding control flow.
     *
     * @param   array  $payload  The response body, sent as-is.
     *
     * @return  void
     *
     * @throws  \JsonException  If the payload cannot be encoded.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function sendJsonPayload(array $payload): void
    {
        // Capture and log any stray output (PHP errors, warnings, etc.) so it
        // cannot corrupt the JSON body.
        $strayOutput = '';

        while (ob_get_level()) {
            $strayOutput .= ob_get_clean();
        }

        if (!empty($strayOutput)) {
            Log::add('Stray output captured: ' . substr($strayOutput, 0, 500), Log::WARNING, 'com_proclaim');
        }

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
        }

        try {
            echo json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            // Without the flag this produced `false`, i.e. an empty body and a
            // client-side parse error with nothing logged anywhere.
            Log::add('Could not encode the JSON response: ' . $e->getMessage(), Log::ERROR, 'com_proclaim');

            throw $e;
        }
    }

    /**
     * Send JSON response and terminate.
     *
     * @param   bool    $success  Success status
     * @param   string  $message  Message
     * @param   array   $data     Additional data
     *
     * @return never
     *
     * @throws \Exception
     * @since 10.5.6
     */
    private function sendJsonResponse(bool $success, string $message = '', array $data = []): never
    {
        // Capture and log any stray output (PHP errors, warnings, etc.)
        $strayOutput = '';

        while (ob_get_level()) {
            $strayOutput .= ob_get_clean();
        }

        if (!empty($strayOutput)) {
            Log::add('Stray output captured: ' . substr($strayOutput, 0, 500), Log::WARNING, 'com_proclaim');
        }

        // Set JSON headers directly (only if headers not already sent)
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
        }

        $response = [
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ];

        echo json_encode($response, JSON_THROW_ON_ERROR);

        // Use exit instead of $app->close() to avoid any shutdown processing issues
        exit(0);
    }
}
