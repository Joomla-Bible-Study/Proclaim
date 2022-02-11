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
use Joomla\CMS\Categories\CategoryInterface;
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
		$this->db = $db;
		$this->categoryFactory = $categoryFactory;
		$params      = ComponentHelper::getParams('com_proclaim');
		$this->noIDs = (bool) $params->get('sef_ids');

		$Sermons = new RouterViewConfiguration('CWMSermons');
		$Sermons->setKey('id');
		$this->registerView($Sermons);

		$Sermon = new RouterViewConfiguration('CWMSermon');
		$Sermon->setKey('id');
		$this->registerView($Sermon);

		$Teachers = new RouterViewConfiguration('CWMTeachers');
		$Teachers->setKey('id');
		$this->registerView($Teachers);

		$Teachers = new RouterViewConfiguration('CWMTeacher');
		$Teachers->setKey('id');
		$this->registerView($Teachers);

		$SeriesDisplay = new RouterViewConfiguration('CWMSeriesDisplay');
		$SeriesDisplay->setKey('id');
		$this->registerView($SeriesDisplay);

		$SeriesDisplays = new RouterViewConfiguration('CWMSeriesDisplays');
		$SeriesDisplays->setKey('id');
		$this->registerView($SeriesDisplays);

		$commentform = new RouterViewConfiguration('CWMcommentform');
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

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}
}
