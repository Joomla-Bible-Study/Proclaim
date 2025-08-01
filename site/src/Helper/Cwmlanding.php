<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use Exception;
use http\Exception\RuntimeException;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * Class for JBSMLanding
 *
 * @package  Proclaim.Site
 * @since    8.0.0
 */
class Cwmlanding
{
    /**
     * Get Locations for Landing Page
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      Item ID
     *
     * @return string
     *
     * @throws   Exception
     * @since    8.0.0
     */
    public function getLocationsLandingPage(Registry $params, int $id = 0): string
    {
        try {
            $mainframe = Factory::getApplication();
        } catch (Exception $e) {
            throw new RuntimeException('Unable to load Application' . $e->getMessage());
        }

        $user      = $mainframe->getIdentity();
        $db        = Factory::getContainer()->get('DatabaseDriver');
        $location  = null;
        $template  = $params->get('studieslisttemplateid', 1);
        $limit     = $params->get('landinglocationslimit');
        $order     = 'ASC';

        if (!$limit) {
            $limit = 10000;
        }

        $locationuselimit = $params->get('landinglocationsuselimit', 0);
        $menu             = $mainframe->getMenu();
        $item             = $menu->getActive();
        $registry         = new Registry();

        if (isset($params)) {
            $registry->loadString($params);
            $language   = $db->quote($item->language) . ',' . $db->quote('*');
            $menu_order = $params->get('locations_order');
        } else {
            $language   = $db->quote($mainframe->getLanguage()->getTag()) . ',' . $db->quote('*');
            $menu_order = null;
        }

        if ($language === '*' || !$language) {
            $langlink = '';
        } elseif (isset($item->language)) {
            $langlink = '&amp;filter.languages=' . $item->language;
        }

        if ($menu_order) {
            switch ($menu_order) {
                case 2:
                    $order = 'ASC';
                    break;
                case 1:
                    $order = 'DESC';
                    break;
                case 0:
                    $order = $params->get('landing_default_order', 'ASC');
            }
        }

        $query = $db->getQuery(true);
        $query->select('distinct a.*')
            ->from('#__bsms_locations a')
            ->select('b.access')
            ->innerJoin('#__bsms_studies b on a.id = b.location_id')
            ->where('b.location_id > 0')
            ->where('a.published = 1')
            ->where('b.published = 1')
            ->where('b.language in (' . $language . ')');
        if ($groups = $user->getAuthorisedViewLevels()) {
            $query->whereIn($db->quoteName('b.access'), $groups);
        }
        $query->where('a.landing_show > 0')
            ->group('a.id')
            ->order('a.location_text ' . $order);
        $db->setQuery($query);

        $tresult = $db->loadObjectList();
        $count   = count($tresult);

        if ($count > 0) {
            switch ($locationuselimit) {
                case 0:
                    $t       = 0;
                    $i       = 0;
                    $showdiv = 0;

                    foreach ($tresult as $b) {
                        if (($t >= $limit) && $showdiv < 1) {
                            $location .= "\n\t" . '<div id="showhidelocations" style="display:none;"> <!-- start show/hide locations div-->';

                            $i       = 0;
                            $showdiv = 1;
                        }

                        $location .= '<div class="col" style="margin-right:7px"">';
                        $location .= '<a href="index.php?option=com_proclaim&amp;view=Cwmsermons&amp;filter_location=' . $b->id . '&amp;sendingview=cwmlanding' .
                            '&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_book=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t='
                            . $template . '">';

                        $location .= $b->location_text;

                        $location .= '</a>';
                        $location .= '</div>';
                        $i++;
                        $t++;

                        if ($i === 3 && $t !== $limit && $t !== $count) {
                            $i = 0;
                            $location .= '<div class="w-100"></div>';
                        } elseif ($i === 3 || $t === $count || $t === $limit) {
                            $i = 0;
                        }
                    }

                    if ($showdiv === 1) {
                        $location .= "\n\t" . '</div> <!-- close show/hide locations div-->';
                    }

                    $location .= '<div class="landing_separator"></div>';
                    break;

                case 1:
                    $location = '<div class="landingtable" style="display:inline-block;">';

                    foreach ($tresult as $b) {
                        if ((int) $b->landing_show === 1) {
                            $location .= '<div class="landingrow">';
                            $location .= '<div class="landingcell">
							<a class="landinglink" href="index.php?option=com_proclaim&amp;sendingview=Cwmlanding&amp;view=Cwmsermons&amp;filter_location='
                                . $b->id . '&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_book=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t='
                                . $template . '">';
                            $location .= $b->location_text;
                            $location .= '</a></div>';
                            $location .= '</div>';
                        }
                    }

                    $location .= '</div>';
                    $location .= '<div id="showhidelocations" style="display:none;">';

                    foreach ($tresult as $b) {
                        if ((int) $b->landing_show === 2) {
                            $location .= '<div class="landingrow">';
                            $location .= '<div class="landingcell">
							<a class="landinglink" href="index.php?option=com_proclaim&amp;sendingview=cwmlanding&amp;view=Cwmsermons&amp;filter_location='
                                . $b->id . '&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_book=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t='
                                . $template . '">';
                            $location .= $b->location_text;
                            $location .= '</a></div>';
                            $location .= '</div>';
                        }
                    }

                    $location .= '</div>';
                    $location .= '<div class="landing_separator"></div>';
                    $location .= '<div style="clear:both;"></div>';
                    break;
            }
        } else {
            $location = '';
        }

        return $location;
    }

