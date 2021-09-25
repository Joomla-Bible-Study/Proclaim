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

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use CWM\Component\Proclaim\Site\Service\ProclaimNomenuRules as NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;

/**
 * Routing class of com_proclaim
 *
 * @since  3.3
 */
class Router extends RouterView
{
	protected $noIDs = false;

	/**
	 * The category factory
	 *
	 * @var CategoryFactoryInterface
	 *
	 * @since  4.0.0
	 */
	private $categoryFactory;

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
	 */
	public function __construct(SiteApplication $app, AbstractMenu $menu,
	                            CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		$this->categoryFactory = $categoryFactory;
		$this->db              = $db;

		$params = ComponentHelper::getParams('com_proclaim');
		$this->noIDs = (bool) $params->get('sef_ids');

		$proclaim = new RouterViewConfiguration('CWMSermons');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMSermon');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMTeachers');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMTeachers');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMSeriesDisplay');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMSeriesDisplays');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMcommentform');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMCommentList');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMLandingPage');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMLatest');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMMediaFileForm');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

		$proclaim = new RouterViewConfiguration('CWMMediaFileList');
		$proclaim->setKey('id');
		$this->registerView($proclaim);

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