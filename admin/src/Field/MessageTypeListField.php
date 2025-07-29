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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Message Type List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class MessageTypeListField extends ListField
{
    /**
     * The field type.
     *
     * @var         string
     *
     * @since 7.0
     */
    protected $type = 'MessageTypeList';

    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of JHtml options.
     *
     * @since 7.0
     */
    protected function getOptions(): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('id,message_type');
        $query->from('#__bsms_message_type');
        $query->where('published = 1');
        $query->order('message_type');
        $db->setQuery((string)$query);
        $messages = $db->loadObjectList();
        $options  = array();

        if ($messages) {
            foreach ($messages as $message) {
                $options[] = HtmlHelper::_('select.option', $message->id, $message->message_type);
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