    /**
     * Get Teacher for LandingPage
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      Item ID
     *
     * @return string
     *
     * @since    8.0.0
     */
    public function getTeacherLandingPage(Registry $params, int $id = 0): string
    {
        try {
            $mainframe   = Factory::getApplication();
        } catch (Exception $e) {
            throw new RuntimeException('Unable to load Application' . $e->getMessage());
        }

        $db        = Factory::getContainer()->get('DatabaseDriver');
        $user      = $mainframe->getIdentity();
        $langlink  = Multilanguage::isEnabled();
        $order     = null;
        $teacher   = null;

        $template        = $params->get('teachertemplateid', 1);
        $limit           = $params->get('landingteacherslimit', 10000);
        $teacheruselimit = $params->get('landingteachersuselimit', 0);
        $menu            = $mainframe->getMenu();
        $item            = $menu->getActive();
        $registry        = new Registry();

        if (isset($params)) {
            $registry->loadString($params);
            $language   = $db->quote($item->language) . ',' . $db->quote('*');
            $menu_order = $params->get('teachers_order');
        } else {
            $language   = $db->quote($mainframe->getLanguage()->getTag()) . ',' . $db->quote('*');
            $menu_order = null;
        }

        if ($language === '*' || !$language) {
            $langlink = '';
        } elseif (isset($item->language)) {
            $langlink = '&amp;filter.languages=' . $item->language;
        }

        if ($menu_order) {
            switch ($menu_order) {
                case 2:
                    $order = 'ASC';
                    break;
                case 1:
                    $order = 'DESC';
                    break;
                case 0:
                    $order = $params->get('landing_default_order', 'ASC');
            }
        }

        $query = $db->getQuery(true);
        $query->select('distinct a.*')
            ->from('#__bsms_teachers a')
            ->select('b.access')
            ->innerJoin('#__bsms_studies b on a.id = b.teacher_id')
            ->where('b.language in (' . $language . ')')
            ->where('a.published = 1');
        if ($groups = $user->getAuthorisedViewLevels()) {
            $query->whereIn($db->quoteName('b.access'), $groups);
        }
        $query->where('a.landing_show > 0')
            ->group('a.id')
            ->order('a.teachername ' . $order);
        $db->setQuery($query);

        $tresult = $db->loadObjectList();
        $count   = count($tresult);
        $t       = 0;
        $i       = 0;

        if ($count > 0) {
            $showdiv = 0;

            switch ($teacheruselimit) {
                case 0:
                    foreach ($tresult as $b) {
                        if (($t >= $limit) && $showdiv < 1) {
                            $teacher .= "\n\t" . '<div id="showhideteachers" style="display:none;"> <!-- start show/hide teacher div-->';

                            $i       = 0;
                            $showdiv = 1;
                        }

                        $teacher .= '<div class="col" style="margin-right:7px">';
                        if ((int) $params->get('linkto') === 0) {
                            $teacher .= '<a href="' . Route::_(
                                'index.php?option=com_proclaim&amp;view=Cwmsermons&amp;t=' . $template
                            )
                                . '&amp;sendingview=landing&amp;filter_teacher=' . $b->id
                                . $langlink . '&amp;filter_book=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;filter_messagetype=0">';
                        } else {
                            $teacher .= '<a href="' . Route::_(
                                'index.php?option=com_proclaim&amp;view=cwmteacher&id=' . $b->id . $langlink . '&t=' . $template
                            ) . '">';
                        }

                        $teacher .= $b->teachername;

                        $teacher .= '</a></div>';

                        $i++;
                        $t++;

                        if ($i === 3 && $t !== $limit && $t !== $count) {
                            $i = 0;
                            $teacher .= '<div class="w-100"></div>';
                        } elseif ($i === 3 || $t === $count || $t === $limit) {
                            $i = 0;
                        }
                    }

                    if ($showdiv === 1) {
                        $teacher .= "\n\t" . '</div> <!-- close show/hide teacher div-->';
                    }

                    $teacher .= '<div class="landing_separator"></div>';
                    break;

                case 1:
                    foreach ($tresult as $b) {
                        if ($b->landing_show === 1) {
                            if ((int) $params->get('linkto') === 0) {
                                $teacher .= '<div class="col-4" style="margin-right:7px"> <a '
                                    . Route::_('index.php?option=com_proclaim&amp;view=Cwmsermons&amp;t=' . $template)
                                    . '&amp;sendingview=landing&amp;filter_teacher=' . $b->id
                                    . '&amp;filter_book=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;filter_messagetype=0">';
                            } else {
                                $teacher .= '<div class="col-4" style="display: inline-block; margin-right:7px"><a href="'
                                    . Route::_(
                                        'index.php?option=com_proclaim&amp;view=cwmteacher&amp;id=' . $b->id . '&amp;t=' . $template
                                    ) . '">';
                            }

                            $teacher .= $b->teachername;
                            $teacher .= '</a></div>';
                        }
                    }

                    $teacher .= '<div id="showhideteachers" style="display:none;">';

                    foreach ($tresult as $b) {
                        if ($b->landing_show === 2) {
                            if ((int) $params->get('linkto') === 0) {
                                $teacher .= '<div class="col-4"><a href="'
                                    . Route::_('index.php?option=com_proclaim&amp;view=Cwmsermons&amp;t=' . $template)
                                    . '&amp;sendingview=landing&amp;filter_teacher=' . $b->id
                                    . '&amp;filter_book=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;filter_messagetype=0">';
                            } else {
                                $teacher .= '<div class="col-4"><a href="'
                                    . Route::_(
                                        'index.php?option=com_proclaim&amp;sendingview=landing&amp;view=teacher&amp;id=' .
                                        $b->id . '&amp;t=' . $template
                                    ) . '">';
                            }

                            $teacher .= $b->teachername;
                            $teacher .= '</a></div>';
                        }
                    }

                    $teacher .= '</div>';
                    $teacher .= '<div class="landing_separator"></div>';
                    $teacher .= '<div style="clear:both;"></div>';
                    break;
            }
        } else {
            $teacher = '';
        }

        return $teacher;
    }

