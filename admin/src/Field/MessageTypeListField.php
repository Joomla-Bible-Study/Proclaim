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

use CWM\Component\Proclaim\Administrator\Helper\CwmfilterHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;

/**
 * Message Type List Form Field class for the Proclaim component
 *
 * On the frontend, only message types used by published, access-filtered
 * messages are shown. On the backend, all published message types are listed.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class MessageTypeListField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 7.0
     */
    protected $type = 'MessageTypeList';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     *
     * @since 7.0
     */
    #[\Override]
    protected function getOptions(): array
    {
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select('DISTINCT ' . $db->quoteName('mt.id') . ', ' . $db->quoteName('mt.message_type'))
            ->from($db->quoteName('#__bsms_message_type', 'mt'))
            ->where($db->quoteName('mt.published') . ' = 1');

        if ($app->isClient('site')) {
            // Frontend: only message types used by published/archived, accessible messages
            $user   = $app->getIdentity();
            $groups = $user->getAuthorisedViewLevels();

            $query->join(
                'INNER',
                $db->quoteName('#__bsms_studies', 's') . ' ON '
                . $db->quoteName('s.messagetype') . ' = ' . $db->quoteName('mt.id')
            )
                ->whereIn($db->quoteName('s.published'), [1, 2])
                ->whereIn($db->quoteName('s.access'), $groups);

            CwmfilterHelper::applyCrossFilters($query, 'messagetype');
        }

        $query->order($db->quoteName('mt.message_type'));
        $db->setQuery($query);
        $messages = $db->loadObjectList();
        $options  = [];

        if ($messages) {
            foreach ($messages as $message) {
                $options[] = HTMLHelper::_('select.option', $message->id, $message->message_type);
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
