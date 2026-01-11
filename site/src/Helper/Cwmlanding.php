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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmtranslated;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Class for JBSMLanding
 *
 * @package  Proclaim.Site
 * @since    8.0.0
 */
class Cwmlanding
{
    private CMSApplicationInterface $app;
    private DatabaseInterface $db;
    private User $user;

    /**
     * Initialize common dependencies
     *
     * @return void
     * @throws \RuntimeException
     * @since 10.0.0
     */
    private function initDependencies(): void
    {
        if (!isset($this->app)) {
            try {
                $this->app = Factory::getApplication();
            } catch (\Exception $e) {
                throw new \RuntimeException('Unable to load Application: ' . $e->getMessage());
            }
            $this->db   = Factory::getContainer()->get('DatabaseDriver');
            $this->user = $this->app->getIdentity();
        }
    }

    /**
     * Determine sort order from menu params
     *
     * @param   Registry     $params     Item Params
     * @param   string       $orderKey   The param key for menu order
     *
     * @return string ASC or DESC
     * @since 10.0.0
     */
    private function getSortOrder(Registry $params, string $orderKey): string
    {
        $menuOrder = $params->get($orderKey);

        if (!$menuOrder) {
            return $params->get('landing_default_order', 'ASC');
        }

        return match ((int) $menuOrder) {
            1       => 'DESC',
            2       => 'ASC',
            default => $params->get('landing_default_order', 'ASC'),
        };
    }

    /**
     * Get language filter for queries
     *
     * @param   Registry  $params  Item Params
     *
     * @return string Quoted language values for SQL IN clause
     * @since 10.0.0
     */
    private function getLanguageFilter(Registry $params): string
    {
        $menu = $this->app->getMenu();
        $item = $menu->getActive();

        if ($item && $item->language) {
            return $this->db->quote($item->language) . ',' . $this->db->quote('*');
        }

        return $this->db->quote($this->app->getLanguage()->getTag()) . ',' . $this->db->quote('*');
    }

    /**
     * Add access level filter to query
     *
     * @param   \Joomla\Database\DatabaseQuery  $query       The query to modify
     * @param   string                          $accessCol   The access column name
     *
     * @return void
     * @since 10.0.0
     */
    private function addAccessFilter($query, string $accessCol = 'b.access'): void
    {
        $groups = $this->user->getAuthorisedViewLevels();
        if ($groups) {
            $query->whereIn($this->db->quoteName($accessCol), $groups);
        }
    }

    /**
     * Build grid items HTML with show/hide support
     *
     * @param   array     $items       Array of result objects
     * @param   int       $limit       Number of items before hide div
     * @param   callable  $linkBuilder Callback to build each item's HTML
     * @param   string    $divId       ID for the show/hide div
     *
     * @return string
     * @since 10.0.0
     */
    private function buildGridHtml(array $items, int $limit, callable $linkBuilder, string $divId): string
    {
        $count = \count($items);
        if ($count === 0) {
            return '';
        }

        $html     = '';
        $t        = 0;
        $i        = 0;
        $showdiv  = false;

        foreach ($items as $item) {
            if ($t >= $limit && !$showdiv) {
                // Use display:contents so items flow with visible section when expanded
                $html .= "\n\t" . '<div id="' . $divId . '" class="landing-expandable" style="display:none;">';
                // Don't reset $i - continue column flow from visible section
                $showdiv = true;
            }

            $html .= $linkBuilder($item);
            $i++;
            $t++;

            if ($i === 3 && $t !== $count) {
                $i = 0;
                $html .= '<div class="w-100"></div>';
            } elseif ($i === 3 || $t === $count) {
                // Add empty placeholder columns to complete the last row
                if ($t === $count && $i > 0 && $i < 3) {
                    $emptyColsNeeded = 3 - $i;
                    for ($e = 0; $e < $emptyColsNeeded; $e++) {
                        $html .= '<div class="col" style="margin-right:7px"></div>';
                    }
                }
                $i = 0;
            }
        }

        if ($showdiv) {
            $html .= "\n\t" . '</div>';
        }

        $html .= '<div class="landing_separator"></div>';

        return $html;
    }