    /**
     * Get Series for LandingPage
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      ID
     *
     * @return string
     *
     * @since    8.0.0
     */
    public function getSeriesLandingPage(Registry $params, int $id = 0): string
    {
        try {
            $mainframe   = Factory::getApplication();
        } catch (Exception $e) {
            throw new RuntimeException('Unable to load Application' . $e->getMessage());
        }

        $user      = $mainframe->getIdentity();
        $db        = Factory::getContainer()->get('DatabaseDriver');
        $order     = 'ASC';
        $series    = null;
        $numRows   = null;

        $template = $params->get('serieslisttemplateid', 1);
        $limit    = $params->get('landingserieslimit');

        if (!$limit) {
            $limit = 10000;
        }

        $seriesuselimit = $params->get('landingseriesuselimit', 0);
        $menu           = $mainframe->getMenu();
        $item           = $menu->getActive();
        $registry       = new Registry();

        if (isset($params)) {
            $registry->loadString($params);
            $language   = $db->quote($item->language) . ',' . $db->quote('*');
            $menu_order = $params->get('series_order');
        } else {
            $language   = $db->quote($mainframe->getLanguage()->getTag()) . ',' . $db->quote('*');
            $menu_order = null;
        }

        if ($menu_order) {
            switch ($menu_order) {
                case 2:
                    $order = 'ASC';
                    break;
                case 1:
                    $order = 'DESC';
                    break;
                case 0:
                    $order = $params->get('landing_default_order', 'ASC');
            }
        }

        $query = $db->getQuery(true);
        $query->select('distinct a.*')
            ->from('#__bsms_series a')
            ->select('b.access')
            ->innerJoin('#__bsms_studies b on a.id = b.series_id')
            ->where('b.language in (' . $language . ')');
        if ($groups = $user->getAuthorisedViewLevels()) {
            $query->whereIn($db->quoteName('b.access'), $groups);
        }
        $query->where('b.published = 1')
            ->group('a.id')
            ->order('a.series_text ' . $order);
        $db->setQuery($query);

        $items = $db->loadObjectList();
        $count = count($items);

        if ($count > 0) {
            switch ($seriesuselimit) {
                // Use landing page limit
                case 0:
                    $series = '';
                    $t      = 0;
                    $i      = 0;

                    $showdiv = 0;

                    foreach ($items as $b) {
                        if ($t >= $limit) {
                            if ($showdiv < 1) {
                                $series .= "\n\t" . '<div id="showhideseries" style="display:none;"> <!-- start show/hide series div-->';

                                $i       = 0;
                                $showdiv = 1;
                            }
                        }

                        if ((int) $params->get('series_linkto') === 0) {
                            $series .= '<div class="col" style="margin-right:7px">';
                            $series .= '<a href="index.php?option=com_proclaim&amp;view=Cwmsermons&amp;filter_series=' . $b->id
                                . '&amp;sendingview=landing&amp;filter_book=0&amp;filter_teacher=0'
                                . '&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t='
                                . $template . '">';
                        } else {
                            $series .= '<div class="col" style="margin-right:7px">';
                            $series .= '<a href="index.php?option=com_proclaim&amp;sendingview=landing&amp;view=cwmseriesdisplay&amp;id=' .
                                $b->id . '&amp;t=' . $template . '">';
                        }

                        $series .= $b->series_text;

                        $series .= '</a>';
                        $series .= '</div>';

                        $i++;
                        $t++;

                        if ($i === 3 && $t !== $limit && $t !== $count) {
                            $i = 0;
                            $series .= '<div class="w-100"></div>';
                        } elseif ($i === 3 || $t === $count || $t === $limit) {
                            $i = 0;
                        }
                    }

                    if ($showdiv === 1) {
                        $series  .= "\n\t" . '</div> <!-- close show/hide series div-->';
                    }

                    $series .= '<div class="landing_separator"></div>';

                    break;

                case 1:
                    // Use limit in each record 0 = do not show, 1 = show above More button, 2 = show below more button
                    $series = '<div class="landingtable" style="display:inline;">';

                    foreach ($items as $b) {
                        if ((int) $b->landing_show === 1) {
                            $series .= '<div class="landingrow">';

                            if ((int) $params->get('series_linkto') === '0') {
                                $series .= '<div class="landingcell">
									<a href="index.php?option=com_proclaim&amp;view=Cwmsermons&amp;filter_series='
                                    . $b->id . '&amp;sendingview=cwmlanding&amp;filter_book=0&amp;filter_teacher=0'
                                    . '&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t=' . $template . '">';
                            } else {
                                $series .= '<div class="landingcell"><a href="index.php?option=com_proclaim&amp;sendingview=cwmlanding&amp;view=seriesdisplay&amp;id='
                                    . $b->id . '&amp;t=' . $template . '">';
                            }

                            $series .= $numRows;
                            $series .= $b->series_text;

                            $series .= '</a></div></div>';
                        }
                    }

                    $series .= '</div>';
                    $series .= '<div id="showhideseries" style="display:none;">';

                    foreach ($items as $b) {
                        if ((int) $b->landing_show === 2) {
                            $series .= '<div class="landingrow">';

                            if ((int) $params->get('series_linkto') === 0) {
                                $series .= '<div class="landingcell">
									<a href="index.php?option=com_proclaim&amp;view=Cwmsermons&amp;filter_series='
                                    . $b->id . '&amp;sendingview=cwmlanding&amp;filter_book=0&amp;filter_teacher=0'
                                    . '&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t=' . $template . '">';
                            } else {
                                $series .= '<div class="landingcell">
									<a href="index.php?option=com_proclaim&amp;sendingview=cwmlanding&amp;view=cwmseriesdisplay&amp;id='
                                    . $b->id . '&amp;t=' . $template . '">';
                            }

                            $series .= $numRows;
                            $series .= $b->series_text;

                            $series .= '</a></div></div>';
                        }
                    }

                    $series .= '</div>';
                    $series .= '<div class="landing_separator"></div>';
                    $series .= '<div style="clear:both;"></div>';
                    break;
            }
        } else {
            $series = '';
        }

        return $series;
    }

