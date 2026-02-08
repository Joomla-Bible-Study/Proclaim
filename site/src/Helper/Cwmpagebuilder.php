<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// No Direct Access
use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use CWM\Component\Proclaim\Administrator\Table\CwmtemplateTable;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class to build page elements in use by custom template files
 *
 * @since  7.0.1
 */
class Cwmpagebuilder
{
    /** @var string Extension Name
     * @since 7.0
     */
    public string $extension = 'com_proclaim';

    /** @var  string Event
     * @since 7.0
     */
    public string $event;

    /**
     * Build Page
     *
     * @param   object            $item      Item info
     * @param   Registry          $params    Item Params
     * @param   CwmtemplateTable  $template  Template data
     *
     * @return object
     *
     * @throws \Exception
     * @since 7.0
     */
    public function buildPage($item, $params, $template): object
    {
        $item->tp_id = '1';

        // Media files image, links, download
        $mids        = $item->mids;
        $page        = new \stdClass();
        $CWMElements = new Cwmlisting();

        if ($mids) {
            // Build media files inline (was mediaBuilder)
            $mediaIDs         = $CWMElements->getFluidMediaids($item);
            $media            = $CWMElements->getMediaFiles($mediaIDs);
            $item->mediafiles = $media;
            $page->media      = $CWMElements->getFluidMediaFiles($item, $params, $template);
        } else {
            $page->media = '';
        }

        // Scripture1
        $esv          = 0;
        $scripturerow = 1;

        if ($item->chapter_begin) {
            $page->scripture1 = $CWMElements->getScripture($params, $item, $esv, $scripturerow);
        } else {
            $page->scripture1 = '';
        }

        if (!$item->secondary_reference) {
            $item->secondary_reference = '';
        }

        // Scripture 2
        $scripturerow = 2;

        if ($item->booknumber2 >= 1) {
            $page->scripture2 = $CWMElements->getScripture($params, $item, $esv, $scripturerow);
        } else {
            $page->scripture2 = '';
        }

        // Study Date
        $page->studydate = $CWMElements->getStudyDate($params, $item->studydate);

        // Translate Topics.
        $item->topics_text = Cwmtranslated::getConcatTopicItemTranslated($item);

        if (isset($item->topics_text) && (substr_count($item->topics_text, ',') > 0)) {
            $topics = explode(',', $item->topics_text);

            foreach ($topics as $key => $value) {
                $topics[$key] = Text::_($value);
            }

            $page->topics = implode(', ', $topics);
        } else {
            $page->topics = Text::_($item->topics_text);
        }

        if ($item->thumbnailm) {
            $image                 = Cwmimages::getStudyThumbnail($item->thumbnailm);
            $page->study_thumbnail = '<img src="' . Uri::base(
            ) . $image->path . '" width="' . $image->width . '" height="' . $image->height
                . '" alt="' . $item->studytitle . '" />';
        } else {
            $page->study_thumbnail = '';
        }

        if ($item->series_thumbnail) {
            $image                  = Cwmimages::getSeriesThumbnail($item->series_thumbnail);
            $page->series_thumbnail = '<img src="' . Uri::base(
            ) . $image->path . '" width="' . $image->width . '" height="' . $image->height
                . '" alt="' . $item->series_text . '" />';
        } else {
            $page->series_thumnail = '';
        }

        $page->detailslink = Route::_(
            'index.php?option=com_proclaim&view=cwmsermon&id=' . $item->slug . '&t=' . $params->get('detailstemplateid')
        );

        if (!isset($item->image)) {
            $item->image = '';
        }

        if (!isset($item->thumb)) {
            $item->thumb = '';
        }

        if ($item->image || $item->thumb) {
            $image              = Cwmimages::getTeacherImage($item->image, $item->thumb);
            $page->teacherimage = '<img src="' . Uri::base(
            ) . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="'
                . $item->teachername . '" />';
        } else {
            $page->teacherimage = '';
        }

        // Study Text
        if (!isset($item->studytext)) {
            $item->studytext = '';
        }

        if (!isset($item->secondary_reference)) {
            $item->secondary_reference = '';
        }

        if (!isset($item->sdescription)) {
            $item->sdescription = '';
        }

        if ($params->get('show_scripture_link') === 0) {
            return $page;
        }

        // Set the item for the plugin to $item->text //run content plugins
        if ($page->scripture1) {
            $item->text       = $page->scripture1;
            $item             = $this->runContentPlugins($item, $params);
            $page->scripture1 = $item->text;
        }

        if ($page->scripture2) {
            $item->text       = $page->scripture2;
            $item             = $this->runContentPlugins($item, $params);
            $page->scripture2 = $item->text;
        }

        if ($item->studyintro) {
            $item->text       = $item->studyintro;
            $item             = $this->runContentPlugins($item, $params);
            $page->studyintro = $item->text;
        }

        if ($item->studytext) {
            $item->text      = $item->studytext;
            $item            = $this->runContentPlugins($item, $params);
            $page->studytext = $item->text;
        }

        if ($item->secondary_reference) {
            $item->text                = $item->secondary_reference;
            $item                      = $this->runContentPlugins($item, $params);
            $page->secondary_reference = $item->text;
        }

        if ($item->sdescription) {
            $item->text         = $item->sdescription;
            $item               = $this->runContentPlugins($item, $params);
            $page->sdescription = $item->text;
        }

        return $page;
    }

