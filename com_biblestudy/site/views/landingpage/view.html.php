<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

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
	 * @var Registry
	 */
	public $params;

	/**
	 * Params
	 *
	 * @var Registry
	 */
	public $state;

	public $main;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$document  = JFactory::getDocument();

		$this->state  = $this->get('state');
		$this->params = $this->state->template->params;

		$itemparams = JComponentHelper::getParams('com_biblestudy');

		// Prepare meta information (under development)
		if ($itemparams->get('metakey'))
		{
			$document->setMetadata('keywords', $itemparams->get('metakey'));
		}
		elseif (!$itemparams->get('metakey'))
		{
			$document->setMetadata('keywords', $this->params->get('metakey'));
		}

		if ($itemparams->get('metadesc'))
		{
			$document->setDescription($itemparams->get('metadesc'));
		}
		elseif (!$itemparams->get('metadesc'))
		{
			$document->setDescription($this->params->get('metadesc'));
		}
		JHtml::_('biblestudy.framework');


		$images   = new JBSMImages;
		$images->getShowhide();

		// Get the main study list image
		$this->main  = $images->mainStudyImage();

		$uri               = new JUri;
		$Uri_toString      = $uri->toString();
		$this->request_url = $Uri_toString;

		parent::display($tpl);
	}

}