    /**
     * Get Years for Landing Page
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      Item ID
     *
     * @return string
     *
     * @since    8.0.0
     */
    public function getYearsLandingPage(Registry $params, int $id = 0): string
    {
        try {
            $mainframe   = Factory::getApplication();
        } catch (Exception $e) {
            throw new RuntimeException('Unable to load Application' . $e->getMessage());
        }

        $db        = Factory::getContainer()->get('DatabaseDriver');
        $user      = $mainframe->getIdentity();
        $order     = 'ASC';
        $template  = $params->get('studieslisttemplateid');
        $limit     = $params->get('landingyearslimit');

        if (!$limit) {
            $limit = 10000;
        }

        $menu     = $mainframe->getMenu();
        $item     = $menu->getActive();
        $registry = new Registry();

        if (isset($params)) {
            $registry->loadString($params);
            $language   = $db->quote($item->language) . ',' . $db->quote('*');
            $menu_order = $params->get('years_order');
        } else {
            $language   = $db->quote($mainframe->getLanguage()->getTag()) . ',' . $db->quote('*');
            $menu_order = null;
        }

        if ($menu_order) {
            switch ($menu_order) {
                case 2:
                    $order = 'ASC';
                    break;
                case 1:
                    $order = 'DESC';
                    break;
                case 0:
                    $order = $params->get('landing_default_order', 'ASC');
            }
        }

        $query = $db->getQuery(true);
        $query->select('distinct year(studydate) as theYear')
            ->from('#__bsms_studies')
            ->where('language in (' . $language . ')');
        if ($groups = $user->getAuthorisedViewLevels()) {
            $query->whereIn($db->quoteName('access'), $groups);
        }
        $query->where('published = 1')
            ->group('year(studydate)')
            ->order('year(studydate) ' . $order);
        $db->setQuery($query);

        $tresult = $db->loadObjectList();
        $count   = count($tresult);
        $t       = 0;
        $i       = 0;

        if ($count > 0) {
            $year    = '';
            $showdiv = 0;

            foreach ($tresult as $b) {
                if (($t >= $limit) && $showdiv < 1) {
                    $year .= "\n\t" . '<div id="showhideyears" style="display:none;"> <!-- start show/hide years div-->';

                    $i       = 0;
                    $showdiv = 1;
                }

                $year .= '<div class="col" style="margin-right:7px">';
                $year .= '<a href="index.php?option=com_proclaim&amp;view=Cwmsermons&amp;filter_year='
                    . $b->theYear . '&amp;sendingview=cwmlanding&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0&amp;'
                    . 'filter_book=0&amp;filter_messagetype=0&amp;t='
                    . $template . '">';

                $year .= $b->theYear;

                $year .= '</a>';
                $year .= '</div>';
                $i++;
                $t++;

                if ($i === 3 && $t !== $limit && $t !== $count) {
                    $i = 0;
                    $year .= '<div class="w-100"></div>';
                } elseif ($i === 3 || $t === $count || $t === $limit) {
                    $i = 0;
                }
            }

            if ($showdiv === 1) {
                $year    .= "\n\t" . '</div> <!-- close show/hide years div-->';
            }

            $year .= '<div class="landing_separator"></div>';
            $year .= '<div style="clear:both;"></div>';
        } else {
            $year = '';
        }

        return $year;
    }