    /**
     * Study Builder
     *
     * @param   string            $whereitem   ?
     * @param   string            $wherefield  ?
     * @param   Registry          $params      Item params
     * @param   int               $limit       Limit of Records
     * @param   string            $order       DESC or ASC
     * @param   CwmtemplateTable  $template    Template Data
     *
     * @return array
     *
     * @throws \Exception
     * @since 7.0
     */
    public function studyBuilder(
        $whereitem = null,
        $wherefield = null,
        $params = null,
        $limit = 10,
        $order = 'DESC',
        $template = null
    ): array {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $orderparam = $params->get('order', '1');

        if ($orderparam === 2) {
            $order = "ASC";
        }

        // Compute view access permissions.
        $user   = \Joomla\CMS\Factory::getApplication()->getIdentity();
        $groups = implode(',', $user->getAuthorisedViewLevels());

        $query = $db->getQuery(true);
        $nullDateQuoted = $db->quote($db->getNullDate());
        $query->select(implode(', ', $db->quoteName([
            'study.id', 'study.published', 'study.studydate', 'study.studytitle',
            'study.booknumber', 'study.chapter_begin', 'study.verse_begin',
            'study.chapter_end', 'study.verse_end', 'study.hits', 'study.alias',
            'study.studyintro', 'study.teacher_id', 'study.secondary_reference',
            'study.booknumber2', 'study.location_id',
        ])));
        // Use studydate as fallback for modified
        $query->select(
            'CASE WHEN ' . $db->quoteName('study.modified') . ' = ' . $nullDateQuoted
            . ' THEN ' . $db->quoteName('study.studydate') . ' ELSE ' . $db->quoteName('study.modified')
            . ' END AS ' . $db->quoteName('modified')
        );
        $query->select($db->quoteName('study.modified_by') . ', ' . $db->quoteName('uam.name', 'modified_by_name'));
        // Use studydate as fallback for publish_up
        $query->select(
            'CASE WHEN ' . $db->quoteName('study.publish_up') . ' = ' . $nullDateQuoted
            . ' THEN ' . $db->quoteName('study.studydate') . ' ELSE ' . $db->quoteName('study.publish_up')
            . ' END AS ' . $db->quoteName('publish_up')
        );
        $query->select(implode(', ', $db->quoteName([
            'study.publish_down', 'study.series_id', 'study.download_id',
            'study.thumbnailm', 'study.thumbhm', 'study.thumbwm',
            'study.access', 'study.user_name', 'study.user_id', 'study.studynumber',
            'study.chapter_begin2', 'study.chapter_end2', 'study.verse_end2', 'study.verse_begin2',
        ])));
        $query->select($query->length($db->quoteName('study.studytext')) . ' AS ' . $db->quoteName('readmore'));
        $query->select(
            'CASE WHEN CHAR_LENGTH(' . $db->quoteName('study.alias') . ') THEN CONCAT_WS('
            . $db->quote(':') . ', ' . $db->quoteName('study.id') . ', ' . $db->quoteName('study.alias')
            . ') ELSE ' . $db->quoteName('study.id') . ' END AS ' . $db->quoteName('slug')
        );
        $query->from($db->quoteName('#__bsms_studies', 'study'));

        // Join over Message Types
        $query->select($db->quoteName('messageType.message_type', 'message_type'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_message_type', 'messageType') . ' ON '
            . $db->quoteName('messageType.id') . ' = ' . $db->quoteName('study.messagetype')
        );

        // Join over Teachers
        $query->select(
            $db->quoteName('teacher.teachername', 'teachername') . ', '
            . $db->quoteName('teacher.title', 'teachertitle') . ', '
            . $db->quoteName('teacher.thumb') . ', ' . $db->quoteName('teacher.thumbh') . ', '
            . $db->quoteName('teacher.thumbw')
        );
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_teachers', 'teacher') . ' ON '
            . $db->quoteName('teacher.id') . ' = ' . $db->quoteName('study.teacher_id')
        );

        // Join over Series
        $query->select(
            $db->quoteName('series.series_text') . ', ' . $db->quoteName('series.series_thumbnail') . ', '
            . $db->quoteName('series.description', 'sdescription') . ', ' . $db->quoteName('series.access')
        );
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_series', 'series') . ' ON '
            . $db->quoteName('series.id') . ' = ' . $db->quoteName('study.series_id')
        );

