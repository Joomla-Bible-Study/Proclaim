<?php

/**
 * @package        Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Service;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Service\ProclaimNomenuRules as NomenuRules;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Routing class of com_proclaim
 *
 * @since  3.3
 */
class Router extends RouterView
{
    /**
     * Flag to remove IDs
     *
     * @var    bool
     * @since  10.0.0
     */
    protected bool $noIDs = false;

    /**
     * The db
     *
     * @var DatabaseInterface
     *
     * @since  4.0.0
     */
    private DatabaseInterface $db;

    private string|int $cacheiddata = 0;

    /**
     * Proclaim Component router constructor
     *
     * @param   SiteApplication            $app              The application object
     * @param   AbstractMenu               $menu             The menu object to work with
     * @param   ?CategoryFactoryInterface  $categoryFactory  The category object
     * @param   ?DatabaseInterface         $db               The database object
     *
     * @since 10.0.0
     */
    public function __construct(
        SiteApplication $app,
        AbstractMenu $menu,
        ?CategoryFactoryInterface $categoryFactory = null,
        ?DatabaseInterface $db = null
    ) {
        $this->db = $db;

        $params      = ComponentHelper::getParams('com_proclaim');
        $this->noIDs = (bool)$params->get('sef_ids', true);

        // Landing page view
        $landingPage = new RouterViewConfiguration('cwmlandingpage');
        $this->registerView($landingPage);

        // Sermons list view (parent for single sermon)
        $sermons = new RouterViewConfiguration('cwmsermons');
        $this->registerView($sermons);

        // Single sermon view - child of sermons list
        // URL: /sermons-menu/sermon-alias (instead of /sermons-menu/cwmsermon/sermon-alias)
        $sermon = new RouterViewConfiguration('cwmsermon');
        $sermon->setKey('id')->setParent($sermons);
        $this->registerView($sermon);

        // Teachers list view (parent for single teacher)
        $teachers = new RouterViewConfiguration('cwmteachers');
        $this->registerView($teachers);

        // Single teacher view - child of teachers list
        // URL: /teachers-menu/teacher-alias (instead of /teachers-menu/cwmteacher/teacher-alias)
        $teacher = new RouterViewConfiguration('cwmteacher');
        $teacher->setKey('id')->setParent($teachers);
        $this->registerView($teacher);

        // Series list view (parent for single series)
        $seriesDisplays = new RouterViewConfiguration('cwmseriesdisplays');
        $this->registerView($seriesDisplays);

        // Single series display view - child of series list
        // URL: /series-menu/series-alias (instead of /series-menu/cwmseriesdisplay/series-alias)
        $seriesDisplay = new RouterViewConfiguration('cwmseriesdisplay');
        $seriesDisplay->setKey('id')->setParent($seriesDisplays);
        $this->registerView($seriesDisplay);

        // Latest sermon view - child of sermons list
        $latest = new RouterViewConfiguration('cwmlatest');
        $latest->setKey('id')->setParent($sermons);
        $this->registerView($latest);

        // Podcast list view (parent for single podcast)
        $podcastList = new RouterViewConfiguration('cwmpodcastlist');
        $this->registerView($podcastList);

        // Podcast display view - child of podcast list
        // URL: /podcasts-menu/podcast-alias (instead of /podcasts-menu/cwmpodcastdisplay/podcast-alias)
        $podcastDisplay = new RouterViewConfiguration('cwmpodcastdisplay');
        $podcastDisplay->setKey('id')->setParent($podcastList);
        $this->registerView($podcastDisplay);

        // Series Podcast list view (parent for single series podcast display)
        $seriesPodcastList = new RouterViewConfiguration('cwmseriespodcastlist');
        $this->registerView($seriesPodcastList);

        // Series Podcast display view - child of series podcast list
        // URL: /series-podcasts-menu/series-alias (instead of /series-podcasts-menu/cwmseriespodcastdisplay/series-alias)
        $seriesPodcastDisplay = new RouterViewConfiguration('cwmseriespodcastdisplay');
        $seriesPodcastDisplay->setKey('id')->setParent($seriesPodcastList);
        $this->registerView($seriesPodcastDisplay);

        // Popup view
        $popup = new RouterViewConfiguration('cwmpopup');
        $this->registerView($popup);

        // Terms view
        $terms = new RouterViewConfiguration('cwmterms');
        $terms->setKey('id');
        $this->registerView($terms);

        parent::__construct($app, $menu);

        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new NoMenuRules($this));
    }

    /**
     * Method to get the segment(s) for a sermon
     *
     * @param   Integer|string  $id     ID of the article to retrieve the segments for
     * @param   array           $query  The request that is built right now
     *
     * @return  array  The segments of this item
     * @since 10.0.0
     */
    public function getCWMSermonSegment(int|string $id, array $query): array
    {
        if ((int)$this->cacheiddata !== (int)$id && !str_contains((string)$id, ':')) {
            $id      = (int)$id;
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('alias'))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where($this->db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $this->db->setQuery($dbquery);

            $id .= ':' . $this->db->loadResult();

            $this->cacheiddata = $id;
        }

        if ((int)$this->cacheiddata === (int)$id) {
            $id = $this->cacheiddata;
        }

        if ($this->noIDs) {
            [$void, $segment] = explode(':', $id, 2);

            return [$void => $segment];
        }

        return [(int)$id => $id];
    }

    /**
     * Method to get the segment(s) for a form
     *
     * @param   string  $id     ID of the article form to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array  The segments of this item
     *
     * @since   3.7.3
     */
    public function getCWMSermonsSegment(string $id, array $query): array
    {
        return []; // $this->getCWMSermonSegment($id, $query);
    }

    /**
     * Method to get the segment(s) for a form
     *
     * @param   string  $id     ID of the article form to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array  The segments of this item
     *
     * @since   3.7.3
     */
    public function getCWMTeachersSegment($id, $query): array
    {
        return $this->getCWMTeacherSegment($id, $query);
    }

    /**
     * Method to get the segment(s) for a teacher
     *
     * @param   int|string  $id     ID of the article to retrieve the segments for
     * @param   array       $query  The request that is built right now
     *
     * @return  array  The segments of this item
     * @since 10.0.0
     */
    public function getCWMTeacherSegment(int|string $id, array $query): array
    {
        if ((int)$this->cacheiddata !== (int)$id && !str_contains((string)$id, ':')) {
            $id      = (int)$id;
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('alias'))
                ->from($this->db->quoteName('#__bsms_teachers'))
                ->where($this->db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $this->db->setQuery($dbquery);

            $id .= ':' . $this->db->loadResult();

            $this->cacheiddata = $id;
        }

        if ((int)$this->cacheiddata === (int)$id) {
            $id = $this->cacheiddata;
        }

        if ($this->noIDs) {
            [$void, $segment] = explode(':', $id, 2);

            return [$void => $segment];
        }

        return [(int)$id => $id];
    }

    /**
     * @Method to get the segment(s) for a sermon
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since  10.0.0
     */
    public function getCWMSermonsId(string $segment, array $query): mixed
    {
        return $this->getCWMSermonId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a sermon
     *
     * @param   string  $segment  Segment of the article to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since   10.0.0
     */
    public function getCWMSermonId(string $segment, array $query): mixed
    {
        if ($this->noIDs) {
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where(
                    [
                        $this->db->quoteName('alias') . ' = :alias',

                    ]
                )
                ->bind(':alias', $segment);

            $this->db->setQuery($dbquery);
            $id = (int)$this->db->loadResult();

            // Return -1 for unknown aliases so routing completes and the
            // view can render a friendly "not found" page with suggestions
            // instead of Joomla's bare 404 error template.
            return $id ?: -1;
        }

        return (int)$segment;
    }

    /**
     * @Method to get the segment(s) for a Teacher
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since  10.0.0
     */
    public function getCWMTeachersId(string $segment, array $query): mixed
    {
        return $this->getCWMTeacherId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a teacher
     *
     * @param   string  $segment  Segment of the article to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  int   The id of this item or false
     * @since   10.0.0
     */
    public function getCWMTeacherId(string $segment, array $query): int
    {
        if ($this->noIDs) {
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__bsms_teachers'))
                ->where(
                    [
                        $this->db->quoteName('alias') . ' = :alias',

                    ]
                )
                ->bind(':alias', $segment);

            $this->db->setQuery($dbquery);

            return (int)$this->db->loadResult();
        }

        return (int)$segment;
    }

    /**
     * Method to get the segment(s) for a series
     *
     * @param   string  $id     ID of the article form to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array  The segments of this item
     *
     * @since   3.7.3
     */
    public function getCWMSeriesDisplaysSegment(string $id, array $query): array
    {
        return $this->getCWMSeriesDisplaySegment($id, $query);
    }

    /**
     * Method to get the segment(s) for a series
     *
     * @param   mixed  $id     ID of the article to retrieve the segments for
     * @param   array  $query  The request that is built right now
     *
     * @return  array  The segments of this item
     * @since 10.0.0
     */
    public function getCWMSeriesDisplaySegment(mixed $id, array $query): array
    {
        if (!str_contains((string)$id, ':')) {
            $id      = (int)$id;
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('alias'))
                ->from($this->db->quoteName('#__bsms_series'))
                ->where($this->db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $this->db->setQuery($dbquery);

            $id .= ':' . $this->db->loadResult();
        }

        if ($this->noIDs) {
            [$void, $segment] = explode(':', $id, 2);

            return [$void => $segment];
        }

        return [(int)$id => $id];
    }

    /**
     * @Method to get the segment(s) for a series
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since  10.0.0
     */
    public function getCWMSeriesDisplaysId(string $segment, array $query): mixed
    {
        return $this->getCWMSeriesDisplayId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a series
     *
     * @param   string  $segment  Segment of the article to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  int   The id of this item or false
     * @since   10.0.0
     */
    public function getCWMSeriesDisplayId(string $segment, array $query): int
    {
        if ($this->noIDs) {
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__bsms_series'))
                ->where(
                    [
                        $this->db->quoteName('alias') . ' = :alias',

                    ]
                )
                ->bind(':alias', $segment);

            $this->db->setQuery($dbquery);

            return (int)$this->db->loadResult();
        }

        return (int)$segment;
    }

    /**
     * Method to get the segment(s) for a Comment
     *
     * @param   string  $segment  Segment of the article to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  int   The id of this item or false
     * @since   10.0.0
     */
    public function getCWMLatestId(string $segment, array $query): int
    {
        if ($this->noIDs) {
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where($this->db->quoteName('alias') . ' = :alias')
                ->where($this->db->quoteName('published') . ' = 1')
                ->bind(':alias', $segment);

            $this->db->setQuery($dbquery);

            return (int)$this->db->loadResult();
        }

        return (int)$segment;
    }

    /**
     * Method to get the segment(s) for a series podcast
     *
     * @param   mixed   $id     ID of the article form to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array  The segments of this item
     *
     * @since   10.0.0
     */
    public function getCWMSeriesPodcastDisplaySegment(mixed $id, array $query): array
    {
        return $this->getCWMSeriesDisplaySegment($id, $query);
    }

    /**
     * @Method to get the segment(s) for a series podcast
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since  10.0.0
     */
    public function getCWMSeriesPodcastDisplayId(string $segment, array $query): mixed
    {
        return $this->getCWMSeriesDisplayId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a series podcast list
     *
     * @param   mixed   $id     ID of the article form to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array  The segments of this item
     *
     * @since   10.0.0
     */
    public function getCWMSeriesPodcastListSegment(mixed $id, array $query): array
    {
        return [];
    }
}
