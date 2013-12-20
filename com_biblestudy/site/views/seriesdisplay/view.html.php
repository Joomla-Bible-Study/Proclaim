<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package        BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * View class for SeriesDisplay
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 *
 * @todo     need to recode to JBSM/MVC Standers and update the way we do params.  bcc
 */
class BiblestudyViewSeriesdisplay extends JViewLegacy
{

	/**
	 * State
	 *
	 * @var array
	 */
	protected $state = null;

	/**
	 * Item
	 *
	 * @var array
	 */
	protected $item = null;

	/**
	 * Items
	 *
	 * @var array
	 */
	protected $items = null;

	/**
	 * Pagination
	 *
	 * @var array
	 */
	protected $pagination = null;

	/** @var  JObject Admin */
	protected $admin;

	/** @var  JRegistry Admin Params */
	protected $admin_params;

	/** @var  JObject Page */
	protected $page;

	/** @var  JObject Series Studies */
	protected $seriesstudies;

	/** @var  JObject Template */
	protected $template;

	/** @var  JRegistry Params */
	protected $params;

	/** @var  string Article */
	protected $article;

	/** @var  string Passage Link */
	protected $passage_link;

	/** @var  JObject Studies */
	protected $studies;

	/** @var  string Request URL */
	protected $request_url;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{

		$mainframe = JFactory::getApplication();
		$input     = new JInput;
		$option    = $input->get('option', '', 'cmd');

		// @todo Need ot move all this into a JS/CSS Loaders so we don't call this twice.
		$document = JFactory::getDocument();
		$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');

		$document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/player/jwplayer.js');
		$pathway       = $mainframe->getPathWay();
		$contentConfig = JFactory::getApplication('site')->getParams();
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap-responsive.css');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap-extended.css');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap-responsive-min.css');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap.css');
		$this->document->addStyleSheet(JURI::base() . 'media/com_biblestudy/jui/css/bootstrap-min.css');


		// Get the menu item object
		// Load the Admin settings and params from the template
		$this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
		$this->loadHelper('params');
		$this->admin        = JBSMParams::getAdmin();
		$this->admin_params = $this->admin->params;
		$items              = $this->get('Item');
		$this->state        = $this->get('State');

		// Get studies associated with this series
		$mainframe->setUserState('sid', $items->id);
		$this->seriesstudies = $this->get('Studies');

		// Get the series image
		$images              = new JBSMImages;
		$image               = $images->getSeriesThumbnail($items->series_thumbnail);
		$items->image        = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="" />';
		$teacherimage        = $images->getTeacherThumbnail($items->thumb, $image2 = null);
		$items->teacherimage = '<img src="' . $teacherimage->path . '" height="' . $teacherimage->height . '" width="'
			. $teacherimage->width . '" alt="" />';
		$t                   = $input->get('t', '1', 'int');
		$this->t             = $t;
		$params              = $this->state->get('params');

		// Convert parameter fields to objects.
		$this->admin_params = $this->admin->params;
		$css                = $params->get('css');

		if ($css <= "-1")
		{
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
		}
		else
		{
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
		}

		$items->slug = $items->alias ? ($items->id . ':' . $items->alias) : str_replace(' ', '-', htmlspecialchars_decode($items->series_text, ENT_QUOTES))
			. ':' . $items->id;

		if ($params->get('seriesdisplaytemplate') > 0)
		{
			// Get studies associated with the series
			$pagebuilder = new JBSMPagebuilder;
			$whereitem   = $items->id;
			$wherefield  = 'study.series_id';

			$limit       = $params->get('series_detail_limit', 10);
			$seriesorder = $params->get('series_detail_order', 'DESC');
			$studies     = $pagebuilder->studyBuilder($whereitem, $wherefield, $params, $this->admin_params, $limit, $seriesorder);

			foreach ($studies AS $i => $study)
			{
				$pelements               = $pagebuilder->buildPage($study, $params, $this->admin_params);
				$studies[$i]->scripture1 = $pelements->scripture1;
				$studies[$i]->scripture2 = $pelements->scripture2;
				$studies[$i]->media      = $pelements->media;
				$studies[$i]->duration   = $pelements->duration;
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
			$this->seriesstudies = $studies;
			$this->page          = $items;
		}
		// Prepare meta information (under development)
		if ($params->get('metakey'))
		{
			$document->setMetadata('keywords', $params->get('metakey'));
		}
		elseif (!$params->get('metakey'))
		{
			$document->setMetadata('keywords', $this->admin_params->get('metakey'));
		}

		if ($params->get('metadesc'))
		{
			$document->setDescription($params->get('metadesc'));
		}
		elseif (!$params->get('metadesc'))
		{
			$document->setDescription($this->admin_params->get('metadesc'));
		}

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();

		if (!in_array($items->access, $groups) && $items->access)
		{
			$mainframe->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		$studies = $items;

		$input->set('returnid', $items->id);

		// Passage link to BibleGateway
		$plugin = JPluginHelper::getPlugin('content', 'scripturelinks');

		if ($plugin)
		{
			// Convert parameter fields to objects.
			$registry = new JRegistry;
			$registry->loadString($plugin->params);
			$st_params = $registry;
			$version   = $st_params->get('bible_version');
		}
		$windowopen = "window.open(this.href,this.target,'width=800,height=500,scrollbars=1');return false;";


		if (isset($items->description))
		{
			//$items->text        = $items->description;
			//$description        = $pagebuilder->runContentPlugins($items, $params);
			//$items->description = $description->text;
		}
		// End process prepare content plugins
		$this->template = $this->state->get('template');
		$this->params   = $params;
		$this->items    = $items;

		// --$this->article = $article;
		// --$this->passage_link = $passage_link;
		$this->studies     = $studies;
		$uri               = new JUri;
		$stringuri         = $uri->toString();
		$this->request_url = $stringuri;

		// Let's get the studies from this series from the sermons model
		//JLoader::import('joomla.application.component.modellist');


		parent::display($tpl);
	}

}
