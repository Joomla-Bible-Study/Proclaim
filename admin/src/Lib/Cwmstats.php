<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Lib;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * Bible Study stats support class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class Cwmstats
{
    /** @var int used to store query of messages
     *
     * @since 9.0.0
     */
    private static int $total_messages = 0;

    /** @var string Start Date
     *
     * @since 9.0.0
     */
    private static string $total_messages_start = '';

    /** @var string End Date
     *
     * @since 9.0.0
     */
    private static string $total_messages_end = '';

    /**
     * Total plays of media files per study
     *
     * @param   int  $id  ID number of study
     *
     * @return int Total plays form the media
     *
     * @since 9.0.0
     */
    public static function totalPlays(int $id): int
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('sum(m.plays), m.study_id, m.published, s.id FROM #__bsms_mediafiles AS m')
            ->leftJoin('#__bsms_studies AS s ON (m.study_id = s.id)')
            ->where('m.study_id = ' . $db->q($id));
        $db->setQuery($query);
        $plays = $db->loadResult();

        return (int)$plays;
    }

    /**
     * Total messages in Bible Study
     *
     * @param   string  $start  Start Time/Date of Study
     * @param   string  $end    End Time/Date of Study
     *
     * @return int Total Messages
     *
     * @since 9.0.0
     */
    public static function getTotalMessages(string $start = '', string $end = ''): int
    {
        if ($start !== self::$total_messages_start || $end !== self::$total_messages_end || !self::$total_messages) {
            self::$total_messages_start = $start;
            self::$total_messages_end   = $end;

            $db    = Factory::getContainer()->get('DatabaseDriver');
            $where = array();

            if (!empty($start)) {
                $where[] = 'time > UNIX_TIMESTAMP(\'' . $start . '\')';
            }

            if (!empty($end)) {
                $where[] = 'time < UNIX_TIMESTAMP(\'' . $end . '\')';
            }

            $query = $db->getQuery(true);
            $query
                ->select('COUNT(*)')
                ->from('#__bsms_studies')
                ->where('published =' . $db->q('1'));

            if (count($where) > 0) {
                $query->where(implode(' AND ', $where));
            }

            $db->setQuery($query);
            self::$total_messages = (int)$db->loadResult();
        }

        return self::$total_messages;
    }

    /**
     * Total topics in Bible Study
     *
     * @param   string  $start  Start Time/Date of Study
     * @param   string  $end    End Time/Date of Study
     *
     * @return int  Total Topics
     *
     * @since 9.0.0
     */
    public static function getTotalTopics(string $start = '', string $end = ''): int
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('COUNT(*)')
            ->from('#__bsms_studies')
            ->leftJoin('#__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)')
            ->leftJoin('#__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)')
            ->where('#__bsms_topics.published = ' . $db->q('1'));

        if (!empty($start)) {
            $query->where('time > UNIX_TIMESTAMP(\'' . $start . '\')');
        }

        if (!empty($end)) {
            $query->where('time < UNIX_TIMESTAMP(\'' . $end . '\')');
        }

        $db->setQuery($query);

        return (int)$db->loadResult();
    }

    /**
     * Get top studies
     *
     * @return string HTML Format
     *
     * @since 9.0.0
     */
    public static function getTopStudies(): string
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('id, studytitle, studydate, hits')
            ->from('#__bsms_studies')
            ->where('published = ' . $db->q('1'))
            ->where('hits > ' . $db->q('0'))
            ->order('hits desc');
        $db->setQuery($query, 0, 1);
        $results     = $db->loadObjectList();
        $top_studies = '';

        foreach ($results as $result) {
            $top_studies .= $result->hits . ' ' . Text::_('JBS_CMN_HITS') .
                ' - <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;id=' . $result->id . '">' .
                $result->studytitle . '</a> - ' . date('Y-m-d', strtotime($result->studydate)) . '<br>';
        }

        return $top_studies;
    }

    /**
     * Total comments
     *
     * @return int
     *
     * @since 9.0.0
     */
    public static function getTotalComments(): int
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('COUNT(*)')
            ->from('#__bsms_comments')
            ->where('published = ' . $db->q('1'));
        $db->setQuery($query);

        return (int)$db->loadResult();
    }

    /**
     * Get top thirty days
     *
     * @return string HTML Format
     *
     * @since 9.0.0
     */
    public static function getTopThirtyDays(): string
    {
        $month      = mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"));
        $last_month = date("Y-m-d 00:00:01", $month);
        $db         = Factory::getContainer()->get('DatabaseDriver');
        $query      = $db->getQuery(true);
        $query
            ->select('id, studytitle, studydate, hits')
            ->from('#__bsms_studies')
            ->where('published = ' . $db->q('1'))
            ->where('hits > ' . $db->q('0'))
            ->where('UNIX_TIMESTAMP(studydate) > UNIX_TIMESTAMP( ' . $db->q($last_month) . ' )')
            ->order('hits desc');
        $db->setQuery($query, 0, 5);
        $results     = $db->loadObjectList();
        $top_studies = '';

        if (!$results) {
            $top_studies = Text::_('JBS_CPL_NO_INFORMATION');
        } else {
            foreach ($results as $result) {
                $top_studies .= $result->hits . ' ' . Text::_('JBS_CMN_HITS') .
                    ' - <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;id=' . $result->id . '">' .
                    $result->studytitle . '</a> - ' . date('Y-m-d', strtotime($result->studydate)) . '<br>';
            }
        }

        return $top_studies;
    }

    /**
     * Get Total Media Files
     *
     * @return int Number of Records under Media Files that are published.
     *
     * @since 9.0.0
     */
    public static function getTotalMediaFiles(): int
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('COUNT(*)')
            ->from('#__bsms_mediafiles')
            ->where('published = ' . 1);
        $db->setQuery($query);

        return (int)$db->loadResult();
    }

    /**
     * Get Top Downloads
     *
     * @return string HTML List of links to the downloads
     *
     * @since 9.0.0
     */
    public static function getTopDownloads(): string
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select(
                '#__bsms_mediafiles.*, #__bsms_studies.published AS spub, #__bsms_mediafiles.published AS mpublished,' .
                '#__bsms_studies.id AS sid, #__bsms_studies.studytitle AS stitle, #__bsms_studies.studydate AS sdate '
            )
            ->from('#__bsms_mediafiles')
            ->leftJoin('#__bsms_studies ON (#__bsms_mediafiles.study_id = #__bsms_studies.id)')
            ->where('#__bsms_mediafiles.published = 1 ')
            ->where('downloads > 0')
            ->order('downloads desc');

        $db->setQuery($query, 0, 5);
        $results     = $db->loadObjectList();
        $top_studies = '';

        foreach ($results as $result) {
            $top_studies .=
                $result->downloads . ' - <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;d=' .
                $result->sid . '">' . $result->stitle . '</a> - ' . date('Y-m-d', strtotime($result->sdate)) .
                '<br>';
        }

        return $top_studies;
    }

    /**
     * Get Downloads Last three Months
     *
     * @return  string HTML list of download links
     *
     * @since 9.0.0
     */
    public static function getDownloadsLastThreeMonths(): string
    {
        $month     = mktime(0, 0, 0, date("m") - 3, date("d"), date("Y"));
        $lastmonth = date("Y-m-d 00:00:01", $month);
        $db        = Factory::getContainer()->get('DatabaseDriver');
        $query     = $db->getQuery(true);
        $query
            ->select(
                '#__bsms_mediafiles.*, #__bsms_studies.published AS spub, #__bsms_mediafiles.published AS mpublished,' .
                ' #__bsms_studies.id AS sid, #__bsms_studies.studytitle AS stitle, #__bsms_studies.studydate AS sdate '
            )
            ->from('#__bsms_mediafiles')
            ->leftJoin('#__bsms_studies ON (#__bsms_mediafiles.study_id = #__bsms_studies.id)')
            ->where('#__bsms_mediafiles.published = ' . $db->q('1'))
            ->where('downloads > ' . (int)$db->q('0'))
            ->where('UNIX_TIMESTAMP(createdate) > UNIX_TIMESTAMP( ' . $db->q($lastmonth) . ' )')
            ->order('downloads DESC');
        $db->setQuery($query, 0, 5);
        $results     = $db->loadObjectList();
        $top_studies = '';

        if (!$results) {
            $top_studies = Text::_('JBS_CPL_NO_INFORMATION');
        } else {
            foreach ($results as $result) {
                $top_studies .= $result->downloads . ' ' . Text::_('JBS_CMN_HITS') .
                    ' - <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;id=' . $result->sid . '">' .
                    $result->stitle . '</a> - ' . date('Y-m-d', strtotime($result->sdate)) . '<br>';
            }
        }

        return $top_studies;
    }

    /**
     * Total Downloads
     *
     * @return  int Number of Media Files Downloaded in Published state
     *
     * @since 9.0.0
     */
    public static function getTotalDownloads(): int
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('SUM(downloads)')
            ->from('#__bsms_mediafiles')
            ->where('published = ' . 1)
            ->where('downloads > ' . 0);
        $db->setQuery($query);

        return (int)$db->loadResult();
    }

    /**
     * Top Score Media File Plays
     *
     * @return string Number of scores
     *
     * @throws Exception
     * @var   Registry $admin_params Admin Prams
     *
     * @since 9.0.0
     */
    public static function getTopScore(): string
    {
        $final           = [];
        $top_score_table = '';
        $format          = Cwmparams::getAdmin()->params->get('format_popular', '0');
        $db              = Factory::getContainer()->get('DatabaseDriver');
        $query           = $db->getQuery(true);
        $query
            ->select('study_id, sum(downloads + plays) as added ')
            ->from('#__bsms_mediafiles')
            ->where('published = ' . 1)
            ->group('study_id')
            ->order('added DESC');
        $db->setQuery($query);
        $results = $db->loadAssocList();
        array_splice($results, 5);

        foreach ($results as $key => $result) {
            $query = $db->getQuery(true);
            $query
                ->select(
                    '#__bsms_studies.studydate, #__bsms_studies.studytitle, #__bsms_studies.hits,' .
                    '#__bsms_studies.id, #__bsms_mediafiles.study_id from #__bsms_studies'
                )
                ->leftJoin('#__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id)')
                ->where('#__bsms_mediafiles.study_id = ' . (int)$result['study_id']);
            $db->setQuery($query);
            $hits = $db->loadObject();

            if ($hits) {
                if ($format < 1) {
                    $total = $result['added'] + $hits->hits;
                } else {
                    $total = $result->added;
                }

                $link    = ' <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;id=' . $hits->id . '">' .
                    $hits->studytitle . '</a> ' . date('Y-m-d', strtotime($hits->studydate)) . '<br>';
                $final2  = array('total' => $total, 'link' => $link);
                $final[] = $final2;
            }
        }

        rsort($final);
        array_splice($final, 5);

        foreach ($final as $value) {
            $top_score_table = implode('', $value);
        }

        return $top_score_table;
    }

    /**
     * Returns a System of Player
     *
     * @return string HTML Format or Empty
     *
     * @since 9.0.0
     */
    public static function getPlayers(): string
    {
        $count_no_player       = 0;
        $count_global_player   = 0;
        $count_internal_player = 0;
        $count_av_player       = 0;
        $count_legacy_player   = 0;
        $count_embed_code      = 0;
        $db                    = Factory::getContainer()->get('DatabaseDriver');
        $query                 = $db->getQuery(true);
        $query
            ->select('params')
            ->from('#__bsms_mediafiles')
            ->where('published = ' . $db->q('1'));
        $db->setQuery($query);
        $params = $db->loadObjectList();

        $registry      = new Registry();
        $media_players = '';

        if ($params) {
            $total_players = count($params);

            foreach ($params as $param) {
                $registry->loadString($param->params);

                switch ($registry->get('player', 0)) {
                    case 0:
                        $count_no_player++;
                        break;
                    case '100':
                        $count_global_player++;
                        break;
                    case '1':
                        $count_internal_player++;
                        break;
                    case '3':
                        $count_av_player++;
                        break;
                    case '7':
                        $count_legacy_player++;
                        break;
                    case '8':
                        $count_embed_code++;
                        break;
                }
            }

            $media_players = '<br /><strong>' . Text::_('JBS_CMN_TOTAL_PLAYERS') . ': ' . $total_players . '</strong>' .
                '<br /><strong>' . Text::_('JBS_CMN_INTERNAL_PLAYER') . ': </strong>' . $count_internal_player .
                '<br /><strong><a href="http://extensions.joomla.org/extensions/extension/multimedia/multimedia-players/allvideos" target="blank">' .
                Text::_('JBS_CMN_AVPLUGIN') . '</a>: </strong>' . $count_av_player . '<br /><strong>' .
                Text::_('JBS_CMN_LEGACY_PLAYER') . ': </strong>' . $count_legacy_player . '<br /><strong>' .
                Text::_('JBS_CMN_NO_PLAYER_TREATED_DIRECT') . ': </strong>' . $count_no_player . '<br /><strong>' .
                Text::_('JBS_CMN_GLOBAL_SETTINGS') . ': </strong>' . $count_global_player . '<br /><strong>' .
                Text::_('JBS_CMN_EMBED_CODE') . ': </strong>' . $count_embed_code;
        }

        return $media_players;
    }

    /**
     * Popups for media files
     *
     * @return string HTML Format
     *
     * @since 9.0.0
     */
    public static function getPopups(): string
    {
        $no_player    = 0;
        $pop_count    = 0;
        $sq_count     = 0;
        $inline_count = 0;
        $global_count = 0;
        $db           = Factory::getContainer()->get('DatabaseDriver');
        $query        = $db->getQuery(true);
        $query
            ->select('params')
            ->from('#__bsms_mediafiles')
            ->where('published = ' . 1);
        $db->setQuery($query);
        $popups = $db->loadObjectList();

        if ($popups) {
            $total_media_files = count($popups);

            foreach ($popups as $popup) {
                $registry = new Registry();
                $registry->loadString($popup->params);
                $popup = $registry->get('popup', null);

                switch ($popup) {
                    case null:
                    case 100:
                    case 0:
                        $no_player++;
                        break;
                    case 1:
                        $pop_count++;
                        break;
                    case 2:
                        $inline_count++;
                        break;
                    case 3:
                        $sq_count++;
                        break;
                }
            }

            $popups = '<br /><strong>' . Text::_('JBS_CMN_TOTAL_MEDIAFILES') . ': ' . $total_media_files . '</strong>' .
                '<br /><strong>' . Text::_('JBS_CMN_INLINE') . ': </strong>' . $inline_count . '<br /><strong>' .
                Text::_('JBS_CMN_POPUP') . ': </strong>' . $pop_count . '<br /><strong>' .
                Text::_('JBS_CMN_SQUEEZEBOX') . ': </strong>' . $sq_count . '<br /><strong>' .
                Text::_('JBS_CMN_NO_OPTION_TREATED_GLOBAL') . ': </strong>' . $no_player;
        }

        return $popups;
    }

    /**
     * Get the Podcast Task State in HTML format
     *
     * @return string HTML Formatted Button with Status info
     *
     * @since 10.0.0
     */
    public static function getPodcastTaskState(): string
    {
        $states = new \stdClass();

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('id, state, title')
            ->from('#__scheduler_tasks')
            ->where('type = ' . $db->q('proclaim.podcast'))
            ->order('state DESC');
        $db->setQuery($query);

        if (!$PodcastTask = $db->loadObject()) {
            $PodcastTask        = $states;
            $PodcastTask->state = '';
        }

        switch ($PodcastTask->state) {
            case 1:
                $states->state       = 'ENABLED';
                $states->buttonstate = ' btn-success';
                break;
            case 0:
                $states->state       = 'DISABLED';
                $states->buttonstate = ' btn-warning';
                break;
            case -2:
                $states->state       = 'TRASHED';
                $states->buttonstate = ' btn-light';
                break;
            default:
                $states->state       = 'JBS_CMN_TASK_NOT_CREATED';
                $states->buttonstate = ' btn-warning';
                break;
        }

        $return = "<div style='float: left; padding: 10px;'>";
        if ($states->state !== 'JBS_CMN_TASK_NOT_CREATED') {
            $return .= "<a href=\"" . Route::_('index.php?option=com_scheduler&amp;task=task.edit&id=' . $PodcastTask->id) . "\" target=\"_blank\">";
        } else {
            $return .= "<a href=\"" . Route::_('index.php?option=com_scheduler&amp;view=tasks') . "\" target=\"_blank\">";
        }

        $return .= "<button type='button' class='btn" . $states->buttonstate . "'><i class='icon-clock' title='Clock showing time'></i>" .
            Text::_('JBS_CMN_PODCAST_TASK_STATUS') . "<strong>" . Text::_($states->state) . "</strong></button>";

        $return .= "</a>";

        $return .= "</div>";

        return $return;
    }

    /**
     * Top Score Site
     *
     * @return bool|string
     *
     * @throws Exception
     * @since 9.0.0
     */
    public function getTopScoreSite(): bool|string
    {
        $input = Factory::getApplication()->input;
        $t     = $input->get('t', 1, 'int');

        $admin = Cwmparams::getAdmin();
        $limit = $admin->params->get('popular_limit', '25');
        $top   = '<select onchange="goTo()" id="urlList" class="form-select chzn-color-state valid form-control-success" size="1" aria-invalid="false"><option value="">' .
            Text::_('JBS_CMN_SELECT_POPULAR_STUDY') . '</option>';
        $final = array();

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('m.study_id, s.access, s.published AS spub, sum(m.downloads + m.plays) as added')
            ->from('#__bsms_mediafiles AS m')
            ->leftJoin('#__bsms_studies AS s ON (m.study_id = s.id)')
            ->where('m.published = 1 GROUP BY m.study_id');
        $db->setQuery($query);
        $format = $admin->params->get('format_popular', '0');

        $items = $db->loadObjectList();

        // Check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user   = Factory::getApplication()->getIdentity();
        $groups = $user->getAuthorisedViewLevels();

        foreach ($items as $i => $iValue) {
            if (($iValue->access > 1) && !in_array($iValue->access, $groups, true)) {
                unset($items[$i]);
            }
        }

        foreach ($items as $result) {
            $query = $db->getQuery(true);
            $query->select(
                '#__bsms_studies.studydate, #__bsms_studies.studytitle, #__bsms_studies.alias,
							#__bsms_studies.hits, #__bsms_studies.id, #__bsms_mediafiles.study_id'
            )
                ->from('#__bsms_studies')
                ->leftJoin('#__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id)')
                ->where('#__bsms_mediafiles.study_id = ' . (int)$result->study_id);
            $db->setQuery($query);
            $hits = $db->loadObject();

            if (!$hits) {
                return false;
            }

            if (!$hits->studytitle) {
                $name = $hits->id;
            } else {
                $name = $hits->studytitle;
            }

            if ($format < 1) {
                $total = $result->added + $hits->hits;
            } else {
                $total = $result->added;
            }

            $hits->slug = $hits->alias ? ($hits->id . ':' . $hits->alias) : $hits->id . ':'
                . str_replace(' ', '-', htmlspecialchars_decode($hits->studytitle, ENT_QUOTES));

            $selectvalue   = Route::_('index.php?option=com_proclaim&view=cwmsermon&id=' . $hits->slug . '&t=' . $t);
            $selectdisplay = $name . ' - ' . Text::_('JBS_CMN_SCORE') . ': ' . $total;
            $final2        = array(
                'score'   => $total,
                'select'  => $selectvalue,
                'display' => $selectdisplay
            );
            $final[]       = $final2;
        }

        rsort($final);
        array_splice($final, $limit);

        foreach ($final as $topscore) {
            $top .= '<option value="' . $topscore['select'] . '">' . $topscore['display'] . '</option>';
        }

        $top .= '</select>';

        return $top;
    }
}
