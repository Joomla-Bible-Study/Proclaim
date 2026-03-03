<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmpodcastPlatformHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Custom list field that reads podcast platform options from podcast-platforms.xml.
 *
 * Adding a new platform = editing the XML file. No PHP changes needed.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class PodcastPlatformField extends ListField
{
    /**
     * The form field type.
     *
     * @var string
     * @since 10.1.0
     */
    protected $type = 'PodcastPlatform';

    /**
     * Build the option list from the platform definitions XML.
     *
     * @return  array  Array of HTMLHelper option objects.
     *
     * @since   10.1.0
     */
    protected function getOptions(): array
    {
        $platforms = CwmpodcastPlatformHelper::getPlatformKeys();
        $options   = [];

        foreach ($platforms as $p) {
            $options[] = HTMLHelper::_('select.option', $p['key'], Text::_($p['label_key']));
        }

        return array_merge(parent::getOptions(), $options);
    }
}
