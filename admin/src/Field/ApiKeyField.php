<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Library\Scripture\Field\ApiKeyField as LibraryApiKeyField;

/**
 * Backward-compatible wrapper — delegates to the library field.
 *
 * @since    10.1.0
 * @deprecated  10.3.0  Use CWM\Library\Scripture\Field\ApiKeyField directly.
 */
class ApiKeyField extends LibraryApiKeyField
{
}