    /**
     * Get Topics for LandingPage
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      ID
     *
     * @return string
     *
     * @since    8.0.0
     */
    public function getTopicsLandingPage(Registry $params, int $id = 0): string
    {
        try {
            $app   = Factory::getApplication();
        } catch (Exception $e) {
            throw new RuntimeException('Unable to load Application' . $e->getMessage());
        }

        $user      = $app->getSession()->get('user');
        $db        = Factory::getContainer()->get('DatabaseDriver');
        $order     = 'ASC';
        $topic     = null;
        $template  = $params->get('studieslisttemplateid');
        $limit     = $params->get('landingtopicslimit');

        if (!$limit) {
            $limit = 10000;
        }

        $menu     = $app->getMenu();
        $item     = $menu->getActive();
        $registry = new Registry();

        if (isset($params)) {
            $registry->loadString($params);
            $language   = $db->quote($item->language) . ',' . $db->quote('*');
            $menu_order = $params->get('topics_order');
        } else {
            $language   = $db->quote($app->getLanguage()->getTag()) . ',' . $db->quote('*');
            $menu_order = null;
        }

        if ($menu_order) {
            switch ($menu_order) {
                case 2:
                    $order = 'ASC';
                    break;
                case 1:
                    $order = 'DESC';
                    break;
                case 0:
                    $order = $params->get('landing_default_order', 'ASC');
            }
        }

        $query = $db->getQuery('true');
        $query->select(
            'DISTINCT #__bsms_studies.access as access, #__bsms_topics.id, #__bsms_topics.topic_text, #__bsms_topics.params AS topic_params'
        )
            ->from('#__bsms_studies')
            ->join('LEFT', '#__bsms_studytopics ON #__bsms_studies.id = #__bsms_studytopics.study_id')
            ->join('LEFT', '#__bsms_topics ON #__bsms_topics.id = #__bsms_studytopics.topic_id')
            ->where('#__bsms_topics.published = 1')
            ->where('#__bsms_studies.published = 1')
            ->order('#__bsms_topics.topic_text ' . $order)
            ->group('id')
            ->where('#__bsms_studies.language in (' . $language . ')');
        if ($groups = $user->getAuthorisedViewLevels()) {
            $query->whereIn($db->quoteName('#__bsms_studies.access'), $groups);
        }
        $db->setQuery($query);

        $tresult = $db->loadObjectList();
        $count   = count($tresult);
        $t       = 0;
        $i       = 0;

        if ($count > 0) {
            $showdiv = 0;

            foreach ($tresult as $b) {
                if (($t >= $limit) && $showdiv < 1) {
                    $topic .= "\n\t" . '<div id="showhidetopics" style="display:none;"> <!-- start show/hide topics div-->';

                    $i       = 0;
                    $showdiv = 1;
                }

                $topic .= '<div class="col" style="margin-right:7px">';
                $topic .= '<a href="index.php?option=com_proclaim&amp;view=Cwmsermons&amp;filter_topic=' .
                    $b->id . '&amp;sendingview=cwmlanding&amp;filter_teacher=0'
                    . '&amp;filter_series=0&amp;filter_location=0&amp;filter_book=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t=' . $template . '">';
                $topic .= Cwmtranslated::getTopicItemTranslated($b);

                $topic .= '</a>';
                $topic .= '</div>';
                $i++;
                $t++;

                if ($i === 3 && $t !== $limit && $t !== $count) {
                    $i = 0;
                    $topic .= '<div class="w-100"></div>';
                } elseif ($i === 3 || $t === $count || $t === $limit) {
                    $i = 0;
                }
            }

            if ($showdiv === 1) {
                $topic   .= "\n\t" . '</div> <!-- close show/hide topics div-->';
            }

            $topic .= '<div class="landing_separator"></div>';
            $topic .= '<div style="clear:both;"></div>';
        } else {
            $topic = '';
        }

        return $topic;
    }

