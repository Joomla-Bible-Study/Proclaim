<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Landing page list view class
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 */
class BiblestudyViewLandingpage extends JViewLegacy
{
	/** @var  string Request URL */
	public $request_url;

	/**
	 * Params
	 *
	 * @var JRegistry
	 */
	public $params;

	/**
	 * Admin Params
	 *
	 * @var JRegistry
	 */
	public $admin_params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{

		$mainframe = JFactory::getApplication();
		$input     = new JInput;
		$option    = $input->get('option', '', 'cmd');

		// Load the Admin settings and params from the template
		$this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
		$document  = JFactory::getDocument();
		$mainframe = JFactory::getApplication();

		$itemparams = $mainframe->getPageParameters();

		// Convert parameter fields to objects.
		$this->admin_params = JBSMParams::getAdmin()->params;

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

		$t = $input->get('t', 1, 'int');

		if (!$t)
		{
			$t = 1;
		}

		$params = JBSMParams::getTemplateparams()->params;

		$document = JFactory::getDocument();
		$document->addScript(JURI::base() . 'media/com_biblestudy/js/tooltip.js');
		$images   = new JBSMImages;
		$showhide = $images->getShowhide();

		$css = $params->get('css');

		if (BIBLESTUDY_CHECKREL)
		{
			JHtml::_('jquery.framework');
		}

		// Import Scripts
		JHtml::script('media/com_biblestudy/js/biblestudy.js');
		JHtml::script('media/com_biblestudy/js/jquery.js');

		// Import Stylesheets
		JHtml::stylesheet('media/com_biblestudy/css/general.css');

		if ($css <= "-1")
		{
			JHtml::stylesheet('media/com_biblestudy/css/biblestudy.css');
		}
		else
		{
			JHtml::stylesheet('media/com_biblestudy/css/site/' . $css);
		}

		$url = $params->get('stylesheet');

		if ($url)
		{
			$document->addStyleSheet($url);
		}

		$uri                = new JUri;
		$filter_topic       = $mainframe->getUserStateFromRequest($option . 'filter_topic', 'filter_topic', 0, 'int');
		$filter_book        = $mainframe->getUserStateFromRequest($option . 'filter_book', 'filter_book', 0, 'int');
		$filter_teacher     = $mainframe->getUserStateFromRequest($option . 'filter_teacher', 'filter_teacher', 0, 'int');
		$filter_series      = $mainframe->getUserStateFromRequest($option . 'filter_series', 'filter_series', 0, 'int');
		$filter_messagetype = $mainframe->getUserStateFromRequest($option . 'filter_messagetype', 'filter_messagetype', 0, 'int');
		$filter_year        = $mainframe->getUserStateFromRequest($option . 'filter_year', 'filter_year', 0, 'int');
		$filter_location    = $mainframe->getuserStateFromRequest($option . 'filter_location', 'filter_location', 0, 'int');
		$filter_orders      = $mainframe->getUserStateFromRequest($option . 'filter_orders', 'filter_orders', 'DESC', 'word');
		$search             = JString::strtolower($mainframe->getUserStateFromRequest($option . 'search', 'search', '', 'string'));

		$app  = JFactory::getApplication();
		$menu = $app->getMenu();
		$item = $menu->getActive();

		// Get the main study list image
		$main              = $images->mainStudyImage();
		$Uri_toString      = $uri->toString();
		$this->request_url = $Uri_toString;
		$this->params      = $params;

		parent::display($tpl);
	}

}