    /**
     * Build landing table HTML for use limit = 1 mode
     *
     * @param   array     $items       Array of result objects
     * @param   callable  $linkBuilder Callback to build each item link
     * @param   string    $divId       ID for the show/hide div
     *
     * @return string
     * @since 10.0.0
     */
    private function buildLandingTableHtml(array $items, callable $linkBuilder, string $divId): string
    {
        if (\count($items) === 0) {
            return '';
        }

        $html = '<div class="landingtable" style="display:inline-block;">';

        foreach ($items as $item) {
            if ((int) $item->landing_show === 1) {
                $html .= '<div class="landingrow"><div class="landingcell">';
                $html .= $linkBuilder($item);
                $html .= '</div></div>';
            }
        }

        $html .= '</div>';
        $html .= '<div id="' . $divId . '" style="display:none;">';

        foreach ($items as $item) {
            if ((int) $item->landing_show === 2) {
                $html .= '<div class="landingrow"><div class="landingcell">';
                $html .= $linkBuilder($item);
                $html .= '</div></div>';
            }
        }

        $html .= '</div>';
        $html .= '<div class="landing_separator"></div>';
        $html .= '<div style="clear:both;"></div>';

        return $html;
    }

    /**
     * Build a filter link for sermons view
     *
     * @param   string  $filterName   The filter parameter name
     * @param   mixed   $filterValue  The filter value
     * @param   int     $template     Template ID
     * @param   string  $text         Link text
     * @param   string  $class        Optional CSS class
     *
     * @return string
     * @since 10.0.0
     */
    private function buildSermonFilterLink(
        string $filterName,
        $filterValue,
        int $template,
        string $text,
        string $class = ''
    ): string {
        $classAttr = $class ? ' class="' . $class . '"' : '';
        $baseUrl   = 'index.php?option=com_proclaim&amp;view=Cwmsermons&amp;sendingview=cwmlanding';
        $filters   = '&amp;filter_teacher=0&amp;filter_series=0&amp;filter_topic=0&amp;filter_location=0'
                   . '&amp;filter_book=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t=' . $template;

        $url = $baseUrl . '&amp;' . $filterName . '=' . $filterValue . $filters;

        return '<a' . $classAttr . ' href="' . $url . '">' . $text . '</a>';
    }

    /**
     * Get Locations for Landing Page
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      Item ID (unused, kept for BC)
     *
     * @return string
     *
     * @throws   \Exception
     * @since    8.0.0
     */
    public function getLocationsLandingPage(Registry $params, int $id = 0): string
    {
        $this->initDependencies();

        $template         = (int) $params->get('studieslisttemplateid', 1);
        $limit            = (int) $params->get('landinglocationslimit', 10000) ?: 10000;
        $locationuselimit = (int) $params->get('landinglocationsuselimit', 0);
        $order            = $this->getSortOrder($params, 'locations_order');
        $language         = $this->getLanguageFilter($params);

        $query = $this->db->getQuery(true);
        $query->select('DISTINCT a.*')
            ->from('#__bsms_locations a')
            ->innerJoin('#__bsms_studies b ON a.id = b.location_id')
            ->where('b.location_id > 0')
            ->where('a.published = 1')
            ->where('b.published = 1')
            ->where('b.language IN (' . $language . ')')
            ->where('a.landing_show > 0')
            ->group('a.id')
            ->order('a.location_text ' . $order);

        $this->addAccessFilter($query);
        $this->db->setQuery($query);
        $items = $this->db->loadObjectList();

        if (empty($items)) {
            return '';
        }

        $linkBuilder = fn ($item) => '<div class="col" style="margin-right:7px">'
            . $this->buildSermonFilterLink('filter_location', $item->id, $template, $item->location_text)
            . '</div>';

        if ($locationuselimit === 0) {
            return $this->buildGridHtml($items, $limit, $linkBuilder, 'showhidelocations');
        }

        return $this->buildLandingTableHtml(
            $items,
            fn ($item) => $this->buildSermonFilterLink(
                'filter_location',
                $item->id,
                $template,
                $item->location_text,
                'landinglink'
            ),
            'showhidelocations'
        );
    }