    /**
     * Get MessageType for Landing Page
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      ID
     *
     * @return string
     *
     * @since    8.0.0
     */
    public function getMessageTypesLandingPage(Registry $params, int $id = 0): string
    {
        try {
            $mainframe   = Factory::getApplication();
        } catch (Exception $e) {
            throw new RuntimeException('Unable to load Application' . $e->getMessage());
        }

        $db          = Factory::getContainer()->get('DatabaseDriver');
        $user        = $mainframe->getIdentity();
        $messagetype = null;
        $order       = 'ASC';
        $template    = $params->get('studieslisttemplateid', 1);
        $limit       = $params->get('landingmessagetypeslimit');

        if (!$limit) {
            $limit = 10000;
        }

        $messagetypeuselimit = $params->get('landingmessagetypeuselimit', 0);
        $menu                = $mainframe->getMenu();
        $item                = $menu->getActive();
        $registry            = new Registry();

        if (isset($params)) {
            $registry->loadString($params);
            $language   = $db->quote($item->language) . ',' . $db->quote('*');
            $menu_order = $params->get('messagetypes_order');
        } else {
            $language   = $db->quote($mainframe->getLanguage()->getTag()) . ',' . $db->quote('*');
            $menu_order = null;
        }

        if ($language === '*' || !$language) {
            $langlink = '';
        } elseif (isset($item->language)) {
            $langlink = '&amp;filter.languages=' . $item->language;
        }

        if ($menu_order) {
            switch ($menu_order) {
                case 2:
                    $order = 'ASC';
                    break;
                case 1:
                    $order = 'DESC';
                    break;
                case 0:
                    $order = $params->get('landing_default_order', 'ASC');
            }
        }

        $query = $db->getQuery(true);
        $query->select('distinct a.*')
            ->from('#__bsms_message_type a')
            ->select('b.access AS study_access')
            ->innerJoin('#__bsms_studies b on a.id = b.messagetype')
            ->where('b.language in (' . $language . ')');
        if ($groups = $user->getAuthorisedViewLevels()) {
            $query->whereIn($db->quoteName('b.access'), $groups);
        }
        $query->where('b.published = 1')
            ->where('a.landing_show > 0')
            ->group('a.id')
            ->order('a.message_type ' . $order);
        $db->setQuery($query);

        $tresult = $db->loadObjectList();
        $count   = count($tresult);
        $t       = 0;
        $i       = 0;

        if ($count > 0) {
            switch ($messagetypeuselimit) {
                case 0:
                    $showdiv = 0;

                    foreach ($tresult as $b) {
                        if (($t >= $limit) && $showdiv < 1) {
                            $messagetype .= "\n\t" . '<div id="showhidemessagetypes" style="display:none;"> <!-- start show/hide messagetype div-->';

                            $i       = 0;
                            $showdiv = 1;
                        }

                        $messagetype .= '<div class="col" style="margin-right:7px">';
                        $messagetype .= '<a href="index.php?option=com_proclaim&amp;view=Cwmsermons&amp;filter_messagetype=' .
                            $b->id . '&amp;sendingview=cwmlanding&amp;filter_book=0&amp;filter_teacher=0&amp;filter_series=0' .
                            '&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;t=' . $template . '">';

                        $messagetype .= $b->message_type;

                        $messagetype .= '</a>';
                        $messagetype .= '</div>';

                        $i++;
                        $t++;

                        if ($i === 3 && $t !== $limit && $t !== $count) {
                            $i = 0;
                            $messagetype .= '<div class="w-100"></div>';
                        } elseif ($i === 3 || $t === $count || $t === $limit) {
                            $i = 0;
                        }
                    }

                    if ($showdiv === 1) {
                        $messagetype .= "\n\t" . '</div> <!-- close show/hide messagetype div-->';
                        $showdiv     = 2;
                    }

                    $messagetype .= '<div class="landing_separator"></div>';
                    break;

                case 1:
                    $messagetype = '<div class="landingtable" style="display:inline;">';

                    foreach ($tresult as $b) {
                        if ((int) $b->landing_show === 1) {
                            $messagetype .= '<div class="landingrow">';
                            $messagetype .= '<div class="landingcell">
							<a class="landinglink" href="index.php?option=com_proclaim&amp;view=Cwmsermons&amp;filter_messagetype='
                                . $b->id . '&amp;sendingview=cwmlanding&amp;filter_book=0&amp;filter_teacher=0&amp;filter_series=0' .
                                '&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;t=' . $template . '">';
                            $messagetype .= $b->message_type;
                            $messagetype .= '</a></div>';
                            $messagetype .= '</div>';
                        }
                    }

                    $messagetype .= '</div>';
                    $messagetype .= '<div id="showhidemessagetypes" style="display:none;">';

                    foreach ($tresult as $b) {
                        if ((int) $b->landing_show === 2) {
                            $messagetype .= '<div class="landingrow">';
                            $messagetype .= '<div class="landingcell">
							<a class="landinglink" href="index.php?option=com_proclaim&amp;view=Cwmsermons&amp;filter_messagetype=' . $b->id
                                . '&amp;sendingview=cwmlanding&amp;filter_book=0&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0' .
                                '&amp;filter_location=0&amp;filter_year=0&amp;t=' . $template . '">';
                            $messagetype .= $b->message_type;
                            $messagetype .= '</a></div>';
                            $messagetype .= '</div>';
                        }
                    }

                    $messagetype .= '</div>';
                    $messagetype .= '<div class="landing_separator"></div>';
                    $messagetype .= '<div style="clear:both;"></div>';
                    break;
            }
        } else {
            $messagetype = '';
        }

        return $messagetype;
    }

