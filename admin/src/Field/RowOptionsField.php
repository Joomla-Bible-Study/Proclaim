<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
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
 * Books List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.4
 */
class RowOptionsField extends ListField
{
    /**
     * The field type.
     *
     * @var         string
     *
     * @since 7.0
     */
    protected $type = 'RowOptions';

    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of JHtml options.
     *
     * @since 7.0
     */
    protected function getOptions(): array
    {
        $options[] = HTMLHelper::_('select.option', '0', Text::_('JBS_CMN_HIDE'));
        $options[] = HTMLHelper::_('select.option', '1', Text::_('JBS_TPL_ROW1'));
        $options[] = HTMLHelper::_('select.option', '2', Text::_('JBS_TPL_ROW2'));
        $options[] = HTMLHelper::_('select.option', '3', Text::_('JBS_TPL_ROW3'));
        $options[] = HTMLHelper::_('select.option', '4', Text::_('JBS_TPL_ROW4'));
        $options[] = HTMLHelper::_('select.option', '5', Text::_('JBS_TPL_ROW5'));
        $options[] = HTMLHelper::_('select.option', '6', Text::_('JBS_TPL_ROW6'));

        return array_merge(parent::getOptions(), $options);
    }
}
