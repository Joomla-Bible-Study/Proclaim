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
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;
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
            $this->db   = Factory::getContainer()->get(DatabaseInterface::class);
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
     * @param   DatabaseQuery  $query       The query to modify
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

        $html = '';
        $t    = 0;
        $i    = 0;

        foreach ($items as $item) {
            $itemHtml = $linkBuilder($item);

            if ($t >= $limit) {
                $hiddenClass = 'landing-hidden-' . $divId;

                // Add class
                $itemHtml = preg_replace('/^<div class="/', '<div class="' . $hiddenClass . ' ', $itemHtml, 1);

                // Add style
                if (strpos($itemHtml, 'style="') !== false) {
                    $itemHtml = str_replace('style="', 'style="display:none; ', $itemHtml);
                } else {
                    $itemHtml = preg_replace('/(<div[^>]+)/', '$1 style="display:none"', $itemHtml, 1);
                }
            }

            $html .= $itemHtml;
            $i++;
            $t++;

            if ($i === 3 && $t !== $count) {
                $i   = 0;
                $sep = '<div class="w-100"></div>';
                if ($t >= $limit) {
                    $sep = '<div class="w-100 landing-hidden-' . $divId . '" style="display:none"></div>';
                }
                $html .= $sep;
            } elseif ($i === 3 || $t === $count) {
                // Add empty placeholder columns to complete the last row
                if ($t === $count && $i > 0 && $i < 3) {
                    $emptyColsNeeded = 3 - $i;
                    $placeholder     = '<div class="col" style="margin-right:7px"></div>';
                    if ($t >= $limit) {
                        $placeholder = '<div class="col landing-hidden-' . $divId . '" style="display:none; margin-right:7px"></div>';
                    }
                    $html .= str_repeat($placeholder, $emptyColsNeeded);
                }
                $i = 0;
            }
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
        $url       = 'index.php?option=com_proclaim&view=cwmsermons&sendingview=cwmlanding'
                   . '&' . $filterName . '=' . $filterValue . '&t=' . $template;

        return '<a' . $classAttr . ' href="' . Route::_($url) . '">' . $text . '</a>';
    }

    /**
     * Get the section order for the landing page
     *
     * Reads from the new landing_layout JSON field first, falling back to
     * legacy headingorder_* fields for backward compatibility.
     *
     * @param   Registry  $params  Item Params
     *
     * @return array Array of section objects with id and enabled properties
     * @since 10.3.0
     */
    public function getSectionOrder(Registry $params): array
    {
        // Try new landing_layout JSON format first
        $landingLayout = $params->get('landing_layout');

        if (!empty($landingLayout)) {
            // Parse if string
            if (\is_string($landingLayout)) {
                try {
                    $landingLayout = json_decode($landingLayout, false, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    $landingLayout = null;
                }
            }

            if (\is_array($landingLayout) && \count($landingLayout) > 0) {
                $sections = [];
                foreach ($landingLayout as $item) {
                    if (\is_object($item) && isset($item->id)) {
                        $sections[] = (object) [
                            'id'      => $item->id,
                            'enabled' => $item->enabled ?? true,
                        ];
                    }
                }

                if (\count($sections) > 0) {
                    return $sections;
                }
            }
        }

        // Fall back to legacy headingorder_* fields
        return $this->getSectionOrderFromLegacy($params);
    }

    /**
     * Get section order from legacy headingorder_* fields
     *
     * @param   Registry  $params  Item Params
     *
     * @return array Array of section objects
     * @since 10.3.0
     */
    private function getSectionOrderFromLegacy(Registry $params): array
    {
        $sections = [];
        $used     = [];

        // Map of section IDs to their show* param names
        $showParams = [
            'teachers'     => 'showteachers',
            'series'       => 'showseries',
            'books'        => 'showbooks',
            'topics'       => 'showtopics',
            'locations'    => 'showlocations',
            'messagetypes' => 'showmessagetypes',
            'years'        => 'showyears',
        ];

        for ($i = 1; $i <= 7; $i++) {
            $sectionId = $params->get('headingorder_' . $i);

            if ($sectionId && !\in_array($sectionId, $used, true)) {
                $showParam = $showParams[$sectionId] ?? ('show' . $sectionId);
                $enabled   = (int) $params->get($showParam) === 1;

                $sections[] = (object) [
                    'id'      => $sectionId,
                    'enabled' => $enabled,
                ];

                $used[] = $sectionId;
            }
        }

        return $sections;
    }

    /**
     * Get all landing data in one query
     *
     * @param   Registry  $params  Item Params
     *
     * @return array
     * @since 10.1.0
     */
    public function getLandingData(Registry $params): array
    {
        $this->initDependencies();
        $language = $this->getLanguageFilter($params);

        $queries = [];
        $types   = [];

        for ($i = 1; $i <= 7; $i++) {
            $type = $params->get('headingorder_' . $i);
            if ($type && (int) $params->get('show' . $type) === 1 && !\in_array($type, $types)) {
                $types[] = $type;
                $q       = $this->buildQueryForType($type, $language);
                if ($q) {
                    $queries[] = $q;
                }
            }
        }

        if (empty($queries)) {
            return [];
        }

        $query = array_shift($queries);

        if (!empty($queries)) {
            foreach ($queries as $q) {
                $query->union($q);
            }
        }

        $this->db->setQuery($query);
        $allResults = $this->db->loadObjectList();

        $grouped = [];
        foreach ($allResults as $row) {
            $grouped[$row->type][] = $row;
        }

        foreach ($grouped as $type => &$items) {
            $orderKey = match ($type) {
                'teachers'     => 'teachers_order',
                'series'       => 'series_order',
                'locations'    => 'locations_order',
                'messagetypes' => 'messagetypes_order',
                'topics'       => 'topics_order',
                'books'        => 'books_order',
                'years'        => 'years_order',
                default        => 'landing_default_order',
            };

            $sortCol = match ($type) {
                'books', 'years' => 'id',
                default => 'text',
            };

            $order = $this->getSortOrder($params, $orderKey);

            usort($items, function ($a, $b) use ($order, $sortCol) {
                $valA = $a->$sortCol;
                $valB = $b->$sortCol;

                if ($valA == $valB) {
                    return 0;
                }
                $res = ($valA < $valB) ? -1 : 1;
                return ($order === 'DESC') ? -$res : $res;
            });
        }

        return $grouped;
    }

    /**
     * Build subquery for a specific type
     *
     * @param   string  $type      The type of item
     * @param   string  $language  Language filter
     *
     * @return DatabaseQuery|null
     * @since 10.1.0
     */
    private function buildQueryForType(string $type, string $language): ?DatabaseQuery
    {
        $query = $this->db->getQuery(true);
        $null  = $this->db->quote('');

        switch ($type) {
            case 'teachers':
                $query->select(
                    'DISTINCT ' . $this->db->quoteName('a.id') . ', '
                    . $this->db->quoteName('a.teachername', 'text') . ', '
                    . $this->db->quoteName('a.landing_show') . ', '
                    . $null . ' AS ' . $this->db->quoteName('params') . ', '
                    . $this->db->quote('teachers') . ' AS ' . $this->db->quoteName('type')
                )
                    ->from($this->db->quoteName('#__bsms_teachers', 'a'))
                    ->innerJoin(
                        $this->db->quoteName('#__bsms_study_teachers', 'stj') . ' ON '
                        . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('stj.teacher_id')
                    )
                    ->innerJoin(
                        $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                        . $this->db->quoteName('b.id') . ' = ' . $this->db->quoteName('stj.study_id')
                    )
                    ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                    ->where($this->db->quoteName('a.published') . ' = 1')
                    ->where($this->db->quoteName('a.landing_show') . ' > 0');
                $this->addAccessFilter($query);
                break;

            case 'series':
                $query->select(
                    'DISTINCT ' . $this->db->quoteName('a.id') . ', '
                    . $this->db->quoteName('a.series_text', 'text') . ', '
                    . $this->db->quoteName('a.landing_show') . ', '
                    . $null . ' AS ' . $this->db->quoteName('params') . ', '
                    . $this->db->quote('series') . ' AS ' . $this->db->quoteName('type')
                )
                    ->from($this->db->quoteName('#__bsms_series', 'a'))
                    ->innerJoin(
                        $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                        . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('b.series_id')
                    )
                    ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                    ->where($this->db->quoteName('b.published') . ' = 1')
                    ->where($this->db->quoteName('a.published') . ' = 1');

                // Cascading series date window for non-admin users
                if (!$this->user->authorise('core.edit.state', 'com_proclaim') && !$this->user->authorise('core.edit', 'com_proclaim')) {
                    $nullDate = $this->db->quote($this->db->getNullDate());
                    $nowDate  = $this->db->quote((new Date())->toSql());
                    $query->where('(' . $this->db->quoteName('a.publish_up') . ' = ' . $nullDate . ' OR ' . $this->db->quoteName('a.publish_up') . ' <= ' . $nowDate . ')')
                        ->where('(' . $this->db->quoteName('a.publish_down') . ' = ' . $nullDate . ' OR ' . $this->db->quoteName('a.publish_down') . ' >= ' . $nowDate . ')');
                }

                $this->addAccessFilter($query);
                break;

            case 'locations':
                $query->select(
                    'DISTINCT ' . $this->db->quoteName('a.id') . ', '
                    . $this->db->quoteName('a.location_text', 'text') . ', '
                    . $this->db->quoteName('a.landing_show') . ', '
                    . $null . ' AS ' . $this->db->quoteName('params') . ', '
                    . $this->db->quote('locations') . ' AS ' . $this->db->quoteName('type')
                )
                    ->from($this->db->quoteName('#__bsms_locations', 'a'))
                    ->innerJoin(
                        $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                        . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('b.location_id')
                    )
                    ->where($this->db->quoteName('b.location_id') . ' > 0')
                    ->where($this->db->quoteName('a.published') . ' = 1')
                    ->where($this->db->quoteName('b.published') . ' = 1')
                    ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                    ->where($this->db->quoteName('a.landing_show') . ' > 0');
                $this->addAccessFilter($query);
                break;

            case 'messagetypes':
                $query->select(
                    'DISTINCT ' . $this->db->quoteName('a.id') . ', '
                    . $this->db->quoteName('a.message_type', 'text') . ', '
                    . $this->db->quoteName('a.landing_show') . ', '
                    . $null . ' AS ' . $this->db->quoteName('params') . ', '
                    . $this->db->quote('messagetypes') . ' AS ' . $this->db->quoteName('type')
                )
                    ->from($this->db->quoteName('#__bsms_message_type', 'a'))
                    ->innerJoin(
                        $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                        . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('b.messagetype')
                    )
                    ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                    ->where($this->db->quoteName('b.published') . ' = 1')
                    ->where($this->db->quoteName('a.landing_show') . ' > 0');
                $this->addAccessFilter($query);
                break;

            case 'topics':
                $query->select(
                    'DISTINCT ' . $this->db->quoteName('a.id') . ', '
                    . $this->db->quoteName('a.topic_text', 'text') . ', '
                    . '0 AS ' . $this->db->quoteName('landing_show') . ', '
                    . $this->db->quoteName('a.params', 'params') . ', '
                    . $this->db->quote('topics') . ' AS ' . $this->db->quoteName('type')
                )
                    ->from($this->db->quoteName('#__bsms_studies', 'b'))
                    ->join(
                        'LEFT',
                        $this->db->quoteName('#__bsms_studytopics', 'st') . ' ON '
                        . $this->db->quoteName('b.id') . ' = ' . $this->db->quoteName('st.study_id')
                    )
                    ->join(
                        'LEFT',
                        $this->db->quoteName('#__bsms_topics', 'a') . ' ON '
                        . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('st.topic_id')
                    )
                    ->where($this->db->quoteName('a.published') . ' = 1')
                    ->where($this->db->quoteName('b.published') . ' = 1')
                    ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')');
                $this->addAccessFilter($query, 'b.access');
                break;

            case 'books':
                $query->select(
                    'DISTINCT ' . $this->db->quoteName('a.booknumber', 'id') . ', '
                    . $this->db->quoteName('a.bookname', 'text') . ', '
                    . '0 AS ' . $this->db->quoteName('landing_show') . ', '
                    . $null . ' AS ' . $this->db->quoteName('params') . ', '
                    . $this->db->quote('books') . ' AS ' . $this->db->quoteName('type')
                )
                    ->from($this->db->quoteName('#__bsms_books', 'a'))
                    ->innerJoin(
                        $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                        . $this->db->quoteName('a.booknumber') . ' = ' . $this->db->quoteName('b.booknumber')
                    )
                    ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                    ->where($this->db->quoteName('b.published') . ' = 1');
                $this->addAccessFilter($query);
                break;

            case 'years':
                $query->select(
                    'DISTINCT YEAR(' . $this->db->quoteName('b.studydate') . ') AS ' . $this->db->quoteName('id') . ', '
                    . 'YEAR(' . $this->db->quoteName('b.studydate') . ') AS ' . $this->db->quoteName('text') . ', '
                    . '0 AS ' . $this->db->quoteName('landing_show') . ', '
                    . $null . ' AS ' . $this->db->quoteName('params') . ', '
                    . $this->db->quote('years') . ' AS ' . $this->db->quoteName('type')
                )
                    ->from($this->db->quoteName('#__bsms_studies', 'b'))
                    ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                    ->where($this->db->quoteName('b.published') . ' = 1');
                $this->addAccessFilter($query, 'b.access');
                break;

            default:
                return null;
        }

        return $query;
    }

    /**
     * Get Locations for Landing Page
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      Item ID (unused, kept for BC)
     * @param   array|null $items  Optional pre-fetched items
     *
     * @return string
     *
     * @throws   \Exception
     * @since    8.0.0
     * @deprecated 10.3.0 Use getLocationsLandingData() with layout rendering instead
     */
    public function getLocationsLandingPage(Registry $params, int $id = 0, ?array $items = null): string
    {
        $this->initDependencies();

        $template         = (int) $params->get('studieslisttemplateid', 1);
        $limit            = (int) $params->get('landinglocationslimit', 10000) ?: 10000;
        $locationuselimit = (int) $params->get('landinglocationsuselimit', 0);

        if ($items === null) {
            $order    = $this->getSortOrder($params, 'locations_order');
            $language = $this->getLanguageFilter($params);

            $query = $this->db->getQuery(true);
            $query->select('DISTINCT ' . $this->db->quoteName('a') . '.*')
                ->from($this->db->quoteName('#__bsms_locations', 'a'))
                ->innerJoin(
                    $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                    . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('b.location_id')
                )
                ->where($this->db->quoteName('b.location_id') . ' > 0')
                ->where($this->db->quoteName('a.published') . ' = 1')
                ->where($this->db->quoteName('b.published') . ' = 1')
                ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                ->where($this->db->quoteName('a.landing_show') . ' > 0')
                ->group($this->db->quoteName('a.id'))
                ->order($this->db->quoteName('a.location_text') . ' ' . $order);

            $this->addAccessFilter($query);

            // Apply database-level LIMIT for grid mode (uselimit=0) to avoid
            // loading thousands of rows just to hide them with CSS display:none
            if ($locationuselimit === 0 && $limit < 10000) {
                $this->db->setQuery($query, 0, $limit);
            } else {
                $this->db->setQuery($query);
            }

            $items = $this->db->loadObjectList();
        } else {
            foreach ($items as $item) {
                $item->location_text = $item->text;
            }
        }

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
     * @param   array|null $items  Optional pre-fetched items
     *
     * @return string
     *
     * @since    8.0.0
     * @deprecated 10.3.0 Use getTeacherLandingData() with layout rendering instead
     */
    public function getTeacherLandingPage(Registry $params, int $id = 0, ?array $items = null): string
    {
        $this->initDependencies();

        $template        = (int) $params->get('teachertemplateid', 1);
        $limit           = (int) $params->get('landingteacherslimit', 10000) ?: 10000;
        $teacheruselimit = (int) $params->get('landingteachersuselimit', 0);
        $linkTo          = (int) $params->get('linkto', 0);

        if ($items === null) {
            $order    = $this->getSortOrder($params, 'teachers_order');
            $language = $this->getLanguageFilter($params);

            $query = $this->db->getQuery(true);
            $query->select('DISTINCT ' . $this->db->quoteName('a') . '.*')
                ->from($this->db->quoteName('#__bsms_teachers', 'a'))
                ->innerJoin(
                    $this->db->quoteName('#__bsms_study_teachers', 'stj') . ' ON '
                    . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('stj.teacher_id')
                )
                ->innerJoin(
                    $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                    . $this->db->quoteName('b.id') . ' = ' . $this->db->quoteName('stj.study_id')
                )
                ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                ->where($this->db->quoteName('a.published') . ' = 1')
                ->where($this->db->quoteName('a.landing_show') . ' > 0')
                ->group($this->db->quoteName('a.id'))
                ->order($this->db->quoteName('a.teachername') . ' ' . $order);

            $this->addAccessFilter($query);

            if ($teacheruselimit === 0 && $limit < 10000) {
                $this->db->setQuery($query, 0, $limit);
            } else {
                $this->db->setQuery($query);
            }

            $items = $this->db->loadObjectList();
        } else {
            foreach ($items as $item) {
                $item->teachername = $item->text;
            }
        }

        if (empty($items)) {
            return '';
        }

        if ($teacheruselimit === 0) {
            $linkBuilder = fn ($item) => '<div class="col" style="margin-right:7px">'
                . $this->buildTeacherLink($item, $template, $linkTo) . '</div>';
            return $this->buildGridHtml($items, $limit, $linkBuilder, 'showhideteachers');
        }

        return $this->buildLandingTableHtml(
            $items,
            fn ($item) => $this->buildTeacherLink($item, $template, $linkTo, 'landinglink'),
            'showhideteachers'
        );
    }

    /**
     * Build a link for a teacher item.
     *
     * @param   object  $item      The teacher item object.
     * @param   int     $template  The template ID.
     * @param   int     $linkTo    The link type (0 for sermons, 1 for teacher profile).
     * @param   string  $class     Optional CSS class for the link.
     *
     * @return  string  The generated HTML anchor tag.
     * @since   10.1.0
     */
    private function buildTeacherLink(object $item, int $template, int $linkTo, string $class = ''): string
    {
        $classAttr = $class ? ' class="' . $class . '"' : '';

        if ($linkTo === 0) {
            // Link to a filtered list of sermons
            $url = 'index.php?option=com_proclaim&view=cwmsermons&t=' . $template
                . '&sendingview=landing&filter_teacher=' . $item->id;
        } else {
            // Link to the teacher's profile page
            $url = 'index.php?option=com_proclaim&view=cwmteacher&id=' . $item->id . '&t=' . $template;
        }

        return '<a' . $classAttr . ' href="' . Route::_($url) . '">' . $item->teachername . '</a>';
    }

    /**
     * Get Series for LandingPage
     *
     * @param   Registry  $params  Item Params
     * @param   int       $id      ID (unused, kept for BC)
     * @param   array|null $items  Optional pre-fetched items
     *
     * @return string
     *
     * @since    8.0.0
     * @deprecated 10.3.0 Use getSeriesLandingData() with layout rendering instead
     */
    public function getSeriesLandingPage(Registry $params, int $id = 0, ?array $items = null): string
    {
        $this->initDependencies();

        $template       = (int) $params->get('serieslisttemplateid', 1);
        $limit          = (int) $params->get('landingserieslimit', 10000) ?: 10000;
        $seriesuselimit = (int) $params->get('landingseriesuselimit', 0);
        $seriesLinkTo   = (int) $params->get('series_linkto', 0);

        if ($items === null) {
            $order    = $this->getSortOrder($params, 'series_order');
            $language = $this->getLanguageFilter($params);

            $query = $this->db->getQuery(true);
            $query->select('DISTINCT ' . $this->db->quoteName('a') . '.*')
                ->from($this->db->quoteName('#__bsms_series', 'a'))
                ->innerJoin(
                    $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                    . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('b.series_id')
                )
                ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                ->where($this->db->quoteName('b.published') . ' = 1')
                ->where($this->db->quoteName('a.published') . ' = 1')
                ->group($this->db->quoteName('a.id'))
                ->order($this->db->quoteName('a.series_text') . ' ' . $order);

            // Cascading series date window for non-admin users
            if (!$this->user->authorise('core.edit.state', 'com_proclaim') && !$this->user->authorise('core.edit', 'com_proclaim')) {
                $nullDate = $this->db->quote($this->db->getNullDate());
                $nowDate  = $this->db->quote((new Date())->toSql());
                $query->where('(' . $this->db->quoteName('a.publish_up') . ' = ' . $nullDate . ' OR ' . $this->db->quoteName('a.publish_up') . ' <= ' . $nowDate . ')')
                    ->where('(' . $this->db->quoteName('a.publish_down') . ' = ' . $nullDate . ' OR ' . $this->db->quoteName('a.publish_down') . ' >= ' . $nowDate . ')');
            }

            $this->addAccessFilter($query);

            if ($seriesuselimit === 0 && $limit < 10000) {
                $this->db->setQuery($query, 0, $limit);
            } else {
                $this->db->setQuery($query);
            }

            $items = $this->db->loadObjectList();
        } else {
            foreach ($items as $item) {
                $item->series_text = $item->text;
            }
        }

        if (empty($items)) {
            return '';
        }

        $buildLink = function ($item, $class = '') use ($template, $seriesLinkTo) {
            if ($seriesLinkTo === 0) {
                $url = 'index.php?option=com_proclaim&view=cwmsermons&filter_series=' . $item->id
                    . '&sendingview=landing&t=' . $template;
            } else {
                $url = 'index.php?option=com_proclaim&view=cwmseriesdisplay&id='
                    . $item->id . '&sendingview=landing&t=' . $template;
            }
            return '<a href="' . Route::_($url) . '">' . $item->series_text . '</a>';
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
     * @param   array|null $items  Optional pre-fetched items
     *
     * @return string
     *
     * @since    8.0.0
     * @deprecated 10.3.0 Use getYearsLandingData() with layout rendering instead
     */
    public function getYearsLandingPage(Registry $params, int $id = 0, ?array $items = null): string
    {
        $this->initDependencies();

        $template = (int) $params->get('studieslisttemplateid', 1);
        $limit    = (int) $params->get('landingyearslimit', 10000) ?: 10000;

        if ($items === null) {
            $order    = $this->getSortOrder($params, 'years_order');
            $language = $this->getLanguageFilter($params);

            $query = $this->db->getQuery(true);
            $query->select('DISTINCT YEAR(' . $this->db->quoteName('studydate') . ') AS ' . $this->db->quoteName('theYear'))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where($this->db->quoteName('language') . ' IN (' . $language . ')')
                ->where($this->db->quoteName('published') . ' = 1')
                ->group('YEAR(' . $this->db->quoteName('studydate') . ')')
                ->order('YEAR(' . $this->db->quoteName('studydate') . ') ' . $order);

            $this->addAccessFilter($query, 'access');

            if ($limit < 10000) {
                $this->db->setQuery($query, 0, $limit);
            } else {
                $this->db->setQuery($query);
            }

            $items = $this->db->loadObjectList();
        } else {
            foreach ($items as $item) {
                $item->theYear = $item->id;
            }
        }

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
     * @param   array|null $items  Optional pre-fetched items
     *
     * @return string
     *
     * @since    8.0.0
     * @deprecated 10.3.0 Use getTopicsLandingData() with layout rendering instead
     */
    public function getTopicsLandingPage(Registry $params, int $id = 0, ?array $items = null): string
    {
        $this->initDependencies();

        $template = (int) $params->get('studieslisttemplateid', 1);
        $limit    = (int) $params->get('landingtopicslimit', 10000) ?: 10000;

        if ($items === null) {
            $order    = $this->getSortOrder($params, 'topics_order');
            $language = $this->getLanguageFilter($params);

            $query = $this->db->getQuery(true);
            $query->select(
                'DISTINCT ' . $this->db->quoteName('#__bsms_topics.id') . ', '
                . $this->db->quoteName('#__bsms_topics.topic_text') . ', '
                . $this->db->quoteName('#__bsms_topics.params', 'topic_params')
            )
                ->from($this->db->quoteName('#__bsms_studies'))
                ->join(
                    'LEFT',
                    $this->db->quoteName('#__bsms_studytopics') . ' ON '
                    . $this->db->quoteName('#__bsms_studies.id') . ' = ' . $this->db->quoteName('#__bsms_studytopics.study_id')
                )
                ->join(
                    'LEFT',
                    $this->db->quoteName('#__bsms_topics') . ' ON '
                    . $this->db->quoteName('#__bsms_topics.id') . ' = ' . $this->db->quoteName('#__bsms_studytopics.topic_id')
                )
                ->where($this->db->quoteName('#__bsms_topics.published') . ' = 1')
                ->where($this->db->quoteName('#__bsms_studies.published') . ' = 1')
                ->where($this->db->quoteName('#__bsms_studies.language') . ' IN (' . $language . ')')
                ->group($this->db->quoteName('#__bsms_topics.id'))
                ->order($this->db->quoteName('#__bsms_topics.topic_text') . ' ' . $order);

            $this->addAccessFilter($query, '#__bsms_studies.access');

            if ($limit < 10000) {
                $this->db->setQuery($query, 0, $limit);
            } else {
                $this->db->setQuery($query);
            }

            $items = $this->db->loadObjectList();
        } else {
            foreach ($items as $item) {
                $item->topic_text = $item->text;
                if (!property_exists($item, 'topic_params') && property_exists($item, 'params')) {
                    $item->topic_params = $item->params;
                }
            }
        }

        if (empty($items)) {
            return '';
        }

        $linkBuilder = fn ($item) => '<div class="col" style="margin-right:7px">'
            . '<a href="' . Route::_('index.php?option=com_proclaim&view=cwmsermons&filter_topic=' . $item->id
            . '&sendingview=cwmlanding&t=' . $template) . '">'
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
     * @param   array|null $items  Optional pre-fetched items
     *
     * @return string
     *
     * @since    8.0.0
     * @deprecated 10.3.0 Use getMessageTypesLandingData() with layout rendering instead
     */
    public function getMessageTypesLandingPage(Registry $params, int $id = 0, ?array $items = null): string
    {
        $this->initDependencies();

        $template            = (int) $params->get('studieslisttemplateid', 1);
        $limit               = (int) $params->get('landingmessagetypeslimit', 10000) ?: 10000;
        $messagetypeuselimit = (int) $params->get('landingmessagetypeuselimit', 0);

        if ($items === null) {
            $order    = $this->getSortOrder($params, 'messagetypes_order');
            $language = $this->getLanguageFilter($params);

            $query = $this->db->getQuery(true);
            $query->select('DISTINCT ' . $this->db->quoteName('a') . '.*')
                ->from($this->db->quoteName('#__bsms_message_type', 'a'))
                ->innerJoin(
                    $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                    . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('b.messagetype')
                )
                ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                ->where($this->db->quoteName('b.published') . ' = 1')
                ->where($this->db->quoteName('a.landing_show') . ' > 0')
                ->group($this->db->quoteName('a.id'))
                ->order($this->db->quoteName('a.message_type') . ' ' . $order);

            $this->addAccessFilter($query);

            if ($messagetypeuselimit === 0 && $limit < 10000) {
                $this->db->setQuery($query, 0, $limit);
            } else {
                $this->db->setQuery($query);
            }

            $items = $this->db->loadObjectList();
        } else {
            foreach ($items as $item) {
                $item->message_type = $item->text;
            }
        }

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
     * @param   array|null $items  Optional pre-fetched items
     *
     * @return string
     * @since    8.0.0
     * @deprecated 10.3.0 Use getBooksLandingData() with layout rendering instead
     */
    public function getBooksLandingPage(Registry $params, int $id = 0, ?array $items = null): string
    {
        $this->initDependencies();

        $template = (int) $params->get('studieslisttemplateid', 1);
        $limit    = (int) $params->get('landingbookslimit', 10000) ?: 10000;

        if ($items === null) {
            $order    = $this->getSortOrder($params, 'books_order');
            $language = $this->getLanguageFilter($params);

            $query = $this->db->getQuery(true);
            $query->select('DISTINCT ' . $this->db->quoteName('a') . '.*')
                ->from($this->db->quoteName('#__bsms_books', 'a'))
                ->innerJoin(
                    $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                    . $this->db->quoteName('a.booknumber') . ' = ' . $this->db->quoteName('b.booknumber')
                )
                ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                ->where($this->db->quoteName('b.published') . ' = 1')
                ->group($this->db->quoteName('a.bookname'))
                ->order($this->db->quoteName('a.booknumber') . ' ' . $order);

            $this->addAccessFilter($query);

            if ($limit < 10000) {
                $this->db->setQuery($query, 0, $limit);
            } else {
                $this->db->setQuery($query);
            }

            $items = $this->db->loadObjectList();
        } else {
            foreach ($items as $item) {
                $item->booknumber = $item->id;
                $item->bookname   = $item->text;
            }
        }

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

    // =========================================================================
    // Data-returning methods for layout-based rendering (Phase 1)
    // =========================================================================

    /**
     * Get structured data for any landing section by type.
     *
     * Dispatches to the per-type data method. Returns a standardized array
     * that layouts can render without knowing the section type.
     *
     * @param   string         $sectionId  Section type (teachers, series, etc.)
     * @param   Registry       $params     Template params
     * @param   array|null     $items      Optional pre-fetched items from union query
     *
     * @return  array{items: array, limit: int, useLimit: int, divId: string, hasImages: bool, sectionType: string}
     *
     * @since   10.3.0
     */
    public function getSectionData(string $sectionId, Registry $params, ?array $items = null): array
    {
        return match ($sectionId) {
            'teachers'     => $this->getTeacherLandingData($params, $items),
            'series'       => $this->getSeriesLandingData($params, $items),
            'locations'    => $this->getLocationsLandingData($params, $items),
            'messagetypes' => $this->getMessageTypesLandingData($params, $items),
            'topics'       => $this->getTopicsLandingData($params, $items),
            'books'        => $this->getBooksLandingData($params, $items),
            'years'        => $this->getYearsLandingData($params, $items),
            default        => ['items' => [], 'limit' => 0, 'useLimit' => 0, 'divId' => '', 'hasImages' => false, 'sectionType' => $sectionId],
        };
    }

    /**
     * Get teacher data for landing page layouts.
     *
     * @param   Registry    $params  Template params
     * @param   array|null  $items   Optional pre-fetched items
     *
     * @return  array  Standardized section data array
     *
     * @since   10.3.0
     */
    public function getTeacherLandingData(Registry $params, ?array $items = null): array
    {
        $this->initDependencies();

        $template = (int) $params->get('teachertemplateid', 1);
        $limit    = (int) $params->get('landingteacherslimit', 10000) ?: 10000;
        $useLimit = (int) $params->get('landingteachersuselimit', 0);
        $linkTo   = (int) $params->get('linkto', 0);

        // Always run own query — pre-fetched union items lack image columns
        $order    = $this->getSortOrder($params, 'teachers_order');
        $language = $this->getLanguageFilter($params);

        $query = $this->db->getQuery(true);
        $query->select('DISTINCT ' . $this->db->quoteName('a') . '.*')
            ->from($this->db->quoteName('#__bsms_teachers', 'a'))
            ->innerJoin(
                $this->db->quoteName('#__bsms_study_teachers', 'stj') . ' ON '
                . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('stj.teacher_id')
            )
            ->innerJoin(
                $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                . $this->db->quoteName('b.id') . ' = ' . $this->db->quoteName('stj.study_id')
            )
            ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
            ->where($this->db->quoteName('a.published') . ' = 1')
            ->where($this->db->quoteName('a.landing_show') . ' > 0')
            ->group($this->db->quoteName('a.id'))
            ->order($this->db->quoteName('a.teachername') . ' ' . $order);

        $this->addAccessFilter($query);

        if ($useLimit === 0 && $limit < 10000) {
            $this->db->setQuery($query, 0, $limit);
        } else {
            $this->db->setQuery($query);
        }

        $items = $this->db->loadObjectList();

        $result = [];

        foreach ($items as $item) {
            if ($linkTo === 0) {
                $url = 'index.php?option=com_proclaim&view=cwmsermons&t=' . $template
                    . '&sendingview=landing&filter_teacher=' . $item->id;
            } else {
                $url = 'index.php?option=com_proclaim&view=cwmteacher&id=' . $item->id . '&t=' . $template;
            }

            $image = null;
            if (!empty($item->teacher_thumbnail)) {
                $image = Cwmimages::getTeacherThumbnail($item->teacher_thumbnail, $item->thumb ?? '');
            }

            $result[] = [
                'id'           => (int) $item->id,
                'text'         => $item->teachername,
                'url'          => Route::_($url),
                'image'        => $image,
                'landing_show' => (int) ($item->landing_show ?? 0),
                'meta'         => $item->title ?? '',
            ];
        }

        return [
            'items'       => $result,
            'limit'       => $limit,
            'useLimit'    => $useLimit,
            'divId'       => 'showhideteachers',
            'hasImages'   => true,
            'sectionType' => 'teachers',
        ];
    }

    /**
     * Get series data for landing page layouts.
     *
     * @param   Registry    $params  Template params
     * @param   array|null  $items   Optional pre-fetched items
     *
     * @return  array  Standardized section data array
     *
     * @since   10.3.0
     */
    public function getSeriesLandingData(Registry $params, ?array $items = null): array
    {
        $this->initDependencies();

        $template     = (int) $params->get('serieslisttemplateid', 1);
        $limit        = (int) $params->get('landingserieslimit', 10000) ?: 10000;
        $useLimit     = (int) $params->get('landingseriesuselimit', 0);
        $seriesLinkTo = (int) $params->get('series_linkto', 0);

        // Always run own query — pre-fetched union items lack image columns
        $order    = $this->getSortOrder($params, 'series_order');
        $language = $this->getLanguageFilter($params);

        $query = $this->db->getQuery(true);
        $query->select('DISTINCT ' . $this->db->quoteName('a') . '.*')
            ->from($this->db->quoteName('#__bsms_series', 'a'))
            ->innerJoin(
                $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('b.series_id')
            )
            ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
            ->where($this->db->quoteName('b.published') . ' = 1')
            ->where($this->db->quoteName('a.published') . ' = 1')
            ->group($this->db->quoteName('a.id'))
            ->order($this->db->quoteName('a.series_text') . ' ' . $order);

        if (!$this->user->authorise('core.edit.state', 'com_proclaim') && !$this->user->authorise('core.edit', 'com_proclaim')) {
            $nullDate = $this->db->quote($this->db->getNullDate());
            $nowDate  = $this->db->quote((new Date())->toSql());
            $query->where('(' . $this->db->quoteName('a.publish_up') . ' = ' . $nullDate . ' OR ' . $this->db->quoteName('a.publish_up') . ' <= ' . $nowDate . ')')
                ->where('(' . $this->db->quoteName('a.publish_down') . ' = ' . $nullDate . ' OR ' . $this->db->quoteName('a.publish_down') . ' >= ' . $nowDate . ')');
        }

        $this->addAccessFilter($query);

        if ($useLimit === 0 && $limit < 10000) {
            $this->db->setQuery($query, 0, $limit);
        } else {
            $this->db->setQuery($query);
        }

        $items = $this->db->loadObjectList();

        $result = [];

        foreach ($items as $item) {
            if ($seriesLinkTo === 0) {
                $url = 'index.php?option=com_proclaim&view=cwmsermons&filter_series=' . $item->id
                    . '&sendingview=landing&t=' . $template;
            } else {
                $url = 'index.php?option=com_proclaim&view=cwmseriesdisplay&id='
                    . $item->id . '&sendingview=landing&t=' . $template;
            }

            $image = null;
            if (!empty($item->series_thumbnail)) {
                $image = Cwmimages::getSeriesThumbnail($item->series_thumbnail);
            }

            $result[] = [
                'id'           => (int) $item->id,
                'text'         => $item->series_text,
                'url'          => Route::_($url),
                'image'        => $image,
                'landing_show' => (int) ($item->landing_show ?? 0),
                'meta'         => !empty($item->description) ? mb_substr(trim(html_entity_decode(strip_tags($item->description), ENT_QUOTES, 'UTF-8')), 0, 120) : '',
            ];
        }

        return [
            'items'       => $result,
            'limit'       => $limit,
            'useLimit'    => $useLimit,
            'divId'       => 'showhideseries',
            'hasImages'   => true,
            'sectionType' => 'series',
        ];
    }

    /**
     * Get locations data for landing page layouts.
     *
     * @param   Registry    $params  Template params
     * @param   array|null  $items   Optional pre-fetched items
     *
     * @return  array  Standardized section data array
     *
     * @since   10.3.0
     */
    public function getLocationsLandingData(Registry $params, ?array $items = null): array
    {
        $this->initDependencies();

        $template = (int) $params->get('studieslisttemplateid', 1);
        $limit    = (int) $params->get('landinglocationslimit', 10000) ?: 10000;
        $useLimit = (int) $params->get('landinglocationsuselimit', 0);

        if ($items === null) {
            $order    = $this->getSortOrder($params, 'locations_order');
            $language = $this->getLanguageFilter($params);

            $query = $this->db->getQuery(true);
            $query->select('DISTINCT ' . $this->db->quoteName('a') . '.*')
                ->from($this->db->quoteName('#__bsms_locations', 'a'))
                ->innerJoin(
                    $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                    . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('b.location_id')
                )
                ->where($this->db->quoteName('b.location_id') . ' > 0')
                ->where($this->db->quoteName('a.published') . ' = 1')
                ->where($this->db->quoteName('b.published') . ' = 1')
                ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                ->where($this->db->quoteName('a.landing_show') . ' > 0')
                ->group($this->db->quoteName('a.id'))
                ->order($this->db->quoteName('a.location_text') . ' ' . $order);

            $this->addAccessFilter($query);

            if ($useLimit === 0 && $limit < 10000) {
                $this->db->setQuery($query, 0, $limit);
            } else {
                $this->db->setQuery($query);
            }

            $items = $this->db->loadObjectList();
        } else {
            foreach ($items as $item) {
                $item->location_text = $item->text;
            }
        }

        $result = [];

        foreach ($items as $item) {
            $url = 'index.php?option=com_proclaim&view=cwmsermons&sendingview=cwmlanding'
                . '&filter_location=' . $item->id . '&t=' . $template;

            $result[] = [
                'id'           => (int) $item->id,
                'text'         => $item->location_text,
                'url'          => Route::_($url),
                'image'        => null,
                'landing_show' => (int) ($item->landing_show ?? 0),
                'meta'         => null,
            ];
        }

        return [
            'items'       => $result,
            'limit'       => $limit,
            'useLimit'    => $useLimit,
            'divId'       => 'showhidelocations',
            'hasImages'   => false,
            'sectionType' => 'locations',
        ];
    }

    /**
     * Get message types data for landing page layouts.
     *
     * @param   Registry    $params  Template params
     * @param   array|null  $items   Optional pre-fetched items
     *
     * @return  array  Standardized section data array
     *
     * @since   10.3.0
     */
    public function getMessageTypesLandingData(Registry $params, ?array $items = null): array
    {
        $this->initDependencies();

        $template = (int) $params->get('studieslisttemplateid', 1);
        $limit    = (int) $params->get('landingmessagetypeslimit', 10000) ?: 10000;
        $useLimit = (int) $params->get('landingmessagetypeuselimit', 0);

        if ($items === null) {
            $order    = $this->getSortOrder($params, 'messagetypes_order');
            $language = $this->getLanguageFilter($params);

            $query = $this->db->getQuery(true);
            $query->select('DISTINCT ' . $this->db->quoteName('a') . '.*')
                ->from($this->db->quoteName('#__bsms_message_type', 'a'))
                ->innerJoin(
                    $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                    . $this->db->quoteName('a.id') . ' = ' . $this->db->quoteName('b.messagetype')
                )
                ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                ->where($this->db->quoteName('b.published') . ' = 1')
                ->where($this->db->quoteName('a.landing_show') . ' > 0')
                ->group($this->db->quoteName('a.id'))
                ->order($this->db->quoteName('a.message_type') . ' ' . $order);

            $this->addAccessFilter($query);

            if ($useLimit === 0 && $limit < 10000) {
                $this->db->setQuery($query, 0, $limit);
            } else {
                $this->db->setQuery($query);
            }

            $items = $this->db->loadObjectList();
        } else {
            foreach ($items as $item) {
                $item->message_type = $item->text;
            }
        }

        $result = [];

        foreach ($items as $item) {
            $url = 'index.php?option=com_proclaim&view=cwmsermons&sendingview=cwmlanding'
                . '&filter_messagetype=' . $item->id . '&t=' . $template;

            $result[] = [
                'id'           => (int) $item->id,
                'text'         => $item->message_type,
                'url'          => Route::_($url),
                'image'        => null,
                'landing_show' => (int) ($item->landing_show ?? 0),
                'meta'         => null,
            ];
        }

        return [
            'items'       => $result,
            'limit'       => $limit,
            'useLimit'    => $useLimit,
            'divId'       => 'showhidemessagetypes',
            'hasImages'   => false,
            'sectionType' => 'messagetypes',
        ];
    }

    /**
     * Get topics data for landing page layouts.
     *
     * @param   Registry    $params  Template params
     * @param   array|null  $items   Optional pre-fetched items
     *
     * @return  array  Standardized section data array
     *
     * @since   10.3.0
     */
    public function getTopicsLandingData(Registry $params, ?array $items = null): array
    {
        $this->initDependencies();

        $template = (int) $params->get('studieslisttemplateid', 1);
        $limit    = (int) $params->get('landingtopicslimit', 10000) ?: 10000;

        if ($items === null) {
            $order    = $this->getSortOrder($params, 'topics_order');
            $language = $this->getLanguageFilter($params);

            $query = $this->db->getQuery(true);
            $query->select(
                'DISTINCT ' . $this->db->quoteName('#__bsms_topics.id') . ', '
                . $this->db->quoteName('#__bsms_topics.topic_text') . ', '
                . $this->db->quoteName('#__bsms_topics.params', 'topic_params')
            )
                ->from($this->db->quoteName('#__bsms_studies'))
                ->join(
                    'LEFT',
                    $this->db->quoteName('#__bsms_studytopics') . ' ON '
                    . $this->db->quoteName('#__bsms_studies.id') . ' = ' . $this->db->quoteName('#__bsms_studytopics.study_id')
                )
                ->join(
                    'LEFT',
                    $this->db->quoteName('#__bsms_topics') . ' ON '
                    . $this->db->quoteName('#__bsms_topics.id') . ' = ' . $this->db->quoteName('#__bsms_studytopics.topic_id')
                )
                ->where($this->db->quoteName('#__bsms_topics.published') . ' = 1')
                ->where($this->db->quoteName('#__bsms_studies.published') . ' = 1')
                ->where($this->db->quoteName('#__bsms_studies.language') . ' IN (' . $language . ')')
                ->group($this->db->quoteName('#__bsms_topics.id'))
                ->order($this->db->quoteName('#__bsms_topics.topic_text') . ' ' . $order);

            $this->addAccessFilter($query, '#__bsms_studies.access');

            if ($limit < 10000) {
                $this->db->setQuery($query, 0, $limit);
            } else {
                $this->db->setQuery($query);
            }

            $items = $this->db->loadObjectList();
        } else {
            foreach ($items as $item) {
                $item->topic_text = $item->text;
                if (!property_exists($item, 'topic_params') && property_exists($item, 'params')) {
                    $item->topic_params = $item->params;
                }
            }
        }

        $result = [];

        foreach ($items as $item) {
            $url = 'index.php?option=com_proclaim&view=cwmsermons&filter_topic=' . $item->id
                . '&sendingview=cwmlanding&t=' . $template;

            $result[] = [
                'id'           => (int) $item->id,
                'text'         => Cwmtranslated::getTopicItemTranslated($item),
                'url'          => Route::_($url),
                'image'        => null,
                'landing_show' => 0,
                'meta'         => null,
            ];
        }

        return [
            'items'       => $result,
            'limit'       => $limit,
            'useLimit'    => 0,
            'divId'       => 'showhidetopics',
            'hasImages'   => false,
            'sectionType' => 'topics',
        ];
    }

    /**
     * Get books data for landing page layouts.
     *
     * @param   Registry    $params  Template params
     * @param   array|null  $items   Optional pre-fetched items
     *
     * @return  array  Standardized section data array
     *
     * @since   10.3.0
     */
    public function getBooksLandingData(Registry $params, ?array $items = null): array
    {
        $this->initDependencies();

        $template = (int) $params->get('studieslisttemplateid', 1);
        $limit    = (int) $params->get('landingbookslimit', 10000) ?: 10000;

        if ($items === null) {
            $order    = $this->getSortOrder($params, 'books_order');
            $language = $this->getLanguageFilter($params);

            $query = $this->db->getQuery(true);
            $query->select('DISTINCT ' . $this->db->quoteName('a') . '.*')
                ->from($this->db->quoteName('#__bsms_books', 'a'))
                ->innerJoin(
                    $this->db->quoteName('#__bsms_studies', 'b') . ' ON '
                    . $this->db->quoteName('a.booknumber') . ' = ' . $this->db->quoteName('b.booknumber')
                )
                ->where($this->db->quoteName('b.language') . ' IN (' . $language . ')')
                ->where($this->db->quoteName('b.published') . ' = 1')
                ->group($this->db->quoteName('a.bookname'))
                ->order($this->db->quoteName('a.booknumber') . ' ' . $order);

            $this->addAccessFilter($query);

            if ($limit < 10000) {
                $this->db->setQuery($query, 0, $limit);
            } else {
                $this->db->setQuery($query);
            }

            $items = $this->db->loadObjectList();
        } else {
            foreach ($items as $item) {
                $item->booknumber = $item->id;
                $item->bookname   = $item->text;
            }
        }

        $result = [];

        foreach ($items as $item) {
            $url = 'index.php?option=com_proclaim&view=cwmsermons&sendingview=cwmlanding'
                . '&filter_book=' . $item->booknumber . '&t=' . $template;

            $result[] = [
                'id'           => (int) $item->booknumber,
                'text'         => Text::_($item->bookname),
                'url'          => Route::_($url),
                'image'        => null,
                'landing_show' => 0,
                'meta'         => null,
            ];
        }

        return [
            'items'       => $result,
            'limit'       => $limit,
            'useLimit'    => 0,
            'divId'       => 'showhidebooks',
            'hasImages'   => false,
            'sectionType' => 'books',
        ];
    }

    /**
     * Get years data for landing page layouts.
     *
     * @param   Registry    $params  Template params
     * @param   array|null  $items   Optional pre-fetched items
     *
     * @return  array  Standardized section data array
     *
     * @since   10.3.0
     */
    public function getYearsLandingData(Registry $params, ?array $items = null): array
    {
        $this->initDependencies();

        $template = (int) $params->get('studieslisttemplateid', 1);
        $limit    = (int) $params->get('landingyearslimit', 10000) ?: 10000;

        if ($items === null) {
            $order    = $this->getSortOrder($params, 'years_order');
            $language = $this->getLanguageFilter($params);

            $query = $this->db->getQuery(true);
            $query->select('DISTINCT YEAR(' . $this->db->quoteName('studydate') . ') AS ' . $this->db->quoteName('theYear'))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where($this->db->quoteName('language') . ' IN (' . $language . ')')
                ->where($this->db->quoteName('published') . ' = 1')
                ->group('YEAR(' . $this->db->quoteName('studydate') . ')')
                ->order('YEAR(' . $this->db->quoteName('studydate') . ') ' . $order);

            $this->addAccessFilter($query, 'access');

            if ($limit < 10000) {
                $this->db->setQuery($query, 0, $limit);
            } else {
                $this->db->setQuery($query);
            }

            $items = $this->db->loadObjectList();
        } else {
            foreach ($items as $item) {
                $item->theYear = $item->id;
            }
        }

        $result = [];

        foreach ($items as $item) {
            $url = 'index.php?option=com_proclaim&view=cwmsermons&sendingview=cwmlanding'
                . '&filter_year=' . $item->theYear . '&t=' . $template;

            $result[] = [
                'id'           => (int) $item->theYear,
                'text'         => (string) $item->theYear,
                'url'          => Route::_($url),
                'image'        => null,
                'landing_show' => 0,
                'meta'         => null,
            ];
        }

        return [
            'items'       => $result,
            'limit'       => $limit,
            'useLimit'    => 0,
            'divId'       => 'showhideyears',
            'hasImages'   => false,
            'sectionType' => 'years',
        ];
    }
}
