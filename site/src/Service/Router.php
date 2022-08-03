<?php
/**
 * @package     Proclaim.Site
 * @subpackage  com_Proclaim
 *
 * @copyright   Copyright (C) 2007 - 2021 Christian Web Ministries.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Site\Service;

defined('_JEXEC') or die;

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
	public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		$this->db              = $db;
		$this->categoryFactory = $categoryFactory;
		$params                = ComponentHelper::getParams('com_proclaim');
		$this->noIDs           = (bool) $params->get('sef_ids');

		$Sermons = new RouterViewConfiguration('CWMSermons');
		$Sermons->setKey('id');
		$this->registerView($Sermons);

		$Sermon = new RouterViewConfiguration('CWMSermon');
		$Sermon->setKey('id');
		$this->registerView($Sermon);

		$Teachers = new RouterViewConfiguration('CWMTeachers');
		$Teachers->setKey('id');
		$this->registerView($Teachers);

		$Teacher = new RouterViewConfiguration('CWMTeacher');
		$Teacher->setKey('id');
		$this->registerView($Teacher);

		$SeriesDisplay = new RouterViewConfiguration('CWMSeriesDisplay');
		$SeriesDisplay->setKey('id');
		$this->registerView($SeriesDisplay);

		$SeriesDisplays = new RouterViewConfiguration('CWMSeriesDisplays');
		$SeriesDisplays->setKey('id');
		$this->registerView($SeriesDisplays);

		$commentform = new RouterViewConfiguration('CWMCommentForm');
		$commentform->setKey('id');
		$this->registerView($commentform);

		$CommentList = new RouterViewConfiguration('CWMCommentList');
		$CommentList->setKey('id');
		$this->registerView($CommentList);

		$LandingPage = new RouterViewConfiguration('CWMLandingPage');
		$LandingPage->setKey('id');
		$this->registerView($LandingPage);

		$Latest = new RouterViewConfiguration('CWMLatest');
		$Latest->setKey('id');
		$this->registerView($Latest);

		$MediaFileForm = new RouterViewConfiguration('CWMMediaFileForm');
		$MediaFileForm->setKey('id');
		$this->registerView($MediaFileForm);

		$MediaFileList = new RouterViewConfiguration('CWMMediaFileList');
		$MediaFileList->setKey('id');
		$this->registerView($MediaFileList);

		$proclaim = new RouterViewConfiguration('CWMMessageForm');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMMessageList');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMPodcastDisplay');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMPopUp');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMSqueezeBox');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMTerms');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$form = new RouterViewConfiguration('form');
		$form->setKey('a_id');
		$this->registerView($form);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NoMenuRules($this));
	}

	/**
	 * Method to get the segment(s) for a sermon
	 *
	 * @param   integer  $id     ID of the article to retrieve the segments for
	 * @param array $query  The request that is built right now
	 *
	 * @return  array  The segments of this item
	 * @since 10.0.0
	 */
	public function getCWMSermonSegment($id, array $query)
	{
		if (!strpos($id, ':'))
		{
			$id      = (int) $id;
			$dbquery = $this->db->getQuery(true);
			$dbquery->select($this->db->quoteName('alias'))
				->from($this->db->quoteName('#__bsms_studies'))
				->where($this->db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER);
			$this->db->setQuery($dbquery);

			$id .= ':' . $this->db->loadResult();
		}
		if ($this->noIDs)
		{
			list($void, $segment) = explode(':', $id, 2);
			return array($void => $segment);
		}

		return array((int) $id => $id);
	}
    /**
     * Method to get the segment(s) for a teacher
     *
     * @param   integer  $id     ID of the article to retrieve the segments for
     * @param array $query  The request that is built right now
     *
     * @return  array  The segments of this item
     * @since 10.0.0
     */
    public function getCWMTeacherSegment($id, array $query)
    {
        if (!strpos($id, ':'))
        {
            $id      = (int) $id;
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('alias'))
                ->from($this->db->quoteName('#__bsms_teachers'))
                ->where($this->db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $this->db->setQuery($dbquery);

            $id .= ':' . $this->db->loadResult();
        }
        if ($this->noIDs)
        {
            list($void, $segment) = explode(':', $id, 2);
            return array($void => $segment);
        }

        return array((int) $id => $id);
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
	public function getFormSegment($id, $query): array
    {
		return $this->getCWMSermonSegment($id, $query);
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
    public function getCWMSermonsSegment($id, $query): array
    {
        return $this->getCWMSermonSegment($id, $query);
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
	public function getCWMSermonId($segment, $query)
	{
		if ($this->noIDs)
		{
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

			return (int) $this->db->loadResult();
		}
//var_dump($segment);
		return (int) $segment;
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
        if ($this->noIDs)
        {
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

            return (int) $this->db->loadResult();
        }

        return (int) $segment;
    }
    /**
     * @Method to get the segment(s) for a sermon
     * @since 10.0.0
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getCWMSermonsId($segment, $query)
    {
        return $this->getCWMSermonId($segment, $query);
    }

    /**
     * @Method to get the segment(s) for a Teacher
     * @since 10.0.0
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getCWMTeachersId($segment, $query)
    {
        return $this->getCWMTeacherId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a series
     *
     * @param   integer  $id     ID of the article to retrieve the segments for
     * @param array $query  The request that is built right now
     *
     * @return  array  The segments of this item
     * @since 10.0.0
     */
    public function getCWMSeriesDisplaySegment($id, array $query)
    {
        if (!strpos($id, ':'))
        {
            $id      = (int) $id;
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('alias'))
                ->from($this->db->quoteName('#__bsms_series'))
                ->where($this->db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $this->db->setQuery($dbquery);

            $id .= ':' . $this->db->loadResult();
        }
        if ($this->noIDs)
        {
            list($void, $segment) = explode(':', $id, 2);
            return array($void => $segment);
        }

        return array((int) $id => $id);
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
     * @param   string  $segment  Segment of the article to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since   10.0.0
     */
    public function getCWMSeriesDisplayId($segment, $query)
    {
        if ($this->noIDs)
        {
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
//var_dump(($this->db->loadResult()));
            return (int) $this->db->loadResult();
        }

        return (int) $segment;
    }

    /**
     * @Method to get the segment(s) for a series
     * @since 10.0.0
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getCWMSeriesDisplaysId($segment, $query)
    {
        return $this->getCWMSeriesDisplayId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a comment
     *
     * @param   integer  $id     ID of the article to retrieve the segments for
     * @param array $query  The request that is built right now
     *
     * @return  array  The segments of this item
     * @since 10.0.0
     */
    public function getCWMCommentFormSegment($id, array $query)
    {
        if (!strpos($id, ':'))
        {
            $id      = (int) $id;
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('alias'))
                ->from($this->db->quoteName('#__bsms_comments'))
                ->where($this->db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $this->db->setQuery($dbquery);

            $id .= ':' . $this->db->loadResult();
        }
        if ($this->noIDs)
        {
            list($void, $segment) = explode(':', $id, 2);
            return array($void => $segment);
        }

        return array((int) $id => $id);
    }

    /**
     * Method to get the segment(s) for a comment
     *
     * @param   string  $id     ID of the article form to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array  The segments of this item
     *
     * @since   3.7.3
     */
    public function getCWMCommentListSegment($id, $query): array
    {
        return $this->getCWMCommentFormSegment($id, $query);
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
    public function getCWMCommentFormId($segment, $query)
    {
        if ($this->noIDs)
        {
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__bsms_comments'))
                ->where(
                    [
                        $this->db->quoteName('alias') . ' = :alias',

                    ]
                )
                ->bind(':alias', $segment);

            $this->db->setQuery($dbquery);

            return (int) $this->db->loadResult();
        }

        return (int) $segment;
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
        if ($this->noIDs)
        {
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where('published = 1')
                ->order('studydate DESC LIMIT 1')
                ->bind(':alias', $segment);

            $this->db->setQuery($dbquery);

            return (int) $this->db->loadResult();
        }

        return (int) $segment;
    }

    /**
     * @Method to get the segment(s) for a sermon
     * @since 10.0.0
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getCWMCommentListId($segment, $query)
    {
        return $this->getCWMCommentFormId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a Media File4
     *
     * @param   integer  $id     ID of the article to retrieve the segments for
     * @param array $query  The request that is built right now
     *
     * @return  array  The segments of this item
     * @since 10.0.0
     */
    public function getCWMMediaFileFormSegment($id, array $query)
    {
        if (!strpos($id, ':'))
        {
            $id      = (int) $id;
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('alias'))
                ->from($this->db->quoteName('#__bsms_comments'))
                ->where($this->db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $this->db->setQuery($dbquery);

            $id .= ':' . $this->db->loadResult();
        }
        if ($this->noIDs)
        {
            list($void, $segment) = explode(':', $id, 2);
            return array($void => $segment);
        }

        return array((int) $id => $id);
    }

    /**
     * Method to get the segment(s) for a Media File
     *
     * @param   string  $id     ID of the article form to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array  The segments of this item
     *
     * @since   3.7.3
     */
    public function getCWMMediaFileListSegment($id, $query): array
    {
        return $this->getCWMMediaFileFormSegment($id, $query);
    }


    /**
     * Method to get the segment(s) for a Media File
     *
     * @param   string  $segment  Segment of the article to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since   10.0.0
     */
    public function getCWMMediaFileFormId($segment, $query)
    {
        if ($this->noIDs)
        {
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__bsms_mediafiles'))
                ->where(
                    [
                        $this->db->quoteName('id') . ' = :id',

                    ]
                )
                ->bind(':id', $segment);

            $this->db->setQuery($dbquery);

            return (int) $this->db->loadResult();
        }

        return (int) $segment;
    }


    /**
     * @Method to get the segment(s) for a media file
     * @since 10.0.0
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getCWMMediaFileListId($segment, $query)
    {
        return $this->getCWMMediaFileFormId($segment, $query);
    }

    /**
     * Method to get the segment(s) for a message File
     *
     * @param   integer  $id     ID of the article to retrieve the segments for
     * @param array $query  The request that is built right now
     *
     * @return  array  The segments of this item
     * @since 10.0.0
     */
    public function getCWMMessageFileFormSegment($id, array $query)
    {
        if (!strpos($id, ':'))
        {
            $id      = (int) $id;
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('alias'))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where($this->db->quoteName('alias') . ' = :alias')
                ->bind(':alias', $id, ParameterType::INTEGER);
            $this->db->setQuery($dbquery);

            $id .= ':' . $this->db->loadResult();
        }
        if ($this->noIDs)
        {
            list($void, $segment) = explode(':', $id, 2);
            return array($void => $segment);
        }

        return array((int) $id => $id);
    }

    /**
     * Method to get the segment(s) for a message File
     *
     * @param   string  $id     ID of the article form to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array  The segments of this item
     *
     * @since   3.7.3
     */
    public function getCWMMessageFileListSegment($id, $query): array
    {
        return $this->getCWMMessageFileFormSegment($id, $query);
    }


    /**
     * Method to get the segment(s) for a message File
     *
     * @param   string  $segment  Segment of the article to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     * @since   10.0.0
     */
    public function getCWMMessageFileFormId($segment, $query)
    {
        if ($this->noIDs)
        {
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

            return (int) $this->db->loadResult();
        }

        return (int) $segment;
    }


    /**
     * @Method to get the segment(s) for a message file
     * @since 10.0.0
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getCWMMessageFileListId($segment, $query)
    {
        return $this->getCWMMessageFileFormId($segment, $query);
    }

}
