<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * View class for SeriesDisplays
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewSeriesdisplays extends JViewLegacy
{
	/** @var object Admin Info */
	protected $admin;

	/** @var  JObject Items */
	protected $items;

	/** @var  JObject Template */
	protected $template;

	/** @var  JObject Pagination */
	protected $pagination;

	/** @var  string Request Url */
	protected $request_url;

	/** @var  Registry Params */
	protected $params;

	/** @var  String Page */
	protected $page;

	/** @var Registry State */
	protected $state;

	/** @var string State */
	protected $go;

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
		$this->state = $this->get('state');
		/** @var  $params Registry */
		$params = $this->state->template->params;
		$this->template = $this->state->get('template');

		$document = JFactory::getDocument();

		/** @var $itemparams Registry */
		$itemparams = $mainframe->getPageParameters();

		// Prepare meta information (under development)
		if ($itemparams->get('metakey'))
		{
			$document->setMetadata('keywords', $itemparams->get('metakey'));
		}
		elseif (!$itemparams->get('metakey'))
		{
			$document->setMetadata('keywords', $params->get('metakey'));
		}

		if ($itemparams->get('metadesc'))
		{
			$document->setDescription($itemparams->get('metadesc'));
		}
		elseif (!$itemparams->get('metadesc'))
		{
			$document->setDescription($params->get('metadesc'));
		}

		JHtml::_('biblestudy.framework');

		$uri            = new JUri;
		$filter_series  = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');
		$filter_teacher = $mainframe->getUserStateFromRequest($option . 'filter_teacher', 'filter_teacher', 0, 'int');
		$filter_year    = $mainframe->getUserStateFromRequest($option . 'filter_year', 'filter_year', 0, 'int');
		$pagebuilder    = new JBSMPagebuilder;
		$items          = $this->get('Items');
		$images         = new JBSMImages;

		// Adjust the slug if there is no alias in the row

		foreach ($items AS $item)
		{
			$item->slug         = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':'
				. str_replace(' ', '-', htmlspecialchars_decode($item->series_text, ENT_QUOTES));
			$seriesimage        = $images->getSeriesThumbnail($item->series_thumbnail);
			$item->image        = '<img src="' . $seriesimage->path . '" height="' . $seriesimage->height . '" width="' . $seriesimage->width . '" alt="" />';
			$item->serieslink   = JRoute::_('index.php?option=com_biblestudy&view=seriesdisplay&id=' . $item->slug . '&t=' . $this->template->id);
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

		$this->items           = $items;
		$pagination            = $this->get('Pagination');
		$this->page            = new stdClass;
		$this->page->pagelinks = $pagination->getPagesLinks();
		$this->page->counter   = $pagination->getPagesCounter();
		$series                = $this->get('Series');
		$teachers              = $this->get('Teachers');
		$years                 = $this->get('Years');

		// End scripture helper
		$this->pagination = $pagination;

		// Get the main study list image
		$mainimage        = $images->mainStudyImage();
		$this->page->main = '<img src="' . $mainimage->path . '" height="' . $mainimage->height . '" width="' . $mainimage->width . '" alt="" />';

		// Build go button
		$this->page->gobutton = '<input class="btn btn-primary" type="submit" value="' . JText::_('JBS_STY_GO_BUTTON') . '">';

		// Build Series List for drop down menu
		$seriesarray[]      = JHTML::_('select.option', '0', JText::_('JBS_CMN_SELECT_SERIES'));
		$seriesarray        = array_merge($seriesarray, $series);
		$this->page->series = JHTML::_('select.genericlist', $seriesarray, 'filter_series', 'class="inputbox" size="1" ',
			'value', 'text', "$filter_series"
		);

		// Build Years List for drop down menu
		$yeararray[]       = JHTML::_('select.option', '0', JText::_('JBS_CMN_SELECT_YEAR'));
		$yeararray         = array_merge($yeararray, $years);
		$this->page->years = JHTML::_('select.genericlist', $yeararray, 'filter_year', 'class="inputbox" size="1" ',
			'value', 'text', "$filter_year"
		);

		// Build Teachers List for drop down menu
		$teacherarray[]       = JHTML::_('select.option', '0', JText::_('JBS_CMN_SELECT_TEACHER'));
		$teacherarray         = array_merge($teacherarray, $teachers);
		$this->page->teachers = JHTML::_('select.genericlist', $teacherarray, 'filter_teacher', 'class="inputbox" size="1" ',
			'value', 'text', "$filter_teacher"
		);
		$go                   = 0;
		if ($params->get('series_list_years') > 0)
		{
			$go++;
		}
		if ($params->get('series_list_teachers') > 0)
		{
			$go++;
		}
		if ($params->get('search_series') > 0)
		{
			$go++;
		}
		$this->go = $go;
		if ($params->get('series_list_show_pagination') == 1)
		{
			$this->page->limits = '<span class="display-limit">' . JText::_('JGLOBAL_DISPLAY_NUM') . $this->pagination->getLimitBox() . '</span>';
			$dropdowns[]        = array('order' => '0', 'item' => $this->page->limits);
		}
		$uri_tostring = $uri->toString();

		// $this->lists = $lists;
		$this->request_url = $uri_tostring;
		$this->params      = $params;

		parent::display($tpl);
	}

}
