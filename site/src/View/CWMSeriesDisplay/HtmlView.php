<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\View\CWMSeriesDisplay;
// No Direct Access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use CWM\Component\Proclaim\Site\Helper\CWMPagebuilder;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Html\HTMLHelper;
use CWM\Component\Proclaim\Site\Helper\CWMImages;
use Joomla\CMS\Uri\Uri;
use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;
use CWM\Component\Proclaim\Site\Helper\CWMMedia;
use CWM\Component\Proclaim\Site\Helper\CWMListing;
use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * View class for SeriesDisplay
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * State
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $state = null;

	/**
	 * Item
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $item = null;

	/**
	 * Items
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $items = null;

	/**
	 * Pagination
	 *
	 * @var array
	 *
	 * @since 7.0
	 */
	protected $pagination = null;

	/** @var  object Admin
	 *
	 * @since 7.0 */
	protected $admin;

	/** @var  Registry Admin Params
	 *
	 * @since 7.0 */
	protected $adminParams;

	/** @var  object Page
	 *
	 * @since 7.0 */
	protected $page;

	/** @var  object Series Studies
	 *
	 * @since 7.0 */
	protected $seriesstudies;

	/** @var  TableTemplate Template
	 *
	 * @since 7.0 */
	protected $template;

	/** @var  Registry Params
	 *
	 * @since 7.0 */
	protected $params;

	/** @var  string Article
	 *
	 * @since 7.0 */
	protected $article;

	/** @var  string Passage Link
	 *
	 * @since 7.0 */
	protected $passageLink;

	/** @var  object Studies
	 *
	 * @since 7.0 */
	protected $studies;

	/** @var  string Request URL
	 *
	 * @since 7.0 */
	protected $requestUrl;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since 7.0
	 */
	public function display($tpl = null)
	{
		$mainframe = Factory::getApplication();
		$input     = Factory::getApplication();
		$document = Factory::getApplication()->getDocument();

		// Get the menu item object
		// Load the Admin settings and params from the template
		$items              = $this->get('Item');
		$this->state        = $this->get('State');

		/** @var Registry $params */
		$params             = $this->state->template->params;
		$this->template     = $this->state->get('template');

		// Get studies associated with this series
		$mainframe->setUserState('sid', $items->id);
		$this->seriesstudies = $this->get('Studies');

		// Get the series image
		$images              = new CWMImages;
		$image               = $images->getSeriesThumbnail($items->series_thumbnail);
		$items->image        = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="" />';
		$teacherimage        = $images->getTeacherThumbnail($items->thumb, $image2 = null);
		$items->teacherimage = '<img src="' . $teacherimage->path . '" height="' . $teacherimage->height . '" width="'
			. $teacherimage->width . '" alt="" />';

		HtmlHelper::_('biblestudy.framework');
		HtmlHelper::_('biblestudy.loadCss', $params, null, 'font-awesome');

		$items->slug = $items->alias ? ($items->id . ':' . $items->alias) : str_replace(' ', '-', htmlspecialchars_decode($items->series_text, ENT_QUOTES))
			. ':' . $items->id;

		if ($params->get('useexpert_list') > 0 || is_string($params->get('seriesdisplaytemplate')) == true )
		{
			// Get studies associated with the series
			$pagebuilder = new CWMPageBuilder;
			$whereitem   = $items->id;
			$wherefield  = 'study.series_id';

			$limit       = $params->get('series_detail_limit', 10);
			$seriesorder = $params->get('series_detail_order', 'DESC');
			$studies     = $pagebuilder->studyBuilder($whereitem, $wherefield, $params, $limit, $seriesorder, $this->template);

			foreach ($studies AS $i => $study)
			{
				$pelements               = $pagebuilder->buildPage($study, $params, $this->template);
				$studies[$i]->scripture1 = $pelements->scripture1;
				$studies[$i]->scripture2 = $pelements->scripture2;
				$studies[$i]->media      = $pelements->media;
				$studies[$i]->studydate  = $pelements->studydate;
				$studies[$i]->topics     = $pelements->topics;

				if (isset($pelements->study_thumbnail))
				{
					$studies[$i]->study_thumbnail = $pelements->study_thumbnail;
				}
				else
				{
					$studies[$i]->study_thumbnail = null;
				}

				if (isset($pelements->series_thumbnail))
				{
					$studies[$i]->series_thumbnail = $pelements->series_thumbnail;
				}
				else
				{
					$studies[$i]->series_thumbnail = null;
				}

				$studies[$i]->detailslink = $pelements->detailslink;

				if (isset($pelements->studyintro))
				{
					$studies[$i]->studyintro = $pelements->studyintro;
				}

				if (isset($pelements->secondary_reference))
				{
					$studies[$i]->secondary_reference = $pelements->secondary_reference;
				}
				else
				{
					$studies[$i]->secondary_reference = '';
				}

				if (isset($pelements->sdescription))
				{
					$studies[$i]->sdescription = $pelements->sdescription;
				}
				else
				{
					$studies[$i]->sdescription = '';
				}
			}

			$this->page          = $items;
		}

		// Prepare meta information (under development)
		if ($params->get('metakey'))
		{
			$document->setMetaData('keywords', $params->get('metakey'));
		}

		if ($params->get('metadesc'))
		{
			$document->setDescription($params->get('metadesc'));
		}

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user   = Factory::getUser();
		$groups = $user->getAuthorisedViewLevels();

		if (!in_array($items->access, $groups) && $items->access)
		{
			$mainframe->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		$input->set('returnid', $items->id);

		// Passage link to BibleGateway
		$plugin = PluginHelper::getPlugin('content', 'scripturelinks');

		if ($plugin)
		{
			// Convert parameter fields to objects.
			$registry = new Registry;
			$registry->loadString($plugin->params);
			$stParams = $registry;
			$version   = $stParams->get('bible_version');
		}

		// End process prepare content plugins
		$this->params      = & $params;
		$this->items       = $items;
		$this->studies     = $studies;
		$uri               = new Uri;
		$stringuri         = $uri->toString();
		$this->requestUrl  = $stringuri;

		parent::display($tpl);
	}
}
