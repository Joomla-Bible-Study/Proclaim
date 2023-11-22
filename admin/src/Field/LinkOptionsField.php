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
use Joomla\CMS\Language\Text;

/**
 * Books List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.4
 */
class LinkOptionsField extends ListField
{
    /**
     * The field type.
     *
     * @var         string
     *
     * @since 7.0
     */
    protected $type = 'LinkOptions';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of HTMLHelper options.
     *
     * @since 7.0
     */
    protected function getOptions(): array
    {
        $options[] = HTMLHelper::_('select.option', '0', Text::_('JBS_TPL_NO_LINK'));
        $options[] = HTMLHelper::_('select.option', '1', Text::_('JBS_TPL_LINK_TO_DETAILS'));
        $options[] = HTMLHelper::_('select.option', '4', Text::_('JBS_TPL_LINK_TO_DETAILS_TOOLTIP'));
        $options[] = HTMLHelper::_('select.option', '2', Text::_('JBS_TPL_LINK_TO_MEDIA'));
        $options[] = HTMLHelper::_('select.option', '9', Text::_('JBS_TPL_LINK_TO_DOWNLOAD'));
        $options[] = HTMLHelper::_('select.option', '5', Text::_('JBS_TPL_LINK_TO_MEDIA_TOOLTIP'));
        $options[] = HTMLHelper::_('select.option', '3', Text::_('JBS_TPL_LINK_TO_TEACHERS_PROFILE'));
        $options[] = HTMLHelper::_('select.option', '6', Text::_('JBS_TPL_LINK_TO_FIRST_ARTICLE'));
        $options[] = HTMLHelper::_('select.option', '7', Text::_('JBS_TPL_LINK_TO_VIRTUEMART'));
        $options[] = HTMLHelper::_('select.option', '8', Text::_('JBS_TPL_LINK_TO_DOCMAN'));

        return array_merge(parent::getOptions(), $options);
    }
}