        // Join over Books
        $query->select($db->quoteName('book.bookname'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_books', 'book') . ' ON '
            . $db->quoteName('book.booknumber') . ' = ' . $db->quoteName('study.booknumber')
        );

        $query->select($db->quoteName('book2.bookname', 'bookname2'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_books', 'book2') . ' ON '
            . $db->quoteName('book2.booknumber') . ' = ' . $db->quoteName('study.booknumber2')
        );

        // Join over Plays/Downloads
        $query->select(
            'GROUP_CONCAT(DISTINCT ' . $db->quoteName('mediafile.id') . ') AS ' . $db->quoteName('mids') . ', '
            . 'SUM(' . $db->quoteName('mediafile.plays') . ') AS ' . $db->quoteName('totalplays') . ', '
            . 'SUM(' . $db->quoteName('mediafile.downloads') . ') AS ' . $db->quoteName('totaldownloads') . ', '
            . $db->quoteName('mediafile.study_id')
        );
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_mediafiles', 'mediafile') . ' ON '
            . $db->quoteName('mediafile.study_id') . ' = ' . $db->quoteName('study.id')
        );

        // Join over Locations
        $query->select($db->quoteName('locations.location_text'));
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_locations', 'locations') . ' ON '
            . $db->quoteName('study.location_id') . ' = ' . $db->quoteName('locations.id')
        );

