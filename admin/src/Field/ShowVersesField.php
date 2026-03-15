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

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Show Verses dropdown — single source of truth for verse display options.
 *
 * Used in template.xml and mod_proclaim.xml.
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class ShowVersesField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.2.0
     */
    protected $type = 'ShowVerses';

    /**
     * Get the field options.
     *
     * @return  array  Array of HTMLHelper option objects
     *
     * @since   10.2.0
     */
    protected function getOptions(): array
    {
        $options   = parent::getOptions();
        $options[] = HTMLHelper::_('select.option', '0', Text::_('JBS_TPL_SHOW_ONLY_CHAPTERS'));
        $options[] = HTMLHelper::_('select.option', '1', Text::_('JBS_TPL_SHOW_VERSES_AND_CHAPTERS'));
        $options[] = HTMLHelper::_('select.option', '2', Text::_('JBS_TPL_SHOW_ONLY_BOOKS'));

        return $options;
    }
}
