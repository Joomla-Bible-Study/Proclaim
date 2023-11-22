<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Books List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.4
 */
class SpanOptionsField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $type = 'SpanOptions';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array   An array of HTMLHelper options.
     *
     * @since 7.0
     */
    protected function getOptions(): array
    {
        $options[] = HTMLHelper::_('select.option', 'None', 0);
        $options[] = HTMLHelper::_('select.option', '1', 1);
        $options[] = HTMLHelper::_('select.option', '2', 2);
        $options[] = HTMLHelper::_('select.option', '3', 3);
        $options[] = HTMLHelper::_('select.option', '4', 4);
        $options[] = HTMLHelper::_('select.option', '5', 5);
        $options[] = HTMLHelper::_('select.option', '6', 6);
        $options[] = HTMLHelper::_('select.option', '7', 7);
        $options[] = HTMLHelper::_('select.option', '8', 8);
        $options[] = HTMLHelper::_('select.option', '9', 9);
        $options[] = HTMLHelper::_('select.option', '10', 10);
        $options[] = HTMLHelper::_('select.option', '11', 11);
        $options[] = HTMLHelper::_('select.option', '12', 12);

        return array_merge(parent::getOptions(), $options);
    }
}
