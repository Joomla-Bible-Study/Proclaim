<?php

/**
 * Popup player layout — renders the appropriate player based on type.
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 *
 * Expected $displayData keys:
 *   - player   (int)      Player type (0=redirect, 1=addon/audio, 2=internal, 8=embed)
 *   - path     (string)   Media URL
 *   - params   (Registry) Merged params
 *   - media    (object)   Media row
 *   - width    (string)   Player width
 *   - height   (string)   Player height
 *   - getMedia (Cwmmedia) Media helper instance
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$player   = (int) ($displayData['player'] ?? 1);
$path     = (string) ($displayData['path'] ?? '');
$params   = $displayData['params'];
$media    = $displayData['media'];
$width    = (string) ($displayData['width'] ?? '');
$height   = (string) ($displayData['height'] ?? '');
$getMedia = $displayData['getMedia'];

// Internal viewer / media code player
if ($player === 2) {
    $mediaCode = $getMedia->getAVmediacode($media->mediacode, $media);
    echo HTMLHelper::_('content.prepare', $mediaCode);

    return;
}

// Addon-owned player (YouTube, Vimeo, etc.) or HTML audio fallback
if ($player === 1) {
    $addon = CWMAddon::resolveForUrl($path);

    if ($addon) {
        echo $addon->renderPopupPlayer($path, $params, $width, $height);
    } else {
        echo CWMAddon::renderDirectPopupPlayer($path);
    }

    return;
}

// Raw embed code
if ($player === 8) {
    echo HTMLHelper::_('content.prepare', (string) $params->get('mediacode', ''));

    return;
}

// Direct link — redirect and close
if ($player === 0) {
    $app = \Joomla\CMS\Factory::getApplication();
    $app->redirect(Route::_($path));
}