        // Join over studytopics
        $query->select('GROUP_CONCAT(DISTINCT ' . $db->quoteName('st.topic_id') . ')');
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_studytopics', 'st') . ' ON '
            . $db->quoteName('study.id') . ' = ' . $db->quoteName('st.study_id')
        );
        $query->select(
            'GROUP_CONCAT(DISTINCT ' . $db->quoteName('t.id') . '), '
            . 'GROUP_CONCAT(DISTINCT ' . $db->quoteName('t.topic_text') . ') AS ' . $db->quoteName('topic_text') . ', '
            . 'GROUP_CONCAT(DISTINCT ' . $db->quoteName('t.params') . ') AS ' . $db->quoteName('topic_params')
        );
        $query->join(
            'LEFT',
            $db->quoteName('#__bsms_topics', 't') . ' ON '
            . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.topic_id')
        );

        // Join over the users for the author and modified_by names.
        $query->select(
            'CASE WHEN ' . $db->quoteName('study.user_name') . ' > ' . $db->quote(' ')
            . ' THEN ' . $db->quoteName('study.user_name') . ' ELSE ' . $db->quoteName('users.name')
            . ' END AS ' . $db->quoteName('submitted')
        )
            ->select($db->quoteName('users.email', 'author_email'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'users') . ' ON '
                . $db->quoteName('study.user_id') . ' = ' . $db->quoteName('users.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uam') . ' ON '
                . $db->quoteName('uam.id') . ' = ' . $db->quoteName('study.modified_by')
            );

        $query->group($db->quoteName('study.id'));

        // Select only published studies
        $query->where($db->quoteName('study.published') . ' = 1');
        $query->where('(' . $db->quoteName('series.published') . ' = 1 OR ' . $db->quoteName('study.series_id') . ' <= 0)');

        if ($wherefield && $whereitem) {
            $query->where($wherefield . ' = ' . $whereitem);
        }

        // Define null and now dates
        $nullDate = $db->quote($db->getNullDate());
        $nowDate  = $db->quote((new Date())->toSql());

        // Filter by start and end dates.
        if (
            (!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise(
                'core.edit',
                'com_proclaim'
            ))
        ) {
            $query->where('(' . $db->quoteName('study.publish_up') . ' = ' . $nullDate . ' OR ' . $db->quoteName('study.publish_up') . ' <= ' . $nowDate . ')')
                ->where('(' . $db->quoteName('study.publish_down') . ' = ' . $nullDate . ' OR ' . $db->quoteName('study.publish_down') . ' >= ' . $nowDate . ')');
        }

        // Filter by language
        $language = $params->get('language', '*');

        if ($language === '*') {
            $query->where($db->quoteName('study.language') . ' IN (' . $db->quote($language) . ',' . $db->quote('*') . ')');
        } elseif ($language !== '*') {
            $query->where(
                $db->quoteName('study.language') . ' IN (' . $db->quote(Factory::getApplication()->getLanguage()->getTag()) . ',' . $db->quote('*') . ')'
            );
        }

        $query->order($db->quoteName('studydate') . ' ' . $order);

        // Filter only for authorized view
        $query->where('(' . $db->quoteName('series.access') . ' IN (' . $groups . ') OR ' . $db->quoteName('study.series_id') . ' <= 0)');
        $query->where($db->quoteName('study.access') . ' IN (' . $groups . ')');

        $db->setQuery($query, 0, $limit);

        return $db->loadObjectList();
    }

    /**
     * Run content plugins on item text
     *
     * @param   object  $item    Item with text property to process
     * @param   object  $params  Component params
     *
     * @return object The item with processed text and event properties
     *
     * @throws \Exception
     * @since 10.0.0
     */
    public function runContentPlugins(object $item, object $params): object
    {
        // We don't need offset, but it is a required argument for the plugin dispatcher
        $offset = 0;
        PluginHelper::importPlugin('content');

        // Run content plugins
        $dispatcher            = Factory::getApplication();
        $contentEventArguments = [
            'context' => 'com_proclaim.sermon',
            'subject' => &$item,
            'params'  => &$params,
            'page'    => $offset,
        ];

        $dispatcher->triggerEvent('onContentPrepare', $contentEventArguments);

        $item->event                        = new \stdClass();
        $results                            = $dispatcher->triggerEvent('onContentAfterTitle', $contentEventArguments);
        $item->event->afterDisplayTitle     = trim(implode("\n", $results));

        $results                            = $dispatcher->triggerEvent('onContentBeforeDisplay', $contentEventArguments);
        $item->event->beforeDisplayContent  = trim(implode("\n", $results));

        $results                            = $dispatcher->triggerEvent('onContentAfterDisplay', $contentEventArguments);
        $item->event->afterDisplayContent   = trim(implode("\n", $results));

        return $item;
    }
}
