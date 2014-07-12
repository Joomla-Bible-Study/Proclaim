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
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$input  = new JInput;
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

		$params = JBSMParams::getTemplateparams()->params;
		JHtml::_('biblestudy.framework');
		JHtml::_('biblestudy.loadcss', $params, $params->get('stylesheet'));

		$uri                = new JUri;

		// Get the main study list image
		$Uri_toString      = $uri->toString();
		$this->request_url = $Uri_toString;
		$this->params      = $params;

		parent::display($tpl);
	}

}
