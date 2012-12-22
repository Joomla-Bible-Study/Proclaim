<?php
/**
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;


require_once JPATH_ROOT . '/components/com_biblestudy/lib/biblestudy.images.class.php';
require_once JPATH_ROOT . '/components/com_biblestudy/lib/biblestudy.pagebuilder.class.php';
JLoader::register('JBSMParams', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/params.php');

/**
 * View class for SeriesDisplays
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 * @todo finish titles
 */
class BiblestudyViewSeriesdisplays extends JViewLegacy
{
	public $admin;

	public $admin_params;

	public $items;

	public $template;

	public $pagination;

	public $request_url;

	public $params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$input     = new JInput;
		$option    = $input->get('option', '', 'cmd');
		JViewLegacy::loadHelper('image');

		$document = JFactory::getDocument();

		//  $model = $this->getModel();
		// Load the Admin settings and params from the template
		$this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
		$this->loadHelper('params');
		$this->admin = JBSMParams::getAdmin();

		$t = $input->get('t', 1, 'int');

		if (!$t)
		{
			$t = 1;
		}
		$template           = JBSMParams::getTemplateparams();
		$params             = $template->params;
		$a_params           = JBSMParams::getAdmin();
		$this->admin_params = $a_params->params;

		$itemparams = $mainframe->getPageParameters();

		// Prepare meta information (under development)
		if ($itemparams->get('metakey'))
		{
			$document->setMetadata('keywords', $itemparams->get('metakey'));
		}
		elseif (!$itemparams->get('metakey'))
		{
			$document->setMetadata('keywords', $this->admin_params->get('metakey'));
		}

		if ($itemparams->get('metadesc'))
		{
			$document->setDescription($itemparams->get('metadesc'));
		}
		elseif (!$itemparams->get('metadesc'))
		{
			$document->setDescription($this->admin_params->get('metadesc'));
		}

		$css = $params->get('css');
		if ($css <= "-1"):
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/biblestudy.css');
		else:
			$document->addStyleSheet(JURI::base() . 'media/com_biblestudy/css/site/' . $css);
		endif;


		// Import Scripts
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/jquery.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/biblestudy.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/tooltip.js');
		$document->addScript('http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js');
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/jwplayer.js');

		// Import Stylesheets
		$document->addStylesheet(JURI::base() . 'media/com_biblestudy/css/general.css');

		$url = $params->get('stylesheet');

		if ($url)
		{
			$document->addStyleSheet($url);
		}

		$uri           = JFactory::getURI();
		$filter_series = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');
		$pagebuilder   = new JBSPagebuilder;
		$items         = $this->get('Items');
		$images        = new jbsImages;

		// Adjust the slug if there is no alias in the row

		foreach ($items AS $item)
		{
			$item->slug         = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':' . str_replace(' ', '-', htmlspecialchars_decode($item->series_text, ENT_QUOTES));
			$seriesimage        = $images->getSeriesThumbnail($item->series_thumbnail);
			$item->image        = '<img src="' . $seriesimage->path . '" height="' . $seriesimage->height . '" width="' . $seriesimage->width . '" alt="" />';
			$item->serieslink   = JRoute::_('index.php?option=com_biblestudy&view=seriesdisplay&id=' . $item->slug . '&t=' . $t);
			$teacherimage       = $images->getTeacherImage($item->thumb, $image2 = null);
			$item->teacherimage = '<img src="' . $teacherimage->path . '" height="' . $teacherimage->height .
					'" width="' . $teacherimage->width . '" alt="" />';

			if (isset($item->description))
			{
				$item->text        = $item->description;
				$description       = $pagebuilder->runContentPlugins($item, $params);
				$item->description = $description->text;
			}
		}

		// Check permissions for this view by running through the records and removing those the user doesn't have permission to see
		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		$count  = count($items);

		if ($count > 0)
		{
			for ($i = 0; $i < $count; $i++)
			{

				if ($items[$i]->access > 1)
				{
					if (!in_array($items[$i]->access, $groups))
					{
						unset($items[$i]);
					}
				}
			}
		}
		$this->items           = $items;
		$total                 = $this->get('Total');
		$pagination            = $this->get('Pagination');
		$this->page            = new stdClass;
		$this->page->pagelinks = $pagination->getPagesLinks();
		$this->page->counter   = $pagination->getPagesCounter();
		$series                = $this->get('Series');

		// This is the helper for scripture formatting
		// @todo move to JLouder. tom
		$this->loadHelper('scripture');

		// End scripture helper
		$this->template   = $template;
		$this->pagination = $pagination;


		// Get the main study list image
		$mainimage        = $images->mainStudyImage();
		$this->page->main = '<img src="' . $mainimage->path . '" height="' . $mainimage->height . '" width="' . $mainimage->width . '" alt="" />';

		// $this->main = $main;

		// Build Series List for drop down menu
		$types3[]           = JHTML::_('select.option', '0', JText::_('JBS_CMN_SELECT_SERIES'));
		$types3             = array_merge($types3, $series);
		$this->page->series = JHTML::_('select.genericlist', $types3, 'filter_series', 'class="inputbox" size="1" onchange="this.form.submit()"',
			'value', 'text', "$filter_series"
		);
		$uri_tostring       = $uri->toString();

		// $this->lists = $lists;
		$this->request_url = $uri_tostring;
		$this->params      = $params;
		parent::display($tpl);
	}

}