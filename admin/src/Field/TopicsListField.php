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
use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;

/**
 * Topic List Form Field class for the Proclaim component
 *
 * Displays only topics associated with published studies.
 * On the frontend, access-level filtering is applied.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class TopicsListField extends ListField
{
    /**
     * The field type.
     *
     * @var  string
     *
     * @since 9.0.0
     */
    protected $type = 'TopicsList';

    /**
     * Method to get a list of options for a list input.
     *
     * @return  array  An array of JHtml options.
     *
     * @throws \Exception
     * @since 9.0.0
     */
    #[\Override]
    protected function getOptions(): array
    {
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(
            'DISTINCT ' . $db->quoteName('t.id') . ', '
            . $db->quoteName('t.topic_text') . ', '
            . $db->quoteName('t.params', 'topic_params')
        )
            ->from($db->quoteName('#__bsms_topics', 't'))
            ->join(
                'INNER',
                $db->quoteName('#__bsms_studytopics', 'st') . ' ON '
                . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.topic_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__bsms_studies', 's') . ' ON '
                . $db->quoteName('s.id') . ' = ' . $db->quoteName('st.study_id')
            )
            ->where($db->quoteName('t.published') . ' = 1')
            ->whereIn($db->quoteName('s.published'), [1, 2]);

        if ($app->isClient('site')) {
            $user   = $app->getIdentity();
            $groups = $user->getAuthorisedViewLevels();
            $query->whereIn($db->quoteName('s.access'), $groups);

            CwmfilterHelper::applyCrossFilters($query, 'topic');
        }

        $query->order($db->quoteName('t.topic_text') . ' ASC');
        $db->setQuery($query);
        $topics  = $db->loadObjectList() ?: [];
        $options = [];

        foreach ($topics as $topic) {
            $text      = Cwmtranslated::getTopicItemTranslated($topic);
            $options[] = HTMLHelper::_('select.option', $topic->id, $text);
        }

        // Sort after translation to maintain alphabetical order
        usort($options, static fn (object $a, object $b): int => strcmp($a->text, $b->text));

        return array_merge(parent::getOptions(), $options);
    }
}
