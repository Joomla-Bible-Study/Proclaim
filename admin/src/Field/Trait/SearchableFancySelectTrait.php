<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field\Trait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;

/**
 * Switches a ListField to the fancy-select (Choices.js) layout when the
 * field's own `searchable="true"` XML attribute is set — a searchable,
 * tag-style multi-select instead of the native ctrl-click listbox.
 *
 * Used by TeacherListField, MediaPlaylistsField, and PodcastsField, which
 * each carried an identical setup() body for this before extraction here.
 *
 * @since  __DEPLOY_VERSION__
 */
trait SearchableFancySelectTrait
{
    /**
     * Apply the fancy-select layout when the field element opts in.
     *
     * @param   \SimpleXMLElement  $element  The XML element representing the `<field>` tag.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function applySearchableFancySelect(\SimpleXMLElement $element): void
    {
        if ((string) $element['searchable'] !== 'true') {
            return;
        }

        $this->layout = 'joomla.form.field.list-fancy-select';

        // Ensure the Choices.js dropdown is not clipped by parent containers
        // (rules live in topics-field.css).
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_proclaim');
        $wa->useStyle('com_proclaim.topics-field');
    }
}
