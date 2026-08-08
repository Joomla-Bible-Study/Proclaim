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