    /**
     * Get a Teacher for the LandingPage
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      Item ID (unused, kept for BC)
     *
     * @return string
     *
     * @since    8.0.0
     */
    public function getTeacherLandingPage(Registry $params, int $id = 0): string
    {
        $this->initDependencies();

        $template        = (int) $params->get('teachertemplateid', 1);
        $limit           = (int) $params->get('landingteacherslimit', 10000) ?: 10000;
        $teacheruselimit = (int) $params->get('landingteachersuselimit', 0);
        $linkTo          = (int) $params->get('linkto', 0);
        $order           = $this->getSortOrder($params, 'teachers_order');
        $language        = $this->getLanguageFilter($params);

        $query = $this->db->getQuery(true);
        $query->select('DISTINCT a.*')
            ->from('#__bsms_teachers a')
            ->innerJoin('#__bsms_studies b ON a.id = b.teacher_id')
            ->where('b.language IN (' . $language . ')')
            ->where('a.published = 1')
            ->where('a.landing_show > 0')
            ->group('a.id')
            ->order('a.teachername ' . $order);

        $this->addAccessFilter($query);
        $this->db->setQuery($query);
        $items = $this->db->loadObjectList();

        if (empty($items)) {
            return '';
        }

        $buildLink = function ($item, $class = '') use ($template, $linkTo) {
            $classAttr = $class ? ' class="' . $class . '"' : '';
            if ($linkTo === 0) {
                $url = Route::_('index.php?option=com_proclaim&view=Cwmsermons&t=' . $template)
                    . '&amp;sendingview=landing&amp;filter_teacher=' . $item->id
                    . '&amp;filter_book=0&amp;filter_series=0&amp;filter_topic=0'
                    . '&amp;filter_location=0&amp;filter_year=0&amp;filter_messagetype=0';
            } else {
                $url = Route::_('index.php?option=com_proclaim&view=cwmteacher&id=' . $item->id . '&t=' . $template);
            }
            return '<a' . $classAttr . ' href="' . $url . '">' . $item->teachername . '</a>';
        };

        if ($teacheruselimit === 0) {
            $linkBuilder = fn ($item) => '<div class="col" style="margin-right:7px">'
                . $buildLink($item) . '</div>';
            return $this->buildGridHtml($items, $limit, $linkBuilder, 'showhideteachers');
        }

        return $this->buildLandingTableHtml($items, fn ($item) => $buildLink($item), 'showhideteachers');
    }

    /**
     * Get Series for LandingPage
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      ID (unused, kept for BC)
     *
     * @return string
     *
     * @since    8.0.0
     */
    public function getSeriesLandingPage(Registry $params, int $id = 0): string
    {
        $this->initDependencies();

        $template       = (int) $params->get('serieslisttemplateid', 1);
        $limit          = (int) $params->get('landingserieslimit', 10000) ?: 10000;
        $seriesuselimit = (int) $params->get('landingseriesuselimit', 0);
        $seriesLinkTo   = (int) $params->get('series_linkto', 0);
        $order          = $this->getSortOrder($params, 'series_order');
        $language       = $this->getLanguageFilter($params);

        $query = $this->db->getQuery(true);
        $query->select('DISTINCT a.*')
            ->from('#__bsms_series a')
            ->innerJoin('#__bsms_studies b ON a.id = b.series_id')
            ->where('b.language IN (' . $language . ')')
            ->where('b.published = 1')
            ->group('a.id')
            ->order('a.series_text ' . $order);

        $this->addAccessFilter($query);
        $this->db->setQuery($query);
        $items = $this->db->loadObjectList();

        if (empty($items)) {
            return '';
        }

        $buildLink = function ($item, $class = '') use ($template, $seriesLinkTo) {
            if ($seriesLinkTo === 0) {
                $url = 'index.php?option=com_proclaim&amp;view=Cwmsermons&amp;filter_series=' . $item->id
                    . '&amp;sendingview=landing&amp;filter_book=0&amp;filter_teacher=0'
                    . '&amp;filter_topic=0&amp;filter_location=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t='
                    . $template;
            } else {
                $url = 'index.php?option=com_proclaim&amp;sendingview=landing&amp;view=cwmseriesdisplay&amp;id='
                    . $item->id . '&amp;t=' . $template;
            }
            return '<a href="' . $url . '">' . $item->series_text . '</a>';
        };

        if ($seriesuselimit === 0) {
            $linkBuilder = fn ($item) => '<div class="col" style="margin-right:7px">'
                . $buildLink($item) . '</div>';
            return $this->buildGridHtml($items, $limit, $linkBuilder, 'showhideseries');
        }

        return $this->buildLandingTableHtml($items, fn ($item) => $buildLink($item), 'showhideseries');
    }

