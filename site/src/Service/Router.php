<?php
/**
 * @package     Proclaim.Site
 * @subpackage  com_Proclaim
 *
 * @copyright   Copyright (C) 2007 - 2021 Christian Web Ministries.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Site\Service;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

//use Joomla\CMS\Component\Router\Rules\NomenuRules;
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
     * @var    boolean
     * @since  10.0.0
     */
    protected $noIDs = false;

    /**
     * The db
     *
     * @var DatabaseInterface
     *
     * @since  4.0.0
     */
    private $db;

    private $cacheiddata = '0';

    /**
     * @var CategoryFactoryInterface|null
     * @since version
     */
    private ?CategoryFactoryInterface $categoryFactory;

    /**
     * Proclaim Component router constructor
     *
     * @param   SiteApplication           $app              The application object
     * @param   AbstractMenu              $menu             The menu object to work with
     * @param   CategoryFactoryInterface  $categoryFactory  The category object
     * @param   DatabaseInterface         $db               The database object
     *
     * @since 10.0.0
     */
    public function __construct(
        SiteApplication $app,
        AbstractMenu $menu,
        CategoryFactoryInterface $categoryFactory = null,
        DatabaseInterface $db = null
    ) {
        $this->db = $db;

        $params      = ComponentHelper::getParams('com_proclaim');
        $this->noIDs = (bool)$params->get('sef_ids', true);

        $landingPage = new RouterViewConfiguration('cwmlandingpage');
        $this->registerView($landingPage);

        $landingPage = new RouterViewConfiguration('cwmlandingpage');
        $this->registerView($landingPage);

        $Sermons = new RouterViewConfiguration('cwmsermons');
        $this->registerView($Sermons);

        $Sermons = new RouterViewConfiguration('cwmsermons');
        $this->registerView($Sermons);

        $Sermon = new RouterViewConfiguration('cwmsermon');
        $Sermon->setKey('id');
        $this->registerView($Sermon);

        $Sermon = new RouterViewConfiguration('cwmsermon');
        $Sermon->setKey('id');
        $this->registerView($Sermon);

        $Teachers = new RouterViewConfiguration('cwmteachers');
        $this->registerView($Teachers);

        $Teachers = new RouterViewConfiguration('cwmteachers');
        $this->registerView($Teachers);

        $Teacher = new RouterViewConfiguration('cwmteacher');
        $Teacher->setKey('id');
        $this->registerView($Teacher);

        $Teacher = new RouterViewConfiguration('cwmteacher');
        $Teacher->setKey('id');
        $this->registerView($Teacher);

        $SeriesDisplay = new RouterViewConfiguration('cwmseriesdisplay');
        $SeriesDisplay->setKey('id');
        $this->registerView($SeriesDisplay);

        $SeriesDisplay = new RouterViewConfiguration('cwmseriesdisplay');
        $SeriesDisplay->setKey('id');
        $this->registerView($SeriesDisplay);

        $SeriesDisplays = new RouterViewConfiguration('cwmseriesdisplays');
        $this->registerView($SeriesDisplays);

        $SeriesDisplays = new RouterViewConfiguration('cwmseriesdisplays');
        $this->registerView($SeriesDisplays);

        $Latest = new RouterViewConfiguration('cwmlatest');
        $Latest->setKey('id');
        $this->registerView($Latest);

        $Latest = new RouterViewConfiguration('cwmlatest');
        $Latest->setKey('id');
        $this->registerView($Latest);

        $proclaim = new RouterViewConfiguration('cwmpodcastdisplay');
        $proclaim->setKey('id');
        $this->registerView($proclaim);

        $proclaim = new RouterViewConfiguration('cwmpodcastdisplay');
        $proclaim->setKey('id');
        $this->registerView($proclaim);

        $proclaim = new RouterViewConfiguration('cwmpopup');
        $this->registerView($proclaim);

        $proclaim = new RouterViewConfiguration('cwmpopup');
        $this->registerView($proclaim);

        $proclaim = new RouterViewConfiguration('cwmsqueezebox');
        $this->registerView($proclaim);

        $proclaim = new RouterViewConfiguration('Cwmsqueezebox');
        $this->registerView($proclaim);

        $proclaim = new RouterViewConfiguration('cwmterms');
        $proclaim->setKey('id');
        $this->registerView($proclaim);

        $proclaim = new RouterViewConfiguration('cwmterms');
        $proclaim->setKey('id');
        $this->registerView($proclaim);

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
    public function getCWMSermonSegment($id, array $query): array
    {
        if ((int)$this->cacheiddata !== (int)$id && !strpos($id, ':')) {
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

            return array($void => $segment);
        }

        return array((int)$id => $id);
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
     * Method to get the segment(s) for a sermon
     *
     * @param   string  $segment  Segment of the article to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since   10.0.0
     */

    /**
     * Method to get the segment(s) for a teacher
     *
     * @param   integer|string  $id     ID of the article to retrieve the segments for
     * @param   array           $query  The request that is built right now
     *
     * @return  array  The segments of this item
     * @since 10.0.0
     */
    public function getCWMTeacherSegment($id, array $query): array
    {
        if ((int)$this->cacheiddata !== (int)$id && !strpos($id, ':')) {
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
    public function getCWMSermonsId($segment, $query)
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
    public function getCWMSermonId($segment, $query)
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

            return (int)$this->db->loadResult();
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
    public function getCWMTeachersId($segment, $query)
    {
        return $this->getCWMTeacherId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a teacher
     *
     * @param   string  $segment  Segment of the article to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since   10.0.0
     */
    public function getCWMTeacherId($segment, $query)
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
    public function getCWMSeriesDisplaysSegment($id, $query)
    {
        return $this->getCWMSeriesDisplaySegment($id, $query);
    }

    /**
     * Method to get the segment(s) for a series
     *
     * @param   integer  $id     ID of the article to retrieve the segments for
     * @param   array    $query  The request that is built right now
     *
     * @return  array  The segments of this item
     * @since 10.0.0
     */
    public function getCWMSeriesDisplaySegment($id, array $query)
    {
        if (!strpos($id, ':')) {
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

            return array($void => $segment);
        }

        return array((int)$id => $id);
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
    public function getCWMSeriesDisplaysId($segment, $query)
    {
        return $this->getCWMSeriesDisplayId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a series
     *
     * @param   string  $segment  Segment of the article to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since   10.0.0
     */
    public function getCWMSeriesDisplayId($segment, $query)
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

            // Var_dump(($this->db->loadResult()));
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
     * @return  mixed   The id of this item or false
     * @since   10.0.0
     */
    public function getCWMLatestId($segment, $query)
    {
        if ($this->noIDs) {
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where('published = 1')
                ->order('studydate DESC LIMIT 1')
                ->bind(':alias', $segment);

            $this->db->setQuery($dbquery);

            return (int)$this->db->loadResult();
        }

        return (int)$segment;
    }
}