    /**
     * Get Books for Landing Page.
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      ID
     *
     * @return string
     * @since    8.0.0
     */
    public function getBooksLandingPage(Registry $params, int $id = 0): string
    {
        try {
            $app      = Factory::getApplication();
        } catch (Exception $e) {
            throw new RuntimeException('Unable to load Application' . $e->getMessage());
        }

        $user     = $app->getSession()->get('user');
        $db       = Factory::getContainer()->get('DatabaseDriver');
        $order    = 'ASC';
        $book     = null;
        $template = $params->get('studieslisttemplateid');
        $limit    = $params->get('landingbookslimit');

        if (!$limit) {
            $limit = 10000;
        }

        $menu     = $app->getMenu();
        $item     = $menu->getActive();
        $registry = new Registry();

        if (isset($params)) {
            $registry->loadString($params);
            $language   = $db->quote($item->language) . ',' . $db->quote('*');
            $menu_order = $params->get('books_order');
        } else {
            $language   = $db->quote($app->getLanguage()->getTag()) . ',' . $db->quote('*');
            $menu_order = null;
        }

        if ($language === '*' || !$language) {
            $langlink = '';
        } elseif (isset($item->language)) {
            $langlink = '&amp;filter.languages=' . $item->language;
        }

        if ($menu_order) {
            switch ($menu_order) {
                case 2:
                    $order = 'ASC';
                    break;
                case 1:
                    $order = 'DESC';
                    break;
                case 0:
                    $order = $params->get('landing_default_order', 'ASC');
            }
        }

        // Compute view access permissions.
        $groups = $user->getAuthorisedViewLevels();
        $groups = array_unique($groups);
        $groups = implode(',', $groups);
        $query  = $db->getQuery(true);
        $query->select('distinct a.*')
            ->from('#__bsms_books a')
            ->select('b.access AS access')
            ->innerJoin('#__bsms_studies b on a.booknumber = b.booknumber')
            ->where('b.language in (' . $language . ')');
        if ($groups = $user->getAuthorisedViewLevels()) {
            $query->whereIn($db->quoteName('b.access'), $groups);
        }
        $query->where('b.published = 1')
            ->order('a.booknumber ' . $order)
            ->group('a.bookname');
        $db->setQuery($query);

        $tresult = $db->loadObjectList();
        $count   = count($tresult);
        $t       = 0;
        $i       = 0;

        if ($count > 0) {
            $showdiv = 0;

            foreach ($tresult as $b) {
                if (($t >= $limit) && $showdiv < 1) {
                    $book .= "\n\t" . '<div id="showhidebooks" style="display:none;"> <!-- start show/hide book div-->';

                    $i       = 0;
                    $showdiv = 1;
                }

                $book .= '<div class="col" style="margin-right:7px">';
                $book .= '<a href="index.php?option=com_proclaim&amp;sendingview=landing&amp;view=Cwmsermons&amp;filter_book=' . $b->booknumber
                    . '&amp;sendingview=cwmlanding&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0' .
                    '&amp;filter_year=0&amp;filter_messagetype=0&amp;t=' . $template . '">';

                $book .= Text::_($b->bookname);

                $book .= '</a>';
                $book .= '</div>';
                $i++;
                $t++;

                if ($i === 3 && $t !== $limit && $t !== $count) {
                    $i = 0;
                    $book .= '<div class="w-100"></div>';
                } elseif ($i === 3 || $t === $count || $t === $limit) {
                    $i = 0;
                }
            }

            if ($showdiv === 1) {
                $book .= "\n\t" . '</div> <!-- close show/hide books div-->';
            }

            $book .= '<div class="landing_separator"></div>';
            $book .= '<div style="clear:both;"></div>';
        } else {
            $book = '';
        }

        return $book;
    }
}
