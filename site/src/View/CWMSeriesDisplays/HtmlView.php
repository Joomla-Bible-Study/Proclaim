<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\View\CWMSeriesDisplays;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use JApplicationSite;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use CWM\Component\Proclaim\Site\Helper\CWMImages;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;
use CWM\Component\Proclaim\Site\Helper\CWMPagebuilder;

/**
 * View class for SeriesDisplays
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class HtmlView extends BaseHtmlView
{
	/** @var object Admin Info
	 *
	 * @since 7.0 */
	protected $admin;

	/** @var  JObject Items
	 *
	 * @since 7.0 */
	protected $items;

	/** @var  JObject Template
	 *
	 * @since 7.0 */
	protected $template;

	/** @var  JObject Pagination
	 *
	 * @since 7.0 */
	protected $pagination;

	/** @var  string Request Url
	 *
	 * @since 7.0 */
	protected $request_url;

	/** @var  Registry Params
	 *
	 * @since 7.0 */
	protected $params;

	/** @var  String Page
	 *
	 * @since 7.0 */
	protected $page;

	/** @var Registry State
	 *
	 * @since 7.0 */
	protected $state;

	/** @var string State
	 *
	 * @since 7.0 */
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
		/** @type JApplicationSite $mainframe */
		$mainframe = Factory::getApplication('site');
		$input     = $mainframe->input;
		$option    = $input->get('option', '');
		$this->state = $this->get('state');
		/** @var  $params Registry */
		$params = $this->state->template->params;
        $this->params = $params;
		$this->template = $this->state->get('template');

		$document = Factory::getApplication()->getDocument();
/*
 * @todo Fix getting itemparams
		$itemparams = new SiteMenu();
		$itemparams->getParams($this->id);

		// Prepare meta information (under development)
		if ($itemparams->get('metakey'))
		{
			$document->setMetaData('keywords', $itemparams->get('metakey'));
		}
		elseif (!$itemparams->get('metakey'))
		{
			$document->setMetaData('keywords', $params->get('metakey'));
		}

		if ($itemparams->get('metadesc'))
		{
			$document->setDescription($itemparams->get('metadesc'));
		}
		elseif (!$itemparams->get('metadesc'))
		{
			$document->setDescription($params->get('metadesc'));
		}
*/

		$uri            = new Uri;
		$filter_series  = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');
		$filter_teacher = $mainframe->getUserStateFromRequest($option . 'filter_teacher', 'filter_teacher', 0, 'int');
		$filter_year    = $mainframe->getUserStateFromRequest($option . 'filter_year', 'filter_year', 0, 'int');
		$pagebuilder    = new CWMPageBuilder;
		$items          = $this->get('Items');
		$images         = new CWMImages;

		// Adjust the slug if there is no alias in the row

		foreach ($items AS $item)
		{
			$item->slug         = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':'
				. str_replace(' ', '-', htmlspecialchars_decode($item->series_text, ENT_QUOTES));
			$seriesimage        = $images->getSeriesThumbnail($item->series_thumbnail);
			$item->image        = '<img src="' . $seriesimage->path . '" height="' . $seriesimage->height . '" width="' . $seriesimage->width . '" alt="" />';
			$item->serieslink   = Route::_('index.php?option=com_proclaim&view=cwmseriesdisplay&id=' . $item->slug . '&t=' . $this->template->id);
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
		$this->page            = new \stdClass;
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


		if ($params->get('series_list_show_pagination') == 1)
		{
			$this->page->limits = '<span class="display-limit">' . Text::_('JGLOBAL_DISPLAY_NUM') . $this->pagination->getLimitBox() . '</span>';
			$dropdowns[]        = array('order' => '0', 'item' => $this->page->limits);
		}

		$uri_tostring = $uri->toString();

		// $this->lists = $lists;
		$this->request_url = $uri_tostring;


		parent::display($tpl);
	}


}