    /**
     * Get Years for Landing Page
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      Item ID (unused, kept for BC)
     *
     * @return string
     *
     * @since    8.0.0
     */
    public function getYearsLandingPage(Registry $params, int $id = 0): string
    {
        $this->initDependencies();

        $template = (int) $params->get('studieslisttemplateid', 1);
        $limit    = (int) $params->get('landingyearslimit', 10000) ?: 10000;
        $order    = $this->getSortOrder($params, 'years_order');
        $language = $this->getLanguageFilter($params);

        $query = $this->db->getQuery(true);
        $query->select('DISTINCT YEAR(studydate) as theYear')
            ->from('#__bsms_studies')
            ->where('language IN (' . $language . ')')
            ->where('published = 1')
            ->group('YEAR(studydate)')
            ->order('YEAR(studydate) ' . $order);

        $this->addAccessFilter($query, 'access');
        $this->db->setQuery($query);
        $items = $this->db->loadObjectList();

        if (empty($items)) {
            return '';
        }

        $linkBuilder = fn ($item) => '<div class="col" style="margin-right:7px">'
            . $this->buildSermonFilterLink('filter_year', $item->theYear, $template, $item->theYear)
            . '</div>';

        $html = $this->buildGridHtml($items, $limit, $linkBuilder, 'showhideyears');
        $html .= '<div style="clear:both;"></div>';

        return $html;
    }

    /**
     * Get Topics for LandingPage
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      ID (unused, kept for BC)
     *
     * @return string
     *
     * @since    8.0.0
     */
    public function getTopicsLandingPage(Registry $params, int $id = 0): string
    {
        $this->initDependencies();

        $template = (int) $params->get('studieslisttemplateid', 1);
        $limit    = (int) $params->get('landingtopicslimit', 10000) ?: 10000;
        $order    = $this->getSortOrder($params, 'topics_order');
        $language = $this->getLanguageFilter($params);

        $query = $this->db->getQuery(true);
        $query->select('DISTINCT #__bsms_topics.id, #__bsms_topics.topic_text, #__bsms_topics.params AS topic_params')
            ->from('#__bsms_studies')
            ->join('LEFT', '#__bsms_studytopics ON #__bsms_studies.id = #__bsms_studytopics.study_id')
            ->join('LEFT', '#__bsms_topics ON #__bsms_topics.id = #__bsms_studytopics.topic_id')
            ->where('#__bsms_topics.published = 1')
            ->where('#__bsms_studies.published = 1')
            ->where('#__bsms_studies.language IN (' . $language . ')')
            ->group('#__bsms_topics.id')
            ->order('#__bsms_topics.topic_text ' . $order);

        $this->addAccessFilter($query, '#__bsms_studies.access');
        $this->db->setQuery($query);
        $items = $this->db->loadObjectList();

        if (empty($items)) {
            return '';
        }

        $linkBuilder = fn ($item) => '<div class="col" style="margin-right:7px">'
            . '<a href="index.php?option=com_proclaim&amp;view=Cwmsermons&amp;filter_topic=' . $item->id
            . '&amp;sendingview=cwmlanding&amp;filter_teacher=0&amp;filter_series=0&amp;filter_location=0'
            . '&amp;filter_book=0&amp;filter_year=0&amp;filter_messagetype=0&amp;t=' . $template . '">'
            . Cwmtranslated::getTopicItemTranslated($item) . '</a></div>';

        $html = $this->buildGridHtml($items, $limit, $linkBuilder, 'showhidetopics');
        $html .= '<div style="clear:both;"></div>';

        return $html;
    }

