<?php

/**
 * Integration Toggle Form Field
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

use Joomla\CMS\Form\Field\RadioField;
use Joomla\CMS\Language\Text;

/**
 * Integration Toggle Field - Radio switcher with extension detection
 *
 * Checks if the specified extension component is installed and disables
 * the toggle if not found. Shows installation status to the user.
 *
 * XML attributes:
 * - extension: The component folder name to check (e.g., "com_virtuemart")
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class IntegrationToggleField extends RadioField
{
    /**
     * The field type.
     *
     * @var string
     * @since 10.2.0
     */
    protected $type = 'IntegrationToggle';

    /**
     * The extension component to check for.
     *
     * @var string
     * @since 10.2.0
     */
    protected $extension = '';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the field tag.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     *
     * @return  boolean  True on success.
     *
     * @since   10.2.0
     */
    #[\Override]
    public function setup(\SimpleXMLElement $element, $value, $group = null): bool
    {
        $result = parent::setup($element, $value, $group);

        if ($result) {
            $this->extension = (string) $this->element['extension'];
        }

        return $result;
    }

    /**
     * Check if the extension is installed.
     *
     * @return boolean True if installed, false otherwise.
     *
     * @since 10.2.0
     */
    protected function isExtensionInstalled(): bool
    {
        if (empty($this->extension)) {
            return true;
        }

        return is_dir(JPATH_ADMINISTRATOR . '/components/' . $this->extension);
    }

    /**
     * Method to get the field input markup.
     *
     * @return string The field input markup.
     *
     * @since 10.2.0
     */
    #[\Override]
    protected function getInput(): string
    {
        $isInstalled = $this->isExtensionInstalled();

        // If extension not installed, disable the field and force value to 0
        if (!$isInstalled) {
            $this->disabled = true;
            $this->value    = '0';
        }

        // Get the parent radio input
        $html = parent::getInput();

        // Add status badge
        $statusClass = $isInstalled ? 'bg-success' : 'bg-secondary';
        $statusText  = $isInstalled
            ? Text::_('JBS_ADM_INTEGRATION_DETECTED')
            : Text::_('JBS_ADM_INTEGRATION_NOT_INSTALLED');

        $badge = '<span class="badge ' . $statusClass . ' ms-2">' . $statusText . '</span>';

        return $html . $badge;
    }

    /**
     * Method to get the field label markup.
     *
     * @return string The field label markup.
     *
     * @since 10.2.0
     */
    #[\Override]
    protected function getLabel(): string
    {
        $label = parent::getLabel();

        // If not installed, add visual indicator to label
        if (!$this->isExtensionInstalled()) {
            $label = str_replace('</label>', ' <em class="text-muted">(' . Text::_('JBS_ADM_UNAVAILABLE') . ')</em></label>', $label);
        }

        return $label;
    }
}
