<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View class for SeriesDisplay
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
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

	/** @var  object Admin */
	protected $admin;

	/** @var  Registry Admin Params */
	protected $admin_params;

	/** @var  object Page */
	protected $page;

	/** @var  object Series Studies */
	protected $seriesstudies;

	/** @var  TableTemplate Template */
	protected $template;

	/** @var  Registry Params */
	protected $params;

	/** @var  string Article */
	protected $article;

	/** @var  string Passage Link */
	protected $passage_link;

	/** @var  object Studies */
	protected $studies;

	/** @var  string Request URL */
	protected $request_url;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{

		$mainframe = JFactory::getApplication();
		$input     = new JInput;
		$document = JFactory::getDocument();

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
		$images              = new JBSMImages;
		$image               = $images->getSeriesThumbnail($items->series_thumbnail);
		$items->image        = '<img src="' . $image->path . '" height="' . $image->height . '" width="' . $image->width . '" alt="" />';
		$teacherimage        = $images->getTeacherThumbnail($items->thumb, $image2 = null);
		$items->teacherimage = '<img src="' . $teacherimage->path . '" height="' . $teacherimage->height . '" width="'
			. $teacherimage->width . '" alt="" />';

		JHtml::_('biblestudy.framework');

		$items->slug = $items->alias ? ($items->id . ':' . $items->alias) : str_replace(' ', '-', htmlspecialchars_decode($items->series_text, ENT_QUOTES))
			. ':' . $items->id;

		if ($params->get('useexpert_list') > 0 || is_string($params->get('seriesdisplaytemplate')) == true )
		{
			// Get studies associated with the series
			$pagebuilder = new JBSMPageBuilder;
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
		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();

		if (!in_array($items->access, $groups) && $items->access)
		{
			$mainframe->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		$input->set('returnid', $items->id);

		// Passage link to BibleGateway
		$plugin = JPluginHelper::getPlugin('content', 'scripturelinks');

		if ($plugin)
		{
			// Convert parameter fields to objects.
			$registry = new Registry;
			$registry->loadString($plugin->params);
			$st_params = $registry;
			$version   = $st_params->get('bible_version');
		}

		// End process prepare content plugins
		$this->params      = & $params;
		$this->items       = $items;
		$this->studies     = $studies;
		$uri               = new JUri;
		$stringuri         = $uri->toString();
		$this->request_url = $stringuri;

		parent::display($tpl);
	}

}
