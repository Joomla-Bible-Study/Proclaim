<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.HtmlHelper GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Topic List Form Field class for the Proclaim component
 * Displays a topics list of ALL published topics
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
     * @return  array  An array of JHtmlHelper options.
     *
     * @throws \Exception
     * @since 9.0.0
     */
    #[\Override]
    protected function getOptions(): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(
            'DISTINCT ' . $db->qn('#__bsms_topics.id') . ', '
                . $db->qn('#__bsms_topics.topic_text') . ', '
                . $db->qn('#__bsms_topics.params', 'topic_params')
        )
            ->from($db->qn('#__bsms_studies'))
            ->leftJoin(
                $db->qn('#__bsms_studytopics') . ' ON '
                    . $db->qn('#__bsms_studies.id') . ' = ' . $db->qn('#__bsms_studytopics.study_id')
            )
            ->leftJoin(
                $db->qn('#__bsms_topics') . ' ON '
                    . $db->qn('#__bsms_topics.id') . ' = ' . $db->qn('#__bsms_studytopics.topic_id')
            )
            ->where($db->qn('#__bsms_topics.published') . ' = 1')
            ->order($db->qn('#__bsms_topics.topic_text') . ' ASC');
        $db->setQuery($query);
        $topics  = $db->loadObjectList();
        $options = [];

        if ($topics) {
            foreach ($topics as $topic) {
                $text      = Cwmtranslated::getTopicItemTranslated($topic);
                $options[] = HTMLHelper::_('select.option', $topic->id, $text);
            }
        }

        // Sort the Topics after Translation to Alphabetically
        usort($options, [$this, "orderNew"]);

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Order New using strcmp
     *
     * @param   object  $a  Start.
     * @param   object  $b  End.
     *
     * @return int Used to place in the new sort.
     *
     * @since 7.0
     */
    private function orderNew(object $a, object $b): int
    {
        $a = (array)$a;
        $b = (array)$b;

        return strcmp($a["text"], $b["text"]);
    }
}
