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
		$input  = new JInput;

		// Load the Admin settings and params from the template
		$this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers');
		$document  = JFactory::getDocument();

		$itemparams = JComponentHelper::getParams('com_biblestudy');

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

		$css = $params->get('css');
		JHtml::_('jquery.framework');

		// Import Scripts
		JHtml::script('media/com_biblestudy/js/biblestudy.js');

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
        $this->document->addStyleSheet(JURI::base(). 'media/com_biblestudy/jui/css/bootstrap-responsive.css');
        $this->document->addStyleSheet(JURI::base(). 'media/com_biblestudy/jui/css/bootstrap-extended.css');
        $this->document->addStyleSheet(JURI::base(). 'media/com_biblestudy/jui/css/bootstrap-responsive-min.css');
        $this->document->addStyleSheet(JURI::base(). 'media/com_biblestudy/jui/css/bootstrap.css');
        $this->document->addStyleSheet(JURI::base(). 'media/com_biblestudy/jui/css/bootstrap-min.css');

		$uri                = new JUri;

		// Get the main study list image
		$Uri_toString      = $uri->toString();
		$this->request_url = $Uri_toString;
		$this->params      = $params;

		parent::display($tpl);
	}

}