    /**
     * Get MessageType for Landing Page
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      ID (unused, kept for BC)
     *
     * @return string
     *
     * @since    8.0.0
     */
    public function getMessageTypesLandingPage(Registry $params, int $id = 0): string
    {
        $this->initDependencies();

        $template            = (int) $params->get('studieslisttemplateid', 1);
        $limit               = (int) $params->get('landingmessagetypeslimit', 10000) ?: 10000;
        $messagetypeuselimit = (int) $params->get('landingmessagetypeuselimit', 0);
        $order               = $this->getSortOrder($params, 'messagetypes_order');
        $language            = $this->getLanguageFilter($params);

        $query = $this->db->getQuery(true);
        $query->select('DISTINCT a.*')
            ->from('#__bsms_message_type a')
            ->innerJoin('#__bsms_studies b ON a.id = b.messagetype')
            ->where('b.language IN (' . $language . ')')
            ->where('b.published = 1')
            ->where('a.landing_show > 0')
            ->group('a.id')
            ->order('a.message_type ' . $order);

        $this->addAccessFilter($query);
        $this->db->setQuery($query);
        $items = $this->db->loadObjectList();

        if (empty($items)) {
            return '';
        }

        $linkBuilder = fn ($item) => '<div class="col" style="margin-right:7px">'
            . $this->buildSermonFilterLink('filter_messagetype', $item->id, $template, $item->message_type)
            . '</div>';

        if ($messagetypeuselimit === 0) {
            return $this->buildGridHtml($items, $limit, $linkBuilder, 'showhidemessagetypes');
        }

        return $this->buildLandingTableHtml(
            $items,
            fn ($item) => $this->buildSermonFilterLink(
                'filter_messagetype',
                $item->id,
                $template,
                $item->message_type,
                'landinglink'
            ),
            'showhidemessagetypes'
        );
    }

    /**
     * Get Books for Landing Page.
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      ID (unused, kept for BC)
     *
     * @return string
     * @since    8.0.0
     */
    public function getBooksLandingPage(Registry $params, int $id = 0): string
    {
        $this->initDependencies();

        $template = (int) $params->get('studieslisttemplateid', 1);
        $limit    = (int) $params->get('landingbookslimit', 10000) ?: 10000;
        $order    = $this->getSortOrder($params, 'books_order');
        $language = $this->getLanguageFilter($params);

        $query = $this->db->getQuery(true);
        $query->select('DISTINCT a.*')
            ->from('#__bsms_books a')
            ->innerJoin('#__bsms_studies b ON a.booknumber = b.booknumber')
            ->where('b.language IN (' . $language . ')')
            ->where('b.published = 1')
            ->group('a.bookname')
            ->order('a.booknumber ' . $order);

        $this->addAccessFilter($query);
        $this->db->setQuery($query);
        $items = $this->db->loadObjectList();

        if (empty($items)) {
            return '';
        }

        $linkBuilder = fn ($item) => '<div class="col" style="margin-right:7px">'
            . $this->buildSermonFilterLink('filter_book', $item->booknumber, $template, Text::_($item->bookname))
            . '</div>';

        $html = $this->buildGridHtml($items, $limit, $linkBuilder, 'showhidebooks');
        $html .= '<div style="clear:both;"></div>';

        return $html;
    }
}
