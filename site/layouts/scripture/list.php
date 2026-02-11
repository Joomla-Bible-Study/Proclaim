<?php

/**
 * Layout: Scripture reference list
 *
 * Renders multiple scripture references with a configurable separator.
 * Override this layout via Joomla template overrides to customise presentation.
 *
 * @var   array  $displayData  {
 *     @var  string[]  parts      Rendered scripture reference strings
 *     @var  string    separator  Separator key: newline|middot|pipe|semicolon
 * }
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.1.0
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

$parts     = $displayData['parts'] ?? [];
$separator = $displayData['separator'] ?? 'middot';

if (empty($parts)) {
    return;
}

if (\count($parts) === 1) {
    echo $parts[0];

    return;
}

$dot = '<span class="scripture-sep mx-2" aria-hidden="true"><i class="fas fa-circle" style="font-size:0.35em;vertical-align:middle;opacity:0.6;"></i></span>';

switch ($separator) {
    case 'middot':
        echo implode($dot, $parts);
        break;
    case 'pipe':
        echo implode(' <span class="scripture-sep mx-1" aria-hidden="true">|</span> ', $parts);
        break;
    case 'semicolon':
        echo implode('; ', $parts);
        break;
    case 'newline':
    default:
        echo '<span class="scripture-list">' . implode('<br>', $parts) . '</span>';
        break;
}
